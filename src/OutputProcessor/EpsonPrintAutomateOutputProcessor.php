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
use Cake\Utility\Xml;
use phpseclib3\Net\SFTP;

class EpsonPrintAutomateOutputProcessor extends OutputProcessorBase implements OutputProcessorInterface
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
            $this->addInfoAlerts("Supplied input folder is empty - nothing to print.");
        }

        //$outputConfiguration Checks
        if (!isset($outputConfiguration['epa_exe'])) {
            $this->addDangerAlerts("Cannot find Epson Print Automate - please make sure it is installed.");
            return false;
        }

        if (!isset($outputConfiguration['epa_command'])) {
            $this->addDangerAlerts("sFTP host not supplied.");
            return false;
        }

        if (!isset($outputConfiguration['epa_username'])) {
            $this->addDangerAlerts("sFTP username not supplied.");
            return false;
        }

        if (!isset($outputConfiguration['epa_password'])) {
            $this->addDangerAlerts("sFTP password not supplied.");
            return false;
        }

        if (!isset($outputConfiguration['epa_preset'])) {
            $this->addDangerAlerts("sFTP host not supplied.");
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
            $fileBuilderName = $this->compileFilenameVariable($outputConfiguration);

            if ($outputConfiguration['filenameOptions'] === 'builder') {
                $finalFileName = $fileBuilderName;
            } else if ($outputConfiguration['filenameOptions'] === 'prefix') {
                $finalFileName = $ojdPrefix . $file;
            } else {
                $finalFileName = $file;
            }

            $finalFileName = str_replace("/", "~", $finalFileName);
            $finalFileName = str_replace("\\", "~", $finalFileName);

            $calculatedInputPath = $inputDir . $file;
            $calculatedRenamedPath = $inputDir . $finalFileName;

            try {
                copy($calculatedInputPath, $calculatedRenamedPath);

                if (strlen($outputConfiguration['epa_password']) > 64) {
                    $u = $outputConfiguration['epa_username'];
                    $p = Security::decrypt64Url($outputConfiguration['epa_password']);
                } else {
                    $u = $outputConfiguration['epa_username'];
                    $p = $outputConfiguration['epa_password'];
                }

                $cmd = $outputConfiguration['epa_command'];
                $inText = [
                    '{{FILEPATH}}',
                    '{{USERNAME}}',
                    '{{PASSWORD}}',
                    '{{PRESET}}',
                ];
                $outText = [
                    $calculatedRenamedPath,
                    $u,
                    $p,
                    $outputConfiguration['epa_preset'],
                ];
                $cmd = str_replace($inText, $outText, $cmd);
                exec($cmd, $out, $ret);

                if ($ret === 0) {
                    $isSuccess = true;
                } else {
                    $isSuccess = false;
                }

                if ($isSuccess) {
                    $this->addSuccessAlerts("Successfully printed the file {$file}.");
                    $successCounter++;
                } else {
                    $this->addWarningAlerts("Failed to print the file {$file}.");
                    $this->addWarningAlerts($out);
                }
                $totalCounter++;
            } catch (\Throwable $exception) {
                $this->addDangerAlerts("Failed to print the file {$file}.");
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
        $epsonExec = $this->getEpsonExecutablePath();
        $powerShellExec = "C:\\PSEXEC\\PsExec.exe";
        $powerShellCmd = '"' . $powerShellExec . '" -accepteula -i 1 -u "{{USERNAME}}" -p "{{PASSWORD}}"';
        $options = '/preset "{{PRESET}}"';
        $cmd = "{$powerShellCmd} \"{$epsonExec}\" {$options} \"{{FILEPATH}}\"";

        //example final command
        //C:\PSEXEC\PsExec.exe -i 1 -u "Some.User" -p "P@$$W0rd" "C:\Program Files (x86)\Epson Software\Epson Print Automate\EpsonPrintAutomateG.exe" /preset "Poster" "C:\Sample Images\landscape.jpg"

        $default = parent::getDefaultOutputConfiguration();

        $config = [
            'epa_exe' => $epsonExec,
            'epa_command' => $cmd,
            'epa_username' => null,
            'epa_password' => null,
            'epa_preset' => null,
        ];

        return array_merge($default, $config);
    }

    /**
     * @param $userName
     * @return array
     */
    public function getEpsonPresets($userName = null): array
    {
        if ($userName) {
            $searchPaths = [
                "C:\\Users\\{$userName}\\AppData\\Roaming\\epson\\Epson Print Automate\settings.xml"
            ];
        } else {
            $webUser = get_current_user();

            $directoryPath = 'C:\\Users\\';
            if (is_dir($directoryPath)) {
                $userFolders = scandir($directoryPath);
                foreach ($userFolders as $userFolder) {
                    if ($userFolder != '.' && $userFolder != '..' && is_dir($directoryPath . DIRECTORY_SEPARATOR . $userFolder)) {
                        if (in_array($userFolder, ['All Users', 'Default', 'Default User', $webUser])) {
                            continue;
                        }
                        $searchPaths[$userFolder] = "C:\\Users\\{$userFolder}\\AppData\\Roaming\\epson\\Epson Print Automate\settings.xml";
                    }
                }
            }

            //add iis web user last
            $searchPaths[$webUser] = "C:\\Users\\{$webUser}\\AppData\\Roaming\\epson\\Epson Print Automate\settings.xml";
        }

        $compiled = [];
        foreach ($searchPaths as $currentUser => $settingsFile) {
            if (!is_file($settingsFile)) {
                continue;
            }

            $contents = file_get_contents($settingsFile);
            $settings = Xml::toArray(Xml::build($contents));
            if (!isset($settings['SettingData']['PresetSettingsJsonText'])) {
                return [];
            }

            $settings = $settings['SettingData']['PresetSettingsJsonText'];
            $settings = json_decode($settings, true);

            foreach ($settings as $setting) {
                if ($setting['PresetName'] === 'Current Settings') {
                    continue;
                }
                $setting['WindowsUser'] = $currentUser;

                $compiled[] = $setting;
            }

        }

        return $compiled;
    }

    /**
     * @param $userName
     * @return array
     */
    public function getEpsonPresetsByUser(): array
    {
        $searchPaths = [];
        $directoryPath = 'C:\\Users\\';
        $webUser = get_current_user();
        if (is_dir($directoryPath)) {
            $userFolders = scandir($directoryPath);
            foreach ($userFolders as $userFolder) {
                if ($userFolder != '.' && $userFolder != '..' && is_dir($directoryPath . DIRECTORY_SEPARATOR . $userFolder)) {
                    if (in_array($userFolder, ['All Users', 'Default', 'Default User', $webUser])) {
                        continue;
                    }
                    $searchPaths[$userFolder] = "C:\\Users\\{$userFolder}\\AppData\\Roaming\\epson\\Epson Print Automate\settings.xml";
                }
            }
        }
        //add iis web user last
        $searchPaths[$webUser] = "C:\\Users\\{$webUser}\\AppData\\Roaming\\epson\\Epson Print Automate\settings.xml";

        $compiled = [];
        foreach ($searchPaths as $settingsFile) {
            if (!is_file($settingsFile)) {
                continue;
            }

            $username = str_replace($directoryPath, "", $settingsFile);
            $username = explode("\\", $username);

            if (!isset($username[0])) {
                continue;
            }
            $username = $username[0];

            $contents = file_get_contents($settingsFile);
            $settings = Xml::toArray(Xml::build($contents));
            if (!isset($settings['SettingData']['PresetSettingsJsonText'])) {
                return [];
            }

            $settings = $settings['SettingData']['PresetSettingsJsonText'];
            $settings = json_decode($settings, true);

            foreach ($settings as $setting) {
                if ($setting['PresetName'] === 'Current Settings') {
                    continue;
                }

                $compiled[$username][$setting['PresetName']] = $setting['PresetName'];
            }

        }
        return $compiled;
    }
}
