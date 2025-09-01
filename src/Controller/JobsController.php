<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\Feedback\DebugSqlCapture;
use Cake\Event\EventInterface;
use Exception;

/**
 * Jobs Controller
 *
 * @property \App\Model\Table\JobsTable $Jobs
 * @method \App\Model\Entity\Job[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class JobsController extends AppController
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
        $this->set('typeMap', $this->Jobs->getSchema()->typeMap());

    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
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
                'Jobs.order_id',
                'Jobs.id',
                'Jobs.job_status_id',
                'Jobs.name',
                'Jobs.description',
                'Jobs.quantity',
                'Jobs.external_job_number',
                'Jobs.external_creation_date',
                'Jobs.priority',
                'actions',
            ];

            $recordsTotal = $this->Jobs->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->Jobs->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $jobs = $this->Jobs->find('all');
            $jobs->contain(['Orders', 'JobStatuses']);

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Jobs.id', 'Jobs.rank', 'Jobs.priority', 'Jobs.quantity'],
                'text_fields' => ['Jobs.name', 'Jobs.description', 'Jobs.text', 'Jobs.external_job_number', 'Jobs.last_name'],
            ];
            $jobs = $this->Jobs->applyDatatablesQuickSearchFilter($jobs, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $jobs = $this->Jobs->applyDatatablesColumnFilters($jobs, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $jobs->count());

            $this->viewBuilder()->setLayout('ajax');
            $this->response = $this->response->withType('json');
            $isAjax = true;
            $this->set('datatablesQuery', $datatablesQuery);

            $sorting = $this->Jobs->applyDatatablesSorting($jobs, $datatablesQuery, $headers);
            $jobs->orderBy($sorting);

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
            ];
            $jobs = $this->paginate($jobs);
            $this->set(compact('jobs'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Jobs->getAllAlertsLogSequence());
            return;
        }

        $this->set('jobs', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Job id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $job = $this->Jobs->get($id, contain: ['Orders', 'JobStatuses', 'Users', 'Documents', 'JobAlerts', 'JobProperties', 'JobStatusMovements'],);

        $this->set(compact('job'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Job id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $job = $this->Jobs->get($id, contain: ['Users']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $job = $this->Jobs->patchEntity($job, $this->request->getData());
            if ($this->Jobs->save($job)) {
                $this->Flash->success(__('The job has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The job could not be saved. Please, try again.'));
        }
        $orders = $this->Jobs->Orders->find('list', ['limit' => 200])->all();
        $jobStatuses = $this->Jobs->JobStatuses->find('list', ['limit' => 200])->all();
        $users = $this->Jobs->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('job', 'orders', 'jobStatuses', 'users'));
    }

    /**
     * Delete method
     *
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->Flash->error(__('Deleting Jobs is not allowed. Please manage the Order this Job belongs to.'));

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

        $recordData = $this->Jobs->redactEntity($id, ['']);

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
