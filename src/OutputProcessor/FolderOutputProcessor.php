<?php

namespace App\OutputProcessor;

use App\Model\Entity\Errand;
use App\Model\Table\ErrandsTable;
use App\OutputProcessor\OutputProcessorBase;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

class FolderOutputProcessor extends OutputProcessorBase implements OutputProcessorInterface
{
    use ReturnAlerts;

    public function process(array $outputItems): void
    {
        $asErrand = $this->outputIsErrand ?? true;

        if ($asErrand) {
            foreach ($outputItems as $outputItem) {
                $this->outputErrand($outputItem);
            }
        } else {
            foreach ($outputItems as $outputItem) {
                $this->output($outputItem);
            }
        }
    }

    /**
     * Create an Errand to Output Process the OJD
     *
     * @param array $outputItem
     * @return bool
     */
    public function outputErrand(array $outputItem): bool
    {
        if ($this->errandMode === 'sequential') {
            $waitForLink = $this->errandIdToWaitFor;
        } else {
            $waitForLink = null;
        }

        $activation = new DateTime();
        $expiration = (clone $activation)->addHours(8);
        $classNameShort = explode("\\", get_class($this));
        $classNameShort = array_pop($classNameShort);
        $rnd = Security::purl();

        $options = [
            'name' => "{$classNameShort} - {$rnd}",
            'activation' => $activation,
            'expiration' => $expiration,
            'wait_for_link' => $waitForLink,
            'grouping' => $this->errandGrouping,
            'class' => get_class($this),
            'method' => 'output',
            'parameters' => [$outputItem],
        ];
        $errand = $this->Errands->createErrand($options, false);
        $this->mergeAlertsFromObject($this->Errands);

        if ($errand) {
            $this->errandSuccessCounter++;
            $this->errandIdToWaitFor = $errand->id;
            $this->errandIds[] = $errand->id;
        } else {
            $this->errandFailCounter++;
        }

        $returnStatus = ($this->errandSuccessCounter === count($this->errandIds) && $this->errandFailCounter === 0);

        if ($returnStatus) {
            $this->setReturnValue(0);
            $this->setReturnMessage(__('Created {0} Errands to process this request', $this->errandSuccessCounter));
        } else {
            $this->setReturnValue(1);
            $this->setReturnMessage(__('Successfully created {0} Errands but failed to create {1} Errands.', $this->errandSuccessCounter, $this->errandFailCounter));
        }

        return $returnStatus;
    }

    /**
     * Main function to output a FSO to the given folder in the $outputConfiguration
     *
     * @param string $inputFileOrFolderPath
     * @param array $outputConfiguration
     * @return bool
     */
    public function output(array $outputItem): bool
    {
        $startTime = microtime(true);

        if (!isset($outputItem['parameters'])) {
            $this->addDangerAlerts("Output configuration is invalid.");
            return false;
        }

        //for output, extract the parameters
        $outputConfiguration = $outputItem['parameters'];
        $defaultOutputConfiguration = $this->getDefaultOutputConfiguration();
        $outputConfiguration = array_merge($defaultOutputConfiguration, $outputConfiguration);

        //determine the artifact to output
        $artifact = $this->getArtifactEntity($outputItem['artifact']);
        $inputFileOrFolderPath = $artifact->full_unc;

        //check FSO and determine type
        if (!is_dir($inputFileOrFolderPath) && !is_file($inputFileOrFolderPath)) {
            $this->addDangerAlerts("Supplied input file/folder is invalid.");
            return false;
        }
        $type = (is_dir($inputFileOrFolderPath)) ? 'folder' : 'file';

        //determine the mode
        $copyMode = $outputConfiguration['fso_copy_or_move'];
        $copyModeVerb = ($outputConfiguration['fso_copy_or_move'] === 'copy') ? 'copied' : 'moved';

        //get file listings and determine base input directory
        if ($type === 'folder') {
            $ZP = new ZipPackager();
            $listings = $ZP->rawFileAndFolderList($inputFileOrFolderPath);
            $inputDir = TextFormatter::makeDirectoryTrailingSmartSlash($inputFileOrFolderPath);
        } else {
            $listings = [
                'folders' => [],
                'files' => [pathinfo($inputFileOrFolderPath, PATHINFO_BASENAME)],
            ];
            $inputDir = TextFormatter::makeDirectoryTrailingSmartSlash(pathinfo($inputFileOrFolderPath, PATHINFO_DIRNAME));
        }

        //exit if no files found in folder
        if ($type === 'folder' && empty($listings['files'])) {
            $this->addInfoAlerts("Supplied input folder is empty - nothing to {$copyMode}.");
        }

        //$outputConfiguration Checks
        if (!isset($outputConfiguration['fso_path'])) {
            $this->addDangerAlerts("Output path not supplied.");
            return false;
        }

        if (!is_dir($outputConfiguration['fso_path'])) {
            $this->addDangerAlerts("Supplied output path is invalid.");
            $this->addDangerAlerts($outputConfiguration['fso_path']);
            return false;
        }

        //determine if placement in a sub-folder has been requested
        if ($outputConfiguration['fso_sub_folder'] === true) {
            if ($type === 'folder') {
                $subFolder = TextFormatter::makeDirectoryTrailingSmartSlash(pathinfo($inputFileOrFolderPath, PATHINFO_BASENAME));
            } else {
                $subFolder = TextFormatter::makeDirectoryTrailingSmartSlash(pathinfo(pathinfo($inputFileOrFolderPath, PATHINFO_DIRNAME), PATHINFO_BASENAME));
            }
        } elseif (is_string($outputConfiguration['fso_sub_folder'])) {
            $subFolder = TextFormatter::makeDirectoryTrailingSmartSlash($outputConfiguration['fso_sub_folder']);
        } else {
            $subFolder = '';
        }

        //determine base output directory
        $outputDir = TextFormatter::makeDirectoryTrailingSmartSlash($outputConfiguration['fso_path']) . $subFolder;
        $outputDir = $this->tidyPath($outputDir);

        //create all the destination dirs
        if (!is_dir($outputDir)) {
            @mkdir($outputDir, 0777, true);
        }
        foreach ($listings['folders'] as $folder) {
            $tmpDirName = $outputDir . $folder;
            if (!is_dir($tmpDirName)) {
                @mkdir($tmpDirName, 0777, true);
            }
        }

        //copy/move the file
        $successCounter = 0;
        $totalCounter = 0;
        $countOfFilesTouOutput = count($listings['files']);
        $countOfFilesToOutputStrLen = strlen($countOfFilesTouOutput);
        foreach ($listings['files'] as $f => $file) {
            $this->counter = $f + 1;
            $this->counterPadded = str_pad($this->counter, $countOfFilesToOutputStrLen, 0, STR_PAD_LEFT);

            $ojdPrefix = $this->compileOjdPrefix($outputConfiguration);
            $fileBuilderName = $this->compileFilenameVariable($outputConfiguration);

            if ($outputConfiguration['filenameOptions'] === 'builder') {
                $finalFileName = $fileBuilderName;
            } else if ($outputConfiguration['filenameOptions'] === 'prefix') {
                $finalFileName = $ojdPrefix . $file;
            } else {
                $finalFileName = $file;
            }

            $calculatedInputPath = $inputDir . $file;
            $calculatedOutputPath = $outputDir . $finalFileName;
            $calculatedOutputPath = $this->tidyPath($calculatedOutputPath);

            //make sure directory exists as $fileBuilderName can contain a variable path
            $this->mkdirLocalForFilename($calculatedOutputPath);

            try {
                if ($outputConfiguration['fso_copy_or_move'] === 'move') {
                    $result = rename($calculatedInputPath, $calculatedOutputPath);
                } else {
                    $result = copy($calculatedInputPath, $calculatedOutputPath);
                }

                if ($result) {
                    $this->addSuccessAlerts("Successfully {$copyModeVerb} the file {$file}.");
                    $successCounter++;
                } else {
                    $this->addWarningAlerts("Failed to {$copyMode} the file {$file}.");
                }
                $totalCounter++;
            } catch (\Throwable $exception) {
                $this->addDangerAlerts("Failed to {$copyMode} the file {$file}.");
                $this->addDangerAlerts($exception->getMessage());
            }
        }

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $this->addInfoAlerts("Completed Output Processor in {$totalTime} seconds.");

        return ($successCounter === $totalCounter);
    }

    public function getDefaultOutputConfiguration(): array
    {
        $default = parent::getDefaultOutputConfiguration();

        $config = [
            'fso_path' => '',                //example paths: "//some/unc/path" "c:\\tmp\\path\\"
            'fso_copy_or_move' => 'copy',    //copy||move the file
            'fso_sub_folder' => false,       //if true and a FOLDER is supplied only the contents will be copied/moved
        ];

        return array_merge($default, $config);
    }
}
