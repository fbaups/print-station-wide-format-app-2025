<?php
declare(strict_types=1);

namespace App\Controller\Producers;

use App\Controller\AppController;
use App\Model\Table\OutputProcessorsTable;
use App\OrderManagement\OrderManagementBase;
use App\OutputProcessor\Handler\OutputProcessorHandlerForOrdersJobsDocuments;
use App\OutputProcessor\OutputProcessorBase;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Orders Controller
 *
 * @property \App\Model\Table\OrdersTable $Orders
 * @method \App\Model\Entity\Order[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdersController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->set('typeMap', $this->Orders->getSchema()->typeMap());

    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        $this->FormProtection->setConfig('unlockedActions', ['outputProcessor']);
        $this->FormProtection->setConfig('unlockedActions', ['index']); //allow index for DataTables index refresh

        //prevent all actions from needing CSRF Token validation for AJAX requests
        //if ($this->request->is('ajax')) {
        //    $this->FormProtection->setConfig('validate', false);
        //}
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $isAjax = false;

        if ($this->request->is('ajax')) {
            //DataTables POSTed the data as a querystring, parse and assign to $datatablesQuery
            parse_str($this->request->getBody()->getContents(), $datatablesQuery);

            //$headers must match the View
            $headers = [
                'Orders.id',
                'order_status_id',
                'Orders.name',
                'Orders.description',
                'Orders.quantity',
                'Orders.external_order_number',
                'Orders.external_creation_date',
                'actions',
            ];

            $recordsTotal = $this->Orders->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->Orders->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $orders = $this->Orders->find('all');
            $orders->contain(['OrderStatuses']);

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Orders.id', 'Orders.order_status_id', 'Orders.priority'],
                'text_fields' => ['Orders.name', 'Orders.description', 'Orders.text', 'Orders.first_name', 'Orders.last_name'],
            ];
            $orders = $this->Orders->applyDatatablesQuickSearchFilter($orders, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $orders = $this->Orders->applyDatatablesColumnFilters($orders, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $orders->count());

            $this->viewBuilder()->setLayout('ajax');
            $this->response = $this->response->withType('json');
            $isAjax = true;
            $this->set('datatablesQuery', $datatablesQuery);

            $sorting = $this->Orders->applyDatatablesSorting($orders, $datatablesQuery, $headers);
            $orders->orderBy($sorting);

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
            ];
            $orders = $this->paginate($orders);
            $this->set(compact('orders'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Orders->getAllAlertsLogSequence());
            return;
        }

        $this->set('orders', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $order = $this->Orders->get($id, contain: ['OrderStatuses', 'Users', 'Jobs', 'OrderAlerts', 'OrderProperties', 'OrderStatusMovements']);

        $this->set(compact('order'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $order = $this->Orders->get($id, contain: ['Users']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $order = $this->Orders->patchEntity($order, $this->request->getData());
            if ($this->Orders->save($order)) {
                $this->Flash->success(__('The order has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The order could not be saved. Please, try again.'));
        }
        $orderStatuses = $this->Orders->OrderStatuses->find('list', ['limit' => 200])->all();
        $users = $this->Orders->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('order', 'orderStatuses', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Order id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $order = $this->Orders->get($id);

        /**
         * Delete via OrderManagement as it has cascading deletes
         */
        $OrderManagement = new OrderManagementBase();

        if ($OrderManagement->delete($order)) {
            $this->Flash->success(__('The order has been deleted.'));
        } else {
            $this->Flash->error(__('The order could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Preview method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function preview($id = null, $format = 'json')
    {
        if (!$this->request->is(['ajax', 'get'])) {
            $responseData = json_encode(false, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
        }

        $recordData = $this->Orders->redactEntity($id, ['']);

        if (strtolower($format) === 'json') {
            $responseData = json_encode($recordData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        } else {
            $this->viewBuilder()->setLayout('ajax');
            $this->viewBuilder()->setTemplatePath('DataTablesPreviewer');
            $this->set(compact('recordData'));
        }

    }

    /**
     * Output Processor method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function outputProcessorModal($id = null)
    {
        if (!$this->request->is(['ajax', 'get'])) {
            return $this->redirect(['action' => 'index']);
        }

        if (empty($id)) {
            return $this->redirect(['action' => 'index']);
        }

        $this->viewBuilder()->setLayout('ajax');

        $id = intval($id);

        $orderData = $this->Orders->getCompleteOrder($id);
        $this->set('orderData', $orderData);

        $artifacts = $this->Orders->getArtifacts($id);
        $this->set('validArtifacts', $artifacts);

        $OP = new OutputProcessorBase();
        $outputProcessorTypes = $OP->getOutputProcessorTypes();
        $this->set('outputProcessorTypes', $outputProcessorTypes);

        /** @var OutputProcessorsTable $OutputPrcessorsTable */
        $OutputProcessorsTable = TableRegistry::getTableLocator()->get('OutputProcessors');
        $outputProcessorsList = $OutputProcessorsTable->getActiveList();
        $this->set('outputProcessorsList', $outputProcessorsList);
    }

    /**
     * Output Processor method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function outputProcessor($id = null)
    {
        if (!$this->request->is(['ajax', 'post'])) {
            return $this->redirect(['action' => 'index']);
        }

        $defaultResponse = [
            'status' => false,
            'errand_count_success' => 0,
        ];

        if (empty($id)) {
            $responseData = json_encode($defaultResponse, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        }

        $id = intval($id);
        $orderId = intval($this->request->getData('order-id'));
        if ($orderId !== $id) {
            $responseData = json_encode($defaultResponse, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        }

        $outputProcessorId = intval($this->request->getData('output-processor-id'));
        if (empty($outputProcessorId)) {
            $responseData = json_encode($defaultResponse, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        }

        $Handler = new OutputProcessorHandlerForOrdersJobsDocuments();
        $response = $Handler->outputProcessOrder($outputProcessorId, $orderId);
        $responseData = json_encode($response, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

}
