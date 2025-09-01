<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * Errands Controller
 *
 * @property \App\Model\Table\ErrandsTable $Errands
 * @method \App\Model\Entity\Errand[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ErrandsController extends AppController
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
        $this->set('typeMap', $this->Errands->getSchema()->typeMap());

    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);

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
            $datatablesQuery = $this->request->getQuery();

            //$headers must match the View
            $headers = [
                'id',
                'name',
                'class',
                'method',
                'parameters',
                'status',
                'started',
                'completed',
                'priority',
                'return_value',
                'lock_to_thread',
                'actions',
            ];

            $recordsTotal = $this->Errands->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            $this->Errands->convertJsonFieldsToString('parameters');

            //create a Query
            $errands = $this->Errands->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['id', 'rank', ' priority', 'return_value'],
                'text_fields' => ['name', 'background_service_name', 'class', 'method', 'status', 'return_message'],
            ];
            $errands = $this->Errands->applyDatatablesQuickSearchFilter($errands, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $errands = $this->Errands->applyDatatablesColumnFilters($errands, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $errands->count());

            $this->viewBuilder()->setLayout('ajax');
            $this->response = $this->response->withType('json');
            $isAjax = true;
            $this->set('datatablesQuery', $datatablesQuery);

            $order = [];
            if (isset($datatablesQuery['order']) && is_array($datatablesQuery['order'])) {
                foreach ($datatablesQuery['order'] as $item) {
                    if (isset($headers[$item['column']])) {
                        $orderBy = $headers[$item['column']];
                        $orderDirection = $item['dir'];
                        $order['Errands.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $errands = $this->paginate($errands);
            $this->set(compact('errands'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Errands->getAllAlertsLogSequence());
            return;
        }

        $this->set('errands', []);
        $this->set('isAjax', $isAjax);

        $servicesStats = $this->BackgroundServicesAssistant->_getServicesStats();
        $this->set('servicesStats', $servicesStats);
    }

    /**
     * View method
     *
     * @param string|null $id Errand id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $errand = $this->Errands->get($id, contain: []);

        $this->set(compact('errand'));
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

        $recordData = $this->Errands->redactEntity($id, ['created']);

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

}
