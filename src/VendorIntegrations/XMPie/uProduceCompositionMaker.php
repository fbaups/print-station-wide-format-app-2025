<?php

namespace App\VendorIntegrations\XMPie;

use App\Model\Entity\Artifact;
use App\Model\Entity\IntegrationCredential;
use App\Model\Entity\XmpieUproduceComposition;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\IntegrationCredentialsTable;
use App\Model\Table\XmpieUproduceCompositionsTable;
use App\Utility\Feedback\ReturnAlerts;
use App\XMPie\uProduce\Tasks\CompositionMaker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class uProduceCompositionMaker
{
    use ReturnAlerts;

    private Table|XmpieUproduceCompositionsTable $XmpieUproduceCompositions;
    private Table|IntegrationCredentialsTable $IntegrationCredentials;
    private Table|ArtifactsTable $Artifacts;


    public function __construct()
    {
        $this->XmpieUproduceCompositions = TableRegistry::getTableLocator()->get('XmpieUproduceCompositions');
        $this->IntegrationCredentials = TableRegistry::getTableLocator()->get('IntegrationCredentials');
        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
    }


    public function composeFromRecord(int $xmpieUproduceCompositionId): array|bool
    {
        /** @var XmpieUproduceComposition $xmpieUproduceComposition */
        $xmpieUproduceComposition = $this->XmpieUproduceCompositions->find('all')
            ->where(['id' => $xmpieUproduceCompositionId])
            ->first();

        /** @var Artifact $artifact */
        $artifact = $this->Artifacts->find('all')
            ->where(['id' => $xmpieUproduceComposition->artifact_link])
            ->first();

        /** @var IntegrationCredential $integrationCredential */
        $integrationCredential = $this->IntegrationCredentials->find('all')
            ->where(['id' => $xmpieUproduceComposition->integration_credential_link])
            ->first();

        $xmpOptions = $integrationCredential->uProduce_getUserCredentials();
        $soapOptions = $integrationCredential->uProduce_getSoapOptions();
        $config = $integrationCredential->uProduce_getConfigOptions();

        $CompositionMaker = new CompositionMaker($xmpOptions, $soapOptions, $config);

        $triggerFilePath = $artifact->full_unc;
        $jobIds = $CompositionMaker->produceFromTriggerFile($triggerFilePath);

        $this->mergeAlertsFromObject($CompositionMaker);

        if ($jobIds) {
            $jobIdsString = json_encode($jobIds);
            $message = __("Created the uProduce Job IDs: {0}", $jobIdsString);
            $this->addInfoAlerts($message);
            $this->returnMessage = $message;
            $this->returnValue = 0;
        } else {
            $message = __("Failed to create uProduce Job IDs.");
            $this->addDangerAlerts($message);
            $this->returnMessage = $message;
            $this->returnValue = 1;
        }

        //save JobIds
        if ($jobIds) {
            foreach ($jobIds as $jobId) {
                $xmpieUproduceCompositionJob = $this->XmpieUproduceCompositions->XmpieUproduceCompositionJobs->newEmptyEntity();
                $xmpieUproduceCompositionJob->job_number = $jobId;
                $xmpieUproduceCompositionJob->xmpie_uproduce_composition_id = $xmpieUproduceCompositionId;
                try {
                    $this->XmpieUproduceCompositions->XmpieUproduceCompositionJobs->save($xmpieUproduceCompositionJob);
                } catch (\Throwable $exception) {
                    $this->addDangerAlerts(__("Save JobID Error: {0}", $exception->getMessage()));
                }
            }
        }

        return $jobIds;
    }
}
