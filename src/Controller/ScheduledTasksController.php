<?php
declare(strict_types=1);

namespace App\Controller;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\ScheduledTaskWorkflows\Base\CronExpression;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Exception;

/**
 * ScheduledTasks Controller
 *
 * @property \App\Model\Table\ScheduledTasksTable $ScheduledTasks
 * @method \App\Model\Entity\ScheduledTask[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ScheduledTasksController extends AppController
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
        $this->set('typeMap', $this->ScheduledTasks->getSchema()->typeMap());
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        $this->FormProtection->setConfig('unlockedActions', ['checkCronExpression']);

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
                'schedule',
                'workflow',
                'is_enabled',
                'next_run_time',
                'actions',
            ];

            $recordsTotal = $this->ScheduledTasks->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->ScheduledTasks->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $scheduledTasks = $this->ScheduledTasks->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['ScheduledTasks.id'],
                'text_fields' => ['ScheduledTasks.name', 'ScheduledTasks.description', 'ScheduledTasks.schedule', 'ScheduledTasks.workflow',],
            ];
            $scheduledTasks = $this->ScheduledTasks->applyDatatablesQuickSearchFilter($scheduledTasks, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $scheduledTasks = $this->ScheduledTasks->applyDatatablesColumnFilters($scheduledTasks, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $scheduledTasks->count());

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
                        $order['ScheduledTasks.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $scheduledTasks = $this->paginate($scheduledTasks);
            $this->set(compact('scheduledTasks'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->ScheduledTasks->getAllAlertsLogSequence());
            return;
        }

        $this->set('scheduledTasks', []);
        $this->set('isAjax', $isAjax);

        $servicesStats = $this->BackgroundServicesAssistant->_getServicesStats();
        $this->set('servicesStats', $servicesStats);
    }

    /**
     * View method
     *
     * @param string|null $id Scheduled Task id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $scheduledTask = $this->ScheduledTasks->get($id);

        $this->set(compact('scheduledTask'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $workflows = $this->ScheduledTasks->getWorkflowClasses();
        $scheduledTask = $this->ScheduledTasks->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $scheduledTask = $this->ScheduledTasks->patchEntity($scheduledTask, $data);
            if ($this->ScheduledTasks->save($scheduledTask)) {
                $this->Flash->success(__('The scheduled task has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The scheduled task could not be saved. Please, try again.'));
        }
        $this->set(compact('scheduledTask'));
        $this->set(compact('workflows'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Scheduled Task id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $workflows = $this->ScheduledTasks->getWorkflowClasses();
        $scheduledTask = $this->ScheduledTasks->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $scheduledTask = $this->ScheduledTasks->patchEntity($scheduledTask, $data);
            if ($this->ScheduledTasks->save($scheduledTask)) {
                $this->Flash->success(__('The scheduled task has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The scheduled task could not be saved. Please, try again.'));
        }
        $this->set(compact('scheduledTask'));
        $this->set(compact('workflows'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Scheduled Task id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $scheduledTask = $this->ScheduledTasks->get($id);
        if ($this->ScheduledTasks->delete($scheduledTask)) {
            $this->Flash->success(__('The scheduled task has been deleted.'));
        } else {
            $this->Flash->error(__('The scheduled task could not be deleted. Please, try again.'));
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

        $recordData = $this->ScheduledTasks->redactEntity($id, ['']);

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
     * @return \Cake\Http\Response|null
     */
    public function checkCronExpression(): ?\Cake\Http\Response
    {
        if (!$this->request->is(['ajax'])) {
            return $this->redirect(['action' => 'index']);
        }

        $schedule = trim($this->request->getData('schedule'));

        $cron = new CronExpression($schedule, LCL_TZ);
        $isValid = $cron->isValid();
        if ($isValid) {
            $nextRun = $cron->getNext();
            $nextRun = new DateTime($nextRun);
            $nextRun = $nextRun->setTimezone('UTC');
            $nextRunLocal = (clone $nextRun)->setTimezone(LCL_TZ);

            $nextRun = $nextRun->format('l jS \o\f F Y h:i:s A ') . LCL_TZ . " Local Time";
            $nextRunLocal = $nextRunLocal->format('l jS \o\f F Y h:i:s A ') . LCL_TZ. " Local Time";
        } else {
            $nextRun = false;
            $nextRunLocal = false;
        }

        $result = [
            'is_valid' => $isValid,
            'next_run_time_utc' => $nextRun,
            'next_run_time_local' => $nextRunLocal,
            'local_timezone' => LCL_TZ,
            'schedule' => $schedule,
        ];

        $responseData = json_encode($result, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

}
