<?php

namespace App\OrderManagement;

use App\Model\Entity\Order;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\OrdersTable;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Console\ConsoleIo;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use DateTime;
use League\Flysystem\Filesystem;

/**
 * Order Management
 * Used as the base class to insert Orders/Jobs/Documents into the DB
 *
 * An Order has many Jobs and Jobs has many Document
 *
 */
class OrderManagementBase
{
    use ReturnAlerts;

    protected Table|OrdersTable $Orders;
    protected array $orderStatusesList;
    protected array $jobStatusesList;
    protected array $documentStatusesList;

    public function __construct()
    {
        $this->ioCli = new ConsoleIo();
        $this->Orders = TableRegistry::getTableLocator()->get('Orders');

        $this->orderStatusesList = $this->Orders->OrderStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
        $this->jobStatusesList = $this->Orders->Jobs->JobStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
        $this->documentStatusesList = $this->Orders->Jobs->Documents->DocumentStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getOrderStatusIdByName($name): mixed
    {
        return $this->orderStatusesList[$name] ?? null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getJobStatusIdByName($name): mixed
    {
        return $this->jobStatusesList[$name] ?? null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getDocumentStatusIdByName($name): mixed
    {
        return $this->documentStatusesList[$name] ?? null;
    }

    /**
     * Simple way to load a single order into the Application.
     * Will populate the Orders/Jobs/Documents tables.
     *
     * $orderData needs to be in the format supplied by $this->getSampleOrderJobDoc()
     *
     * @param $orderData
     * @return array
     */
    public function loadOrder($orderData): array
    {
        $order = $orderData;
        $orderCount = 0;
        $jobCount = 0;
        $documentCount = 0;

        if (isset($order['jobs'])) {
            $jobs = $order['jobs'];
            unset($order['jobs']);
        } else {
            $jobs = [];
        }

        $orderEnt = $this->Orders->create($order);
        if (!$orderEnt) {
            $this->addDangerAlerts("Failed to create an Order.");
            $this->mergeAlerts($this->Orders->getAllAlertsForMerge());
            return ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];
        }

        $orderCount++;

        foreach ($jobs as $job) {
            if (isset($job['documents'])) {
                $documents = $job['documents'];
                unset($job['documents']);
            } else {
                $documents = [];
            }

            $job['order_id'] = $orderEnt->id;
            $jobEnt = $this->Orders->Jobs->create($job);
            if (!$jobEnt) {
                $this->addDangerAlerts("Failed to create a Job.");
                continue;
            }

            foreach ($documents as $document) {
                $document['job_id'] = $jobEnt->id;
                $documentEnt = $this->Orders->Jobs->Documents->create($document);
                if (!$documentEnt) {
                    $this->addDangerAlerts("Failed to create a Document.");
                    continue;
                }
                $documentCount++;
            }

            $jobCount++;
        }

        $this->mergeAlerts($this->Orders->getAllAlertsForMerge());
        $this->mergeAlerts($this->Orders->Jobs->getAllAlertsForMerge());
        $this->mergeAlerts($this->Orders->Jobs->Documents->getAllAlertsForMerge());
        return ['order_count' => $orderCount, 'job_count' => $jobCount, 'document_count' => $documentCount];
    }

    /**
     * Special delete function to delete an order.
     * Cascades down to Jobs, Documents and Artifacts.
     *
     * @param EntityInterface $entity
     * @param array $options
     * @return bool
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        /** @var Order[] $orders */
        $orders = $this->Orders->find('all')
            ->where(['id' => $entity->id])
            ->contain(['Jobs', 'Jobs.Documents']);

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        try {
            foreach ($orders as $order) {
                foreach ($order->jobs as $job) {
                    foreach ($job->documents as $document) {
                        if ($document->artifact_token) {
                            $Artifacts->deleteByToken($document->artifact_token);
                        }
                        $this->Orders->Jobs->Documents->delete($document);
                    }
                    $this->Orders->Jobs->delete($job);
                }
                $this->Orders->delete($order);
            }

            $this->mergeAlerts($this->Orders->getAllAlertsForMerge());
            $this->mergeAlerts($this->Orders->Jobs->getAllAlertsForMerge());
            $this->mergeAlerts($this->Orders->Jobs->Documents->getAllAlertsForMerge());

            return true;
        } catch (\Throwable $exception) {
            $this->mergeAlerts($this->Orders->getAllAlertsForMerge());
            $this->mergeAlerts($this->Orders->Jobs->getAllAlertsForMerge());
            $this->mergeAlerts($this->Orders->Jobs->Documents->getAllAlertsForMerge());

            $this->addDangerAlerts('Could not delete the Order. ' . $exception->getMessage());
            return false;
        }

    }

    protected function addArraysByKey($array1, $array2)
    {
        $result = [];

        foreach ($array1 as $key => $value) {
            if (isset($array2[$key])) {
                $result[$key] = $value + $array2[$key];
            } else {
                $result[$key] = $value;
            }
        }

        foreach ($array2 as $key => $value) {
            if (!isset($array1[$key])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }


    public function getSampleOrderJobDoc(): array
    {
        $order = $this->getSampleOrderData();
        $order['jobs'] = [];

        foreach (range(1, 3) as $j) {
            $order['jobs'][$j] = $this->getSampleJobData();
            $order['jobs'][$j]['documents'] = [];
            foreach (range(1, 3) as $d) {
                $order['jobs'][$j]['documents'][] = $this->getSampleDocumentData();
            }
        }

        return $order;
    }

    private function getSampleOrderData($options = []): array
    {
        $counter = microtimestamp();
        $data = [
            'guid' => Security::guid(),
            'order_status_id' => $this->Orders->OrderStatuses->findByNameOrAlias('Received')->first()->id,
            'name' => $counter,
            'description' => "Online order {$counter}",
            'external_order_number' => $counter,
            'external_creation_date' => new DateTime(),
            'payload' => null,
            'priority' => null,
            'hash_sum' => null,
        ];

        $data = array_merge($data, $options);

        return $data;
    }

    private function getSampleJobData($options = []): array
    {
        $name = $this->Orders->Jobs->makeRandomName();
        $counter = microtimestamp();
        $data = [
            'guid' => Security::guid(),
            'order_id' => null,
            'job_status_id' => $this->Orders->Jobs->JobStatuses->findByNameOrAlias('Received')->first()->id,
            'name' => $name,
            'description' => "A book about {$name}.",
            'quantity' => 1,
            'external_job_number' => $counter,
            'external_creation_date' => new DateTime(),
            'payload' => null,
            'priority' => null,
            'hash_sum' => null,
        ];

        $data = array_merge($data, $options);

        return $data;
    }

    private function getSampleDocumentData($options = []): array
    {
        $counter = microtimestamp();
        $data = [
            'guid' => Security::guid(),
            'job_id' => null,
            'document_status_id' => $this->Orders->Jobs->Documents->DocumentStatuses->findByNameOrAlias('Requested')->first()->id,
            'name' => 'Book File',
            'description' => null,
            'artifact_token' => null,
            'external_document_number' => $counter,
            'external_creation_date' => new DateTime(),
            'external_url' => "https://httpbin.org/get?{$counter}",
            'payload' => null,
            'priority' => null,
            'hash_sum' => null,
        ];

        $data = array_merge($data, $options);

        return $data;
    }

    /**
     * Delete a Temporary folder
     *
     * @param string $folder
     * @return void
     */
    public function cleanupTmpFolder(string $folder): void
    {
        $adapter = new LocalFilesystemAdapter($folder);
        $filesystem = new Filesystem($adapter);
        try {
            $filesystem->deleteDirectory('');
            $this->setReturnMessage('Successfully deleted folder.');
            $this->setReturnValue(0);
        } catch (\Throwable $exception) {
            $this->setReturnMessage('Unable to delete Folder. ' . $exception->getMessage());
            $this->setReturnValue(1);
        }
    }
}
