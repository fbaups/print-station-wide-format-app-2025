<?php

namespace App\OrderManagement;

use arajcany\PhotoPackageAdapter\Adapters\PackageReader;
use arajcany\PhotoPackageAdapter\Adapters\PackageWriter;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\I18n\DateTime;

class PhotoPackageOrdering extends OrderManagementBase
{

    public function __construct()
    {
        parent::__construct();
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
     * @param $orderData
     * @param array $orderOptions
     * @return array
     */
    private function _loadOrderSingleJob($orderData, array $orderOptions = []): array
    {
        $PackageReader = new PackageReader();

        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        $masterFormat = $PackageReader->readToMasterFormat($orderData);

        if (empty($masterFormat)) {
            $this->addDangerAlerts("Could not read Photo Package into the Master Format");
            return $tallies;
        }

        if (is_file($orderData) || is_dir($orderData)) {
            $creationDate = new DateTime(filectime($orderData));
        } else {
            $creationDate = null;
        }

        $orderId = $masterFormat->getOrder_ID();
        $payload = $masterFormat->getPayload();
        $hashSum = sha1($payload);

        if (is_dir($payload) || is_file($payload)) {
            $payload = null;
            $hashSum = null;
        }

        //may be overridden with the $orderOptions
        $orderStructured = [
            'guid' => Security::guid(),
            'order_status_id' => $this->getOrderStatusIdByName('Received'),
            'name' => "PhotoPackage-{$orderId}",
            'description' => "Photo Package Order {$orderId}",
            'external_system_type' => 'PhotoPackage',
            'external_order_number' => $orderId,
            'external_creation_date' => $creationDate,
            'payload' => $payload,
            'priority' => 3,
            'hash_sum' => $hashSum,
            'jobs' => [],
        ];

        $orderStructured = array_merge($orderStructured, $orderOptions);

        $jobStructured = [
            'guid' => Security::guid(),
            'job_status_id' => $this->getJobStatusIdByName('Received'),
            'name' => $orderStructured['name'],
            'description' => $orderStructured['description'],
            'quantity' => 1, //will always be 1 as the document will have a QTY
            'external_job_number' => $orderStructured['external_order_number'],
            'external_creation_date' => $orderStructured['external_creation_date'],
            'payload' => null,
            'priority' => 3,
            'documents' => [],
        ];

        $images = $masterFormat->getImages_InputList();
        $imagesInfo = $masterFormat->getImages_Information();
        $backPrints = $masterFormat->getImages_DelimitedBackprints();

        foreach ($imagesInfo as $k => $imageInfo) {
            $name = $imageInfo['filename'];
            $qty = $imageInfo['print_qty'];
            $backPrintInfo = $imageInfo['back_print_info'] ?? "";
            $backPrintInfo = str_replace("FREE", "", $backPrintInfo);
            $description = $backPrintInfo . " " . implode(" ", $backPrints[$k]);
            $description = trim($description);

            $documentStructured = [
                'guid' => Security::guid(),
                'document_status_id' => $this->getDocumentStatusIdByName('Requested'),
                'name' => $name,
                'description' => $description,
                'quantity' => $qty,
                'artifact_token' => null,
                'external_document_number' => null,
                'external_creation_date' => $creationDate,
                'external_url' => ($images[$k]) ?? null,
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
        $PackageReader = new PackageReader();

        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        $masterFormat = $PackageReader->readToMasterFormat($orderData);

        if (empty($masterFormat)) {
            $this->addDangerAlerts("Could not read Photo Package into the Master Format");
            return $tallies;
        }

        if (is_file($orderData) || is_dir($orderData)) {
            $creationDate = new DateTime(filectime($orderData));
        } else {
            $creationDate = null;
        }

        $orderId = $masterFormat->getOrder_ID();
        $payload = $masterFormat->getPayload();

        //may be overridden with the $orderOptions
        $orderStructured = [
            'guid' => Security::guid(),
            'order_status_id' => $this->getOrderStatusIdByName('Received'),
            'name' => "PhotoPackage-{$orderId}",
            'description' => "PhotoPackage Order {$orderId}",
            'external_system_type' => 'PhotoPackage',
            'external_order_number' => $orderId,
            'external_creation_date' => $creationDate,
            'payload' => $payload,
            'priority' => 3,
            'hash_sum' => sha1($payload),
            'jobs' => [],
        ];

        $orderStructured = array_merge($orderStructured, $orderOptions);

        $images = $masterFormat->getImages_InputList();
        $imagesInfo = $masterFormat->getImages_Information();
        $backPrints = $masterFormat->getImages_DelimitedBackprints();

        foreach ($imagesInfo as $k => $imageInfo) {
            $name = $imageInfo['filename'];
            $qty = $imageInfo['print_qty'];
            $backPrintInfo = $imageInfo['back_print_info'] ?? "";
            $backPrintInfo = str_replace("FREE", "", $backPrintInfo);
            $description = $backPrintInfo . " " . implode(" ", $backPrints[$k]);
            $description = trim($description);

            $documentStructured = [
                'guid' => Security::guid(),
                'document_status_id' => $this->getDocumentStatusIdByName('Requested'),
                'name' => $name,
                'description' => $description,
                'quantity' => $qty,
                'artifact_token' => null,
                'external_document_number' => null,
                'external_creation_date' => $creationDate,
                'external_url' => ($images[$k]) ?? null,
                'payload' => null,
                'priority' => 3,
            ];

            $jobStructured = [
                'guid' => Security::guid(),
                'job_status_id' => $this->getJobStatusIdByName('Received'),
                'name' => $name,
                'description' => $description,
                'quantity' => $qty,
                'external_job_number' => "{$orderId}-J{$k}",
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
