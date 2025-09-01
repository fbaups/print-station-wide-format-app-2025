<?php

namespace App\OutputProcessor;

use App\Model\Table\ErrandsTable;
use App\Utility\Feedback\ReturnAlerts;
use App\VendorIntegrations\Fujifilm\PressReady;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use League\Csv\Writer;

class FujifilmXmfPressReadyCsvHotFolderProcessor extends OutputProcessorBase implements OutputProcessorInterface
{

    use ReturnAlerts;

    /**
     * $outputItems is an array that looks like the following:
     *
     * (int) 0 => [
     *      'order' => (int) 224,
     *      'job' => (int) 423,
     *      'document' => (int) 704,
     *      'artifact' => (int) 1436,
     *      'parameters' => [ ],
     * ],
     * (int) 1 => [
     *      'order' => (int) 224,
     *      'job' => (int) 423,
     *      'document' => (int) 705,
     *      'artifact' => (int) 1437,
     *      'parameters' => [ ],
     * ],
     *
     * The 'parameters' controls how the item is output.
     *
     * @param array $outputItems
     * @return void
     */
    public function process(array $outputItems): void
    {
        $asErrand = $this->outputIsErrand ?? true;

        if ($asErrand) {
            $this->outputErrand($outputItems);
        } else {
            $this->output($outputItems);
        }
    }


    /**
     * Create an Errand to Output Process the OJD
     *
     * @param array $outputItems
     * @return bool
     */
    public function outputErrand(array $outputItems): bool
    {
        $checksumValidationResult = $this->validateChecksum($outputItems);

        $classNameShort = explode("\\", get_class($this));
        $classNameShort = array_pop($classNameShort);
        $rnd = Security::purl();

        $activation = new DateTime();
        $expiration = (clone $activation)->addHours(8);
        $options = [
            'name' => "{$classNameShort} - {$rnd}",
            'activation' => $activation,
            'expiration' => $expiration,
            'class' => get_class($this),
            'method' => 'output',
            'parameters' => [$outputItems],
        ];

        $errand = $this->Errands->createErrand($options, false);
        $this->mergeAlertsFromObject($this->Errands);

        if ($errand && !$checksumValidationResult) {
            $warningMessage = __('The Hot Folder or Workflow settings have changed in Fujifilm XMF Press Ready. Please have an Administrator check the Output Processor settings.');
            $this->addWarningAlerts($warningMessage);

            $this->errandSuccessCounter++;
            $this->errandIdToWaitFor = $errand->id;
            $this->errandIds[] = $errand->id;

            $this->setReturnValue(0);
            $this->setReturnMessage(__('An Errand has been created to process this request, but a warning has been issued. ' . $warningMessage));

            return true;
        } elseif ($errand) {
            $this->errandSuccessCounter++;
            $this->errandIdToWaitFor = $errand->id;
            $this->errandIds[] = $errand->id;

            $this->setReturnValue(0);
            $this->setReturnMessage(__('An Errand has been created to process this request.'));

            return true;
        } else {
            $this->errandFailCounter++;

            $this->setReturnValue(0);
            $this->setReturnMessage(__('Failed to create an Errand to process this request.'));

            return false;
        }
    }


    /**
     * Main function to output a FSO to the given folder in the $outputConfiguration
     *
     * @param array $outputItems
     * @return bool
     */
    public function output(array $outputItems): bool
    {
        $startTime = microtime(true);

        if (!isset($outputItems[0]['parameters'])) {
            $this->addDangerAlerts("Output configuration is invalid.");
            return false;
        }

        //for csv output, we look at the first record and use the parameters for the CSV schema
        $outputConfiguration = $outputItems[0]['parameters'];
        $defaultOutputConfiguration = $this->getDefaultOutputConfiguration();
        $outputConfiguration = array_merge($defaultOutputConfiguration, $outputConfiguration);

        $hfId = intval($outputConfiguration['pr-csv-hf-id']);
        $wfId = intval($outputConfiguration['pr-csv-wf-id']);

        $fieldValues = $outputConfiguration['pr-csv-hf-schema'][$hfId][$wfId];
        $checksum = $fieldValues['checksum'];
        unset($fieldValues['checksum']);
        if (!$checksum) {
            $this->addDangerAlerts("Output configuration checksum is invalid.");
            return false;
        }

        $PressReady = new PressReady();
        $pressReadyHotFolder = $PressReady->getCsvHotFolderByHotFolderIdAndWorkflowId($hfId, $wfId);
        if (!$pressReadyHotFolder) {
            $this->addDangerAlerts("Supplied Hot Folder ID {$outputConfiguration['pr-csv-hf-id-wf-id']} is invalid.");
            return false;
        }

        $fieldValuesTmp = [];
        foreach ($fieldValues as $key => $value) {
            $key = explode("-", $key);
            if (is_numeric($key[0])) {
                $fieldValuesTmp[intval($key[0])] = $value;
            }
        }
        $fieldValues = $fieldValuesTmp;
        unset($fieldValuesTmp);
        if (array_keys($fieldValues) !== array_keys($pressReadyHotFolder['csv_schema'])) {
            $this->addDangerAlerts("Output configuration schema length is invalid.");
            return false;
        }


        /*
         * Now we can start generating the CSV
         */

        $csvData = [];

        //get the max number of fields in the CSV
        $maxFields = max(array_keys($fieldValues));
        $fieldNumbers = range(1, $maxFields);

        //generate the headers
        $headers = [];
        foreach ($fieldNumbers as $fieldNumber) {
            if (isset($fieldValues[$fieldNumber])) {
                $headers[] = $pressReadyHotFolder['csv_schema'][$fieldNumber]['columnNameSlug'];
            } else {
                $headers[] = "$fieldNumber-column";
            }
        }

        //skip header lines
        $shouldSkipFirstLines = $pressReadyHotFolder['csv_parse_rule']['ShouldSkipFirstLines'];
        $skipFirstLines = $pressReadyHotFolder['csv_parse_rule']['SkipFirstLines'];
        if ($shouldSkipFirstLines && $skipFirstLines > 0) {
            foreach (range(1, $skipFirstLines) as $lineToSkip) {
                $csvData[] = $headers;
            }
        }

        //generate the row data
        foreach ($outputItems as $outputItem) {

            $outputConfigurationForRow = $outputItem['parameters'];

            $artifact = $this->getArtifactEntity($outputItem['artifact']);

            if (!in_array($artifact->mime_type, $this->Artifacts->getPdfMimeTypes())) {
                //convert to PDF
                $imageProperties = [
                    'format' => 'jpg',
                    'quality' => 100,
                    'anchor' => 5,
                    'fitting' => 'fill',
                    'resolution' => '@',
                    'clipping' => false,
                    'auto_rotate' => true,
                ];
                $pageProperties = [
                    'unit' => 'mm',
                    'page_width' => '297',
                    'page_height' => '210',
                    'crop_length' => 5,
                    'crop_offset' => 5,
                    'bleed' => 5,
                    'slug' => 20,
                    'info' => true,
                ];
                $this->Artifacts->createVersionPdf($artifact, $imageProperties, $pageProperties);
            }

            $subIn = [
                $artifact->full_unc,
                $artifact->full_url
            ];
            $subOut = [
                TextFormatter::makeEndsWith($artifact->full_unc, '.pdf'),
                TextFormatter::makeEndsWith($artifact->full_url, '.pdf')
            ];

            $rowData = [];
            foreach ($fieldNumbers as $fieldNumber) {
                if (isset($fieldValues[$fieldNumber])) {
                    $fieldValue = $fieldValues[$fieldNumber];
                    $fieldValue = $this->compileVariable($fieldValue, $outputConfigurationForRow);
                    $fieldValue = str_replace($subIn, $subOut, $fieldValue);
                    $rowData[] = $fieldValue;
                } else {
                    $rowData[] = '';
                }
            }
            $csvData[] = $rowData;
        }

        //convert to string
        try {
            $csv = Writer::createFromString('');
            $csv->insertAll($csvData);
            $csvString = $csv->toString();
        } catch (\Throwable $exception) {
            $csvString = false;
        }

        if (!$csvString) {
            $this->addDangerAlerts("Could not convert data to a CSV string.");
            return false;
        }

        //write to FSO
        $directory = TextFormatter::makeDirectoryTrailingSmartSlash($pressReadyHotFolder['jobflow_hotfolder_path']);
        $filename = $this->compileFilenameVariable($outputConfiguration);
        $path = "{$directory}{$filename}";

        //force CSV filename as PressReady only accepts CSV files
        $path = TextFormatter::makeEndsWith($path, ".csv");
        file_put_contents($path, $csvString);

        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        $this->addInfoAlerts("Completed Output Processor in {$totalTime} seconds.");

        $this->mergeAlertsFromObject($PressReady);

        return true;
    }


    public function getDefaultOutputConfiguration(): array
    {
        $default = parent::getDefaultOutputConfiguration();

        $config = [
            'pr-csv-hf-id-wf-id' => '',
            'pr-csv-hf-id' => '',
            'pr-csv-wf-id' => '',
            'pr-csv-hf-path' => '',
            'pr-csv-hf-schema' => [],
        ];

        return array_merge($default, $config);
    }

    /**
     * @param $outputItems
     * @return bool
     */
    private function validateChecksum($outputItems): bool
    {
        $checksumsFailCounter = 0;
        $checksumsSuccessCounter = 0;

        foreach ($outputItems as $outputItem) {
            $hfId = intval($outputItem['parameters']['pr-csv-hf-id']);
            $wfId = intval($outputItem['parameters']['pr-csv-wf-id']);

            $checksumFromConfig = $outputItem['parameters']['pr-csv-hf-schema'][$hfId][$wfId]['checksum'];

            $PressReady = new PressReady();
            $pressReadyHotFolder = $PressReady->getCsvHotFolderByHotFolderIdAndWorkflowId($hfId, $wfId);
            $checksumFromPressReady = $pressReadyHotFolder['csv_parse_rule_checksum'];

            if ($checksumFromConfig === $checksumFromPressReady) {
                $checksumsSuccessCounter++;
            } else {
                $checksumsFailCounter++;
            }
        }

        return ($checksumsFailCounter === 0 && $checksumsSuccessCounter > 0);
    }
}
