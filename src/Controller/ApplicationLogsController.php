<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * ApplicationLogs Controller
 *
 * @property \App\Model\Table\ApplicationLogsTable $ApplicationLogs
 * @method \App\Model\Entity\ApplicationLog[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ApplicationLogsController extends AppController
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
        $this->set('typeMap', $this->ApplicationLogs->getSchema()->typeMap());

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
                'created',
                'level',
                'user_link',
                'url',
                'message',
                'actions',
            ];

            $recordsTotal = $this->ApplicationLogs->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->ApplicationLogs->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $applicationLogs = $this->ApplicationLogs->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['ApplicationLogs.id', 'ApplicationLogs.user_link'],
                'text_fields' => ['ApplicationLogs.level', 'ApplicationLogs.url', 'ApplicationLogs.message', 'ApplicationLogs.message_overflow'],
            ];
            $applicationLogs = $this->ApplicationLogs->applyDatatablesQuickSearchFilter($applicationLogs, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $applicationLogs = $this->ApplicationLogs->applyDatatablesColumnFilters($applicationLogs, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $applicationLogs->count());

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
                        $order['ApplicationLogs.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $applicationLogs = $this->paginate($applicationLogs);
            $this->set(compact('applicationLogs'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->ApplicationLogs->getAllAlertsLogSequence());
            return;
        }

        $this->set('applicationLogs', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Application Log id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id = null)
    {
        if (strtolower($id) === 'last') {
            $applicationLog = $this->ApplicationLogs->find('all', contain: [])
                ->orderByDesc('id')
                ->first();
        } elseif (strtolower($id) === 'first') {
            $applicationLog = $this->ApplicationLogs->find('all', contain: [])
                ->orderByAsc('id')
                ->first();
        } else {
            $applicationLog = $this->ApplicationLogs->get($id, contain: []);
        }

        $this->set(compact('applicationLog'));
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

        $recordData = $this->ApplicationLogs->redactEntity($id, ['']);

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
