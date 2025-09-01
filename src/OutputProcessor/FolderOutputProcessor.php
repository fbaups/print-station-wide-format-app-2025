<?php

namespace App\OutputProcessor;

use App\Model\Entity\Errand;
use App\Model\Table\ErrandsTable;
use App\OutputProcessor\OutputProcessorBase;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\ORM\TableRegistry;

class FolderOutputProcessor extends OutputProcessorBase implements OutputProcessorInterface
{
    use ReturnAlerts;

    /**
     * Create an Errand to Output Process the OJD
     *
     * @param string $inputFileOrFolderPath
     * @param array $outputConfiguration
     * @param array $errandOptions
     * @return false|Errand
     */
    public function outputErrand(string $inputFileOrFolderPath, array $outputConfiguration, array $errandOptions = []): false|Errand
    {
        /** @var ErrandsTable $Errands */
        $Errands = TableRegistry::getTableLocator()->get('Errands');

        $classNameShort = explode("\\", get_class($this));
        $classNameShort = array_pop($classNameShort);
        $rnd = Security::purl();

        $options = [
            'name' => "{$classNameShort} - {$rnd}",
            'class' => get_class($this),
            'method' => 'output',
            'parameters' => [$inputFileOrFolderPath, $outputConfiguration],
        ];
        $options = array_merge($options, $errandOptions);

        return $Errands->createErrand($options, false);
    }

    /**
     * Main function to output a FSO to the given folder in the $outputConfiguration
     *
     * @param string $inputFileOrFolderPath
     * @param array $outputConfiguration
     * @return bool
     */
    public function output(string $inputFileOrFolderPath, array $outputConfiguration): bool
    {
        $startTime = microtime(true);

        $defaultOutputConfiguration = $this->getDefaultOutputConfiguration();
        $outputConfiguration = array_merge($defaultOutputConfiguration, $outputConfiguration);

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
            $fileBuilderName = $this->compileFilenameVariables($outputConfiguration);

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
