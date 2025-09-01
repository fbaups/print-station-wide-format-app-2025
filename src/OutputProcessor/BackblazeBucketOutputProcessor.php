<?php

namespace App\OutputProcessor;

use App\Model\Entity\Errand;
use App\Model\Table\ErrandsTable;
use App\OutputProcessor\OutputProcessorBase;
use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Network\CACert;
use App\Utility\Releases\RemoteUpdateServer;
use arajcany\BackblazeB2Client\BackblazeB2\Client;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\ORM\TableRegistry;
use League\Flysystem\Filesystem;
use League\Flysystem\StorageAttributes;
use phpseclib3\Net\SFTP;
use Throwable;
use Zaxbux\Flysystem\BackblazeB2Adapter;

class BackblazeBucketOutputProcessor extends OutputProcessorBase implements OutputProcessorInterface
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
        $copyMode = $outputConfiguration['b2_copy_or_move'];
        $copyModeVerb = ($outputConfiguration['b2_copy_or_move'] === 'copy') ? 'copied' : 'moved';

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
        if (!isset($outputConfiguration['b2_key'])) {
            $this->addDangerAlerts("Backblaze Key not supplied.");
            return false;
        }

        if (!isset($outputConfiguration['b2_key_id'])) {
            $this->addDangerAlerts("Backblaze KeyID not supplied.");
            return false;
        }

        if (!isset($outputConfiguration['b2_bucket'])) {
            $this->addDangerAlerts("Backblaze Bucket not supplied.");
            return false;
        }

        //determine if placement in a sub-folder has been requested
        if ($outputConfiguration['b2_sub_folder'] === true) {
            if ($type === 'folder') {
                $subFolder = TextFormatter::makeDirectoryTrailingForwardSlash(pathinfo($inputFileOrFolderPath, PATHINFO_BASENAME));
            } else {
                $subFolder = TextFormatter::makeDirectoryTrailingForwardSlash(pathinfo(pathinfo($inputFileOrFolderPath, PATHINFO_DIRNAME), PATHINFO_BASENAME));
            }
        } elseif (is_string($outputConfiguration['b2_sub_folder'])) {
            $subFolder = TextFormatter::makeDirectoryTrailingForwardSlash($outputConfiguration['b2_sub_folder']);
        } else {
            $subFolder = '';
        }

        //determine base output directory
        $outputDir = TextFormatter::makeDirectoryTrailingSmartSlash($outputConfiguration['b2_path']) . $subFolder;
        $outputDir = $this->tidyPath($outputDir);


        if (strlen($outputConfiguration['b2_key']) > 64) {
            $b2Key = Security::decrypt64Url($outputConfiguration['b2_key']);
            $b2KeyId = $outputConfiguration['b2_key_id'];
        } else {
            $b2Key = $outputConfiguration['b2_key'];
            $b2KeyId = $outputConfiguration['b2_key_id'];
        }

        //BackblazeB2 Client options
        $config = [
            'applicationKeyId' => $b2KeyId,
            'applicationKey' => $b2Key,
            //'authorizationCache' => false, //uncomment to stop using the AuthorizationCache, but why would you?
        ];

        //Guzzle options
        $guzzleConfig = [
            'verify' => (new CACert())->getCertPath()
        ];

        try {
            $client = new Client($config, $guzzleConfig);
            $adapter = new BackblazeB2Adapter($client, $outputConfiguration['b2_bucket']);
            $B2Filesystem = new Filesystem($adapter);
        } catch (\Throwable $exception) {
            $this->addDangerAlerts("Cannot connect to Backblaze.");
            return false;
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

            try {
                try {
                    $B2Filesystem->write($calculatedOutputPath, file_get_contents($calculatedInputPath));
                    $this->addSuccessAlerts("Successfully {$copyModeVerb} the file {$file}.");
                    $successCounter++;
                } catch (\Throwable $exception) {
                    $this->addWarningAlerts("Failed to {$copyMode} the file {$file}.");
                    $this->addWarningAlerts($exception->getMessage());
                }

                if ($outputConfiguration['b2_copy_or_move'] === 'move') {
                    //todo remove the source as this is a move operation
                    $this->addWarningAlerts("Move not yet implemented.");
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
            'b2_key_id' => '',
            'b2_key' => '',
            'b2_bucket' => '',
            'b2_path' => '',
            'b2_copy_or_move' => 'copy',    //copy||move the file
            'b2_sub_folder' => false,       //if true and a FOLDER is supplied only the contents will be copied/moved
        ];

        return array_merge($default, $config);
    }
}
