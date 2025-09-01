<?php

namespace App\OutputProcessor\Handler;

use App\Model\Entity\Document;
use App\Model\Entity\Job;
use App\Model\Entity\Order;
use App\Model\Entity\OutputProcessor;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\DocumentsTable;
use App\Model\Table\JobsTable;
use App\Model\Table\OrdersTable;
use App\Model\Table\OutputProcessorsTable;
use App\OutputProcessor\BackblazeBucketOutputProcessor;
use App\OutputProcessor\EpsonPrintAutomateOutputProcessor;
use App\OutputProcessor\FolderOutputProcessor;
use App\OutputProcessor\FujifilmXmfPressReadyCsvHotFolderProcessor;
use App\OutputProcessor\FujifilmXmfPressReadyPdfHotFolderProcessor;
use App\OutputProcessor\OutputProcessorBase;
use App\OutputProcessor\sFTPOutputProcessor;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * Convenience Class to handle requests to Output Process Orders, Jobs and Documents
 */
class OutputProcessorHandlerForOrdersJobsDocuments
{
    use ReturnAlerts;

    private Table|OrdersTable $OrdersTable;
    private Table|JobsTable $JobsTable;
    private Table|DocumentsTable $DocumentsTable;
    private Table|ArtifactsTable $ArtifactsTable;
    private Table|OutputProcessorsTable $OutputProcessorsTable;

    private bool $outputIsErrand;

    public function __construct()
    {
        /**
         * @var OrdersTable $OrdersTable
         * @var JobsTable $JobsTable
         * @var DocumentsTable $DocumentsTable
         */
        $this->OrdersTable = TableRegistry::getTableLocator()->get('Orders');
        $this->JobsTable = TableRegistry::getTableLocator()->get('Jobs');
        $this->DocumentsTable = TableRegistry::getTableLocator()->get('Documents');
        $this->ArtifactsTable = TableRegistry::getTableLocator()->get('Artifacts');
        $this->OutputProcessorsTable = TableRegistry::getTableLocator()->get('OutputProcessors');

        $this->outputIsErrand = true;
    }

    /**
     * @param bool $outputIsErrand
     * @return void
     */
    public function setOutputIsErrand(bool $outputIsErrand): void
    {
        $this->outputIsErrand = $outputIsErrand;
    }

    /**
     * @param int|OutputProcessor $outputProcessorIdOrEntity
     * @param int|Order $orderIdOrEntity
     * @return array|false
     */
    public function outputProcessOrder(int|OutputProcessor $outputProcessorIdOrEntity, int|Order $orderIdOrEntity): false|array
    {
        /** @var Order $order */
        $order = $this->OrdersTable->asEntity($orderIdOrEntity);
        if (!$order) {
            $this->addDangerAlerts("Invalid Order provided");
            return false;
        }

        return $this->_outputProcessOJD($outputProcessorIdOrEntity, $orderIdOrEntity, null, null);
    }

    /**
     * @param int|OutputProcessor $outputProcessorIdOrEntity
     * @param int|Job $jobIdOrEntity
     * @return array|false
     */
    public function outputProcessJob(int|OutputProcessor $outputProcessorIdOrEntity, int|Job $jobIdOrEntity): false|array
    {
        /** @var Job $job */
        $job = $this->JobsTable->asEntity($jobIdOrEntity);
        if (!$job) {
            $this->addDangerAlerts("Invalid Job provided");
            return false;
        }

        $orderId = $job->order_id;

        return $this->_outputProcessOJD($outputProcessorIdOrEntity, $orderId, $job, null);
    }

    /**
     * @param int|OutputProcessor $outputProcessorIdOrEntity
     * @param int|Document $documentIdOrEntity
     * @return array|false
     */
    public function outputProcessDocument(int|OutputProcessor $outputProcessorIdOrEntity, int|Document $documentIdOrEntity): false|array
    {
        /** @var Document $document */
        $document = $this->DocumentsTable->asEntity($documentIdOrEntity);
        if (!$document) {
            $this->addDangerAlerts("Invalid Document provided");
            return false;
        }

        /** @var Job $job */
        $job = $this->JobsTable->asEntity($document->job_id);
        if (!$job) {
            $this->addDangerAlerts("Invalid Job provided");
            return false;
        }

        $orderId = $job->order_id;

        return $this->_outputProcessOJD($outputProcessorIdOrEntity, $orderId, $job, $document);
    }

    /**
     * Main function to Output Process and Order/Job/Document
     *
     * An Order must be provided
     * Jobs is optional, if provided will be limited to that Job
     * Document is optional, if provided will be limited to that Document
     *
     * @param int|OutputProcessor $outputProcessorIdOrEntity
     * @param int|Order $orderIdOrEntity
     * @param int|Job|null $jobIdOrEntity
     * @param int|Document|null $documentIdOrEntity
     * @return false|array
     */
    private function _outputProcessOJD(int|OutputProcessor $outputProcessorIdOrEntity, int|Order $orderIdOrEntity, int|Job|null $jobIdOrEntity = null, int|Document|null $documentIdOrEntity = null): false|array
    {
        /** @var Order $order */
        $order = $this->OrdersTable->getCompleteOrder($orderIdOrEntity);
        if (!$order) {
            $this->addDangerAlerts("Invalid Order provided");
            return false;
        }

        /** @var OutputProcessor $outputProcessor */
        $outputProcessor = $this->OutputProcessorsTable->asEntity($outputProcessorIdOrEntity);
        if (!$outputProcessor) {
            $this->addDangerAlerts("Invalid Output Processor provided");
            return false;
        }

        if ($jobIdOrEntity) {
            $jobLimiter = $this->JobsTable->asEntity($jobIdOrEntity);
        } else {
            $jobLimiter = null;
        }

        if ($documentIdOrEntity) {
            $documentLimiter = $this->DocumentsTable->asEntity($documentIdOrEntity);
        } else {
            $documentLimiter = null;
        }

        $grouping = sha1(Security::guid());

        $OutputProcessorBase = new OutputProcessorBase();
        $outputProcessorClassPath = $OutputProcessorBase->getOutputProcessorTypeClassPathByName($outputProcessor->type);
        /**
         * @var FolderOutputProcessor|sFTPOutputProcessor|EpsonPrintAutomateOutputProcessor|BackblazeBucketOutputProcessor|FujifilmXmfPressReadyCsvHotFolderProcessor|FujifilmXmfPressReadyPdfHotFolderProcessor $OutputProcessor
         */
        $OutputProcessor = new $outputProcessorClassPath();

        $artifactsCache = $this->OrdersTable->getArtifacts($order->id);
        $artifactsValidated = [];

        $artifactCount = 0;
        $artifactCountExpected = 0;

        $documentCount = 0;
        $documentCountExpected = 0;

        $fileCounterPrefixMax = count($artifactsCache);
        $fileCounterPrefixStrlen = strlen($fileCounterPrefixMax);
        $fileCounterPrefix = 0; //used as the  padded counter variable in the output filename

        $toProcess = [];

        foreach ($order->jobs as $job) {
            //limit to specified Job
            if ($jobLimiter) {
                if ($job->id !== $jobLimiter->id) {
                    continue;
                }
            }

            foreach ($job->documents as $document) {
                //limit to specified Document
                if ($documentLimiter) {
                    if ($document->id !== $documentLimiter->id) {
                        continue;
                    }
                }

                //got here, so we expect a Document and Artifact to be present
                $documentCountExpected++;
                $artifactCountExpected++;

                $outputProcessorParameters = $outputProcessor->parameters;

                $outputProcessorParameters['outputIsErrand'] = $this->outputIsErrand;

                if ($outputProcessorParameters['prefixOrderId']) {
                    $outputProcessorParameters['prefixOrderId'] = $order->id;
                }
                if ($outputProcessorParameters['prefixJobId']) {
                    $outputProcessorParameters['prefixJobId'] = $job->id;
                }
                if ($outputProcessorParameters['prefixDocumentId']) {
                    $outputProcessorParameters['prefixDocumentId'] = $document->id;
                }

                if ($outputProcessorParameters['prefixExternalOrderNumber']) {
                    $outputProcessorParameters['prefixExternalOrderNumber'] = $order->external_order_number;
                }
                if ($outputProcessorParameters['prefixExternalJobNumber']) {
                    $outputProcessorParameters['prefixExternalJobNumber'] = $job->external_job_number;
                }
                if ($outputProcessorParameters['prefixExternalDocumentNumber']) {
                    $outputProcessorParameters['prefixExternalDocumentNumber'] = $document->external_document_number;
                }

                //check that the Document has an Artifact
                $artifactToken = $document->artifact_token;
                if (!$artifactToken) {
                    continue;
                }

                //got here, so Document is valid
                $documentCount++;

                //get the Artifact from the array
                $artifact = $artifactsCache[$artifactToken] ?? null;
                if (!$artifact) {
                    continue;
                }

                //check the file exists
                if (!is_file($artifact->full_unc)) {
                    continue;
                }

                //got here, so Artifact (and actual file) is valid
                $artifactCount++;
                $artifactsValidated[$artifact->token] = true;

                //increase the prefix for filename variable
                $fileCounterPrefix++;

                $orderQuantity = $order->quantity;
                $jobQuantity = $job->quantity;
                $documentQuantity = $document->quantity;
                $totalQuantity = $orderQuantity * $jobQuantity * $documentQuantity;

                $filenameBuilderVars = [
                    'counter' => str_pad($fileCounterPrefix, $fileCounterPrefixStrlen, '0', STR_PAD_LEFT),
                    'order' => [
                        'id' => $order->id,
                        'name' => trim($this->ArtifactsTable->sanitizeFilename($order->name)),
                        'description' => trim($this->ArtifactsTable->sanitizeFilename($order->description)),
                        'order_quantity' => $orderQuantity,
                        'external_order_number' => trim($this->ArtifactsTable->sanitizeFilename($order->external_order_number)),
                    ],
                    'job' => [
                        'id' => $job->id,
                        'name' => trim($this->ArtifactsTable->sanitizeFilename($job->name)),
                        'description' => trim($this->ArtifactsTable->sanitizeFilename($job->description)),
                        'job_quantity' => $jobQuantity,
                        'external_job_number' => trim($this->ArtifactsTable->sanitizeFilename($job->external_job_number)),
                    ],
                    'document' => [
                        'id' => $document->id,
                        'name' => trim($this->ArtifactsTable->sanitizeFilename($document->name)),
                        'description' => trim($this->ArtifactsTable->sanitizeFilename($document->description)),
                        'document_quantity' => $documentQuantity,
                        'total_quantity' => $totalQuantity,
                        'external_document_number' => trim($this->ArtifactsTable->sanitizeFilename($document->external_document_number)),
                        'file_name' => trim($this->ArtifactsTable->sanitizeFilename(pathinfo($artifact->name, PATHINFO_FILENAME))),
                        'file_extension' => trim($this->ArtifactsTable->sanitizeFilename(pathinfo($artifact->name, PATHINFO_EXTENSION))),
                    ],
                    'artifact' => [
                        'id' => $artifact->id,
                        'name' => trim($this->ArtifactsTable->sanitizeFilename($artifact->name)),
                        'description' => trim($this->ArtifactsTable->sanitizeFilename($artifact->description)),
                        'unc' => $artifact->full_unc,
                        'url' => $artifact->full_url,
                        'mime_type' => $artifact->mime_type,
                    ],
                ];
                if (empty($filenameBuilderVars['document']['file_extension'])) {
                    $filenameBuilderVars['document']['file_extension'] = $this->OrdersTable->getExtensionFromMimeType($artifact->mime_type);
                }
                $outputProcessorParameters['filenameBuilderVars'] = $filenameBuilderVars;

                $toProcess[] = [
                    'order' => $order->id,
                    'job' => $job->id,
                    'document' => $document->id,
                    'artifact' => $artifact->id,
                    'parameters' => $outputProcessorParameters,
                ];

            }
        }

        $OutputProcessor->setOutputIsErrand($this->outputIsErrand);
        $OutputProcessor->process($toProcess);
        $alertLevel = $OutputProcessor->getHighestAlertLevel();

        if (in_array(strtolower($alertLevel), ['info', 'success'])) {
            $status = true;
        } else {
            $status = false;
        }

        $results = [
            'return_value' => $OutputProcessor->getReturnValue(),
            'return_message' => $OutputProcessor->getReturnMessage(),
            'status' =>
                $status
                && ($artifactCountExpected === $artifactCount)
                && ($documentCountExpected === $documentCount)
                && ($documentCount === $artifactCount),
            'errand_count_success' => $OutputProcessor->getErrandSuccessCounter(),
            'errand_count_fail' => $OutputProcessor->getErrandFailCounter(),
            'alert_level' => $alertLevel,
        ];

        if (strtolower(Configure::read('mode')) === 'dev') {
            $results['alerts'] = $OutputProcessor->getAllAlertsLogSequence();
        }

        return $results;
    }
}
