<?php

namespace App\OrderManagement;

use App\Model\Entity\Artifact;
use App\Model\Table\ArtifactsTable;
use arajcany\PhotoPackageAdapter\Adapters\PackageReader;
use arajcany\PhotoPackageAdapter\Adapters\PackageWriter;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class ArtifactGroupOrdering extends OrderManagementBase
{
    private Table|ArtifactsTable $ArtifactsTable;

    public function __construct()
    {
        parent::__construct();

        $this->ArtifactsTable = TableRegistry::getTableLocator()->get('Artifacts');
    }

    /**
     * @param $orderData
     * @param array $orderOptions
     * @return int[]
     */
    public function loadOrder($orderData, array $orderOptions = []): array
    {
        $orderOptions = array_merge($this->getDefaultOrderOptions(), $orderOptions);

        if ($orderOptions['numberOfJobs'] === 'single') {
            return $this->_loadOrderSingleJob($orderData, $orderOptions);
        } else {
            return $this->_loadOrderMultipleJobs($orderData, $orderOptions);
        }

    }

    /**
     * @param $artifactGroupingToken
     * @param array $orderOptions
     * @return array
     */
    private function _loadOrderSingleJob($artifactGroupingToken, array $orderOptions = []): array
    {
        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        if (empty($artifactGroupingToken)) {
            $this->addDangerAlerts("No Artifact Grouping supplied.");
            return $tallies;
        }

        $creationDate = new DateTime();

        //may be overridden with the $orderOptions
        $orderStructured = [
            'guid' => Security::guid(),
            'order_status_id' => $this->getOrderStatusIdByName('Received'),
            'name' => "InternalOrder-{$artifactGroupingToken}",
            'description' => "Internal Order creation {$artifactGroupingToken}",
            'external_system_type' => 'InternalOrder',
            'external_order_number' => null,
            'external_creation_date' => $creationDate,
            'payload' => $artifactGroupingToken,
            'priority' => 3,
            'hash_sum' => $artifactGroupingToken,
            'jobs' => [],
        ];

        $orderStructured = array_merge($orderStructured, $orderOptions);

        $jobStructured = [
            'guid' => Security::guid(),
            'job_status_id' => $this->getJobStatusIdByName('Received'),
            'name' => "InternalJob-{$artifactGroupingToken}",
            'description' => "Internal Job Creation {$artifactGroupingToken}",
            'quantity' => 1,
            'external_job_number' => null,
            'external_creation_date' => $creationDate,
            'payload' => null,
            'priority' => 3,
            'documents' => [],
        ];

        /** @var Artifact[] $artifacts */
        $artifacts = $this->ArtifactsTable->find('all')->where(['grouping' => $artifactGroupingToken]);

        foreach ($artifacts as $k => $artifact) {
            $documentStructured = [
                'guid' => Security::guid(),
                'document_status_id' => $this->getDocumentStatusIdByName('Ready'),
                'name' => "InternalDocument-{$k}",
                'description' => "Internal Document Creation {$k}",
                'quantity' => 1,
                'artifact_token' => $artifact->token,
                'external_document_number' => null,
                'external_creation_date' => $creationDate,
                'external_url' => null,
                'payload' => null,
                'priority' => 3,
            ];

            $jobStructured['documents'][] = $documentStructured;
        }

        $orderStructured['jobs'][] = $jobStructured;

        $result = parent::loadOrder($orderStructured);

        $tallies = $this->addArraysByKey($tallies, $result);
        $this->addInfoAlerts($tallies);

        return $tallies;
    }

    /**
     * @param $orderData
     * @param array $orderOptions
     * @return array
     */
    private function _loadOrderMultipleJobs($orderData, array $orderOptions = []): array
    {
        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        if (empty($artifactGroupingToken)) {
            $this->addDangerAlerts("No Artifact Grouping supplied.");
            return $tallies;
        }

        $creationDate = new DateTime();

        //may be overridden with the $orderOptions
        $orderStructured = [
            'guid' => Security::guid(),
            'order_status_id' => $this->getOrderStatusIdByName('Received'),
            'name' => "InternalOrder-{$artifactGroupingToken}",
            'description' => "Internal Order creation {$artifactGroupingToken}",
            'external_system_type' => 'InternalOrder',
            'external_order_number' => null,
            'external_creation_date' => $creationDate,
            'payload' => $artifactGroupingToken,
            'priority' => 3,
            'hash_sum' => $artifactGroupingToken,
            'jobs' => [],
        ];

        $orderStructured = array_merge($orderStructured, $orderOptions);

        /** @var Artifact[] $artifacts */
        $artifacts = $this->ArtifactsTable->find('all')->where(['grouping' => $artifactGroupingToken]);

        foreach ($artifacts as $k => $artifact) {
            $documentStructured = [
                'guid' => Security::guid(),
                'document_status_id' => $this->getDocumentStatusIdByName('Ready'),
                'name' => "InternalDocument-{$k}",
                'description' => "Internal Document Creation {$k}",
                'quantity' => 1,
                'artifact_token' => $artifact->token,
                'external_document_number' => null,
                'external_creation_date' => $creationDate,
                'external_url' => null,
                'payload' => null,
                'priority' => 3,
            ];

            $jobStructured = [
                'guid' => Security::guid(),
                'job_status_id' => $this->getJobStatusIdByName('Received'),
                'name' => "InternalJob-{$k}",
                'description' => "Internal Job Creation {$k}",
                'quantity' => 1,
                'external_job_number' => null,
                'external_creation_date' => $creationDate,
                'payload' => null,
                'priority' => 3,
                'documents' => [$documentStructured],
            ];

            $orderStructured['jobs'][] = $jobStructured;
        }

        $result = parent::loadOrder($orderStructured);

        $tallies = $this->addArraysByKey($tallies, $result);
        $this->addInfoAlerts($tallies);

        return $tallies;
    }

    public function getDefaultOrderOptions(): array
    {
        return [
            'numberOfJobs' => 'single',
        ];
    }

}
