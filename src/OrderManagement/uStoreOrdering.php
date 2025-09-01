<?php

namespace App\OrderManagement;

use App\Model\Entity\Order;
use App\Model\Table\OrdersTable;
use App\Utility\Network\CACert;
use arajcany\ToolBox\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Xml;
use GuzzleHttp\Client;

class uStoreOrdering extends OrderManagementBase
{
    use ReturnAlerts;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $orderData
     * @param array $orderOptions
     * @return array
     */
    public function loadOrder($orderData, array $orderOptions = []): array
    {
        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        if (is_file($orderData)) {
            $order = file_get_contents($orderData);
            $order = str_replace('encoding="utf-16"', 'encoding="utf-8"', $order);
        } else {
            $order = $orderData;
        }

        //save as original payload
        $payload = $order;

        //convert xml to array
        try {
            $xmlGuid = sha1($order);
            $order = Xml::toArray(Xml::build($order));
        } catch (\Throwable $exception) {
            $this->addDangerAlerts("Error decoding uStore XML");
            return $tallies;
        }

        if (!isset($order['OrderXml']['Order']['OrderProducts'])) {
            $this->addDangerAlerts("Invalid uStore XML");
            return $tallies;
        }

        //nest the $orderData correctly
        if (isset($order['OrderXml']['Order']['OrderProducts']['OrderProduct']['@id'])) {
            $orderProduct = $order['OrderXml']['Order']['OrderProducts']['OrderProduct'];
            $order['OrderXml']['Order']['OrderProducts']['OrderProduct'] = [];
            $order['OrderXml']['Order']['OrderProducts']['OrderProduct'][] = $orderProduct;
        }

        //construct the $orderStructured array
        $orderStructured = [
            'guid' => Security::guid(),
            'order_status_id' => $this->getOrderStatusIdByName('Received'),
            'name' => "uStore-{$order['OrderXml']['Order']['@DisplayOrderId']}",
            'description' => "uSore Order {$order['OrderXml']['Order']['@DisplayOrderId']}",
            'external_system_type' => 'uStore',
            'external_order_number' => $order['OrderXml']['Order']['@DisplayOrderId'],
            'external_creation_date' => $order['OrderXml']['Order']['@CreationDate'],
            'payload' => $payload,
            'priority' => 3,
            'jobs' => [],
        ];

        $orderStructured = array_merge($orderStructured, $orderOptions);

        foreach ($order['OrderXml']['Order']['OrderProducts']['OrderProduct'] as $j => $lineItem) {

            $jobStructured = [
                'guid' => Security::guid(),
                'job_status_id' => $this->getJobStatusIdByName('Received'),
                'name' => $lineItem['Product']['Name'],
                'description' => "{$lineItem['Product']['Type']} - {$lineItem['Product']['@id']} {$lineItem['Product']['Name']}",
                'quantity' => $lineItem['Quantities']['NumberOfCopies'],
                'external_job_number' => $lineItem['@id'],
                'external_creation_date' => $lineItem['@creationDate'],
                'payload' => $lineItem,
                'priority' => 3,
                'documents' => [],
            ];
            $orderStructured['jobs'][$j] = $jobStructured;


            /**
             * uStore, as always, does things differently.
             * You access the document via a crafted URL with an OutputToken
             * In the header response you get the filename of the document and the body contains the document itself - be it pdf/jpg/etc.
             */
            $externalUrl = "https://{$order['OrderXml']['Order']['Store']['@LandingDomain']}/uStore/Controls/SDK/OrderOutputProxy.ashx?token={$lineItem['@OutputToken']}";

            $documentStructured = [
                'guid' => Security::guid(),
                'document_status_id' => $this->getDocumentStatusIdByName('Requested'),
                'name' => $lineItem['@OutputToken'],
                'description' => "uStore artwork",
                'artifact_token' => null,
                'external_document_number' => $lineItem['@OutputToken'],
                'external_creation_date' => $lineItem['@creationDate'],
                'external_url' => $externalUrl,
                'payload' => null,
                'priority' => 3,
            ];

            $orderStructured['jobs'][$j]['documents'][] = $documentStructured;
        }

        $result = parent::loadOrder($orderStructured);

        $tallies = $this->addArraysByKey($tallies, $result);

        return $tallies;
    }

}
