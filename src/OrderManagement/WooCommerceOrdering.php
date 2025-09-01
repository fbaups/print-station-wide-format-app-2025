<?php

namespace App\OrderManagement;

use arajcany\ToolBox\Utility\Security\Security;
use Cake\Utility\Hash;

class WooCommerceOrdering extends OrderManagementBase
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $orderData
     * @return array
     */
    public function loadOrder($orderData): array
    {
        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        if (is_file($orderData)) {
            $orderData = file_get_contents($orderData);
            $orderData = json_decode($orderData, JSON_OBJECT_AS_ARRAY);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->addDangerAlerts("Error decoding WooCommerce JSON.");
                return $tallies;
            }
        }

        if (!isset($orderData[0]['id'])) {
            $this->addDangerAlerts("Invalid  WooCommerce JSON");
            return $tallies;
        }

        foreach ($orderData as $order) {
            //save as original payload
            $payload = $order;
            if (isset($payload['line_items'])) {
                unset($payload['line_items']);
            }

            $orderStructured = [
                'guid' => Security::guid(),
                'order_status_id' => $this->getOrderStatusIdByName('Received'),
                'name' => "WC-{$order['id']}",
                'description' => "WooCommerce Order {$order['id']}",
                'external_system_type' => 'WooCommerce',
                'external_order_number' => $order['id'],
                'external_creation_date' => $order['date_created_gmt'],
                'payload' => $payload,
                'priority' => 3,
                'jobs' => [],
            ];

            foreach ($order['line_items'] as $j => $lineItem) {
                $jobStructured = [
                    'guid' => Security::guid(),
                    'job_status_id' => $this->getJobStatusIdByName('Received'),
                    'name' => $lineItem['name'],
                    'description' => "SKU={$lineItem['sku']} | ProductID={$lineItem['product_id']} | VariationID={$lineItem['variation_id']}",
                    'quantity' => $lineItem['quantity'],
                    'external_job_number' => $lineItem['id'],
                    'external_creation_date' => $order['date_created_gmt'],
                    'payload' => $lineItem,
                    'priority' => 3,
                    'documents' => [],
                ];
                $orderStructured['jobs'][$j] = $jobStructured;

                //extract the download URL of the documents
                $artworkUrls = [];
                $lineItemEntries = Hash::flatten($lineItem['meta_data']);
                foreach ($lineItemEntries as $lineItemEntryKey => $lineItemEntryValue) {
                    if (is_array($lineItemEntryValue) || empty($lineItemEntryValue)) {
                        continue;
                    }

                    if (str_contains($lineItemEntryValue, 'https:')) {
                        if (str_contains($lineItemEntryKey, '_gravity_form_lead')) {
                            if (!str_contains($lineItemEntryKey, 'source_url')) {
                                $url = stripslashes(trim($lineItemEntryValue, "[]\"\'"));
                                $url = str_replace("https://posterfactodev.wpengine.com/", "https://posterfactory.com.au/", $url);
                                $artworkUrls[] = $url;
                            }
                        }
                    }
                }
                $artworkUrls = array_unique($artworkUrls);
                foreach ($artworkUrls as $d => $artworkUrl) {
                    $artworkFilename = pathinfo($artworkUrl, PATHINFO_BASENAME);
                    $documentStructured = [
                        'guid' => Security::guid(),
                        'document_status_id' => $this->getDocumentStatusIdByName('Requested'),
                        'name' => $artworkFilename,
                        'description' => "SKU={$lineItem['sku']} | ProductID={$lineItem['product_id']} | VariationID={$lineItem['variation_id']}",
                        'artifact_token' => null,
                        'external_document_number' => null,
                        'external_creation_date' => $order['date_created_gmt'],
                        'external_url' => $artworkUrl,
                        'payload' => null,
                        'priority' => 3,
                    ];
                    $orderStructured['jobs'][$j]['documents'][$d] = $documentStructured;
                }
            }

            $result = parent::loadOrder($orderStructured);

            $tallies = $this->addArraysByKey($tallies, $result);
        }

        return $tallies;
    }


    private function getWooCommerceOrderStatusMap()
    {
        return [
            "pending" => "Pending payment",
            "processing" => "Processing",
            "on-hold" => "On hold",
            "completed" => "Order Confirmed",
            "cancelled" => "Cancelled",
            "refunded" => "Refunded",
            "failed" => "Failed",
            "checkout-draft" => "Draft",
        ];
    }

}
