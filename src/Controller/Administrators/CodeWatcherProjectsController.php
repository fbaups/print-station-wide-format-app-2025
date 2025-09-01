<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Entity\CodeWatcherProject;
use App\Utility\CodeWatcher\Sweeper;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Exception;

/**
 * CodeWatcherProjects Controller
 *
 * @property \App\Model\Table\CodeWatcherProjectsTable $CodeWatcherProjects
 * @method CodeWatcherProject[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class CodeWatcherProjectsController extends AppController
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
        $this->set('typeMap', $this->CodeWatcherProjects->getSchema()->typeMap());

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
                'id',
                'name',
                'description',
                'enable_tracking',
                'actions',
            ];

            $recordsTotal = $this->CodeWatcherProjects->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->CodeWatcherProjects->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $codeWatcherProjects = $this->CodeWatcherProjects->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['CodeWatcherProjects.id'],
                'text_fields' => ['CodeWatcherProjects.name', 'CodeWatcherProjects.description', 'CodeWatcherProjects.text'],
            ];
            $codeWatcherProjects = $this->CodeWatcherProjects->applyDatatablesQuickSearchFilter($codeWatcherProjects, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $codeWatcherProjects = $this->CodeWatcherProjects->applyDatatablesColumnFilters($codeWatcherProjects, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $codeWatcherProjects->count());

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
                        $order['CodeWatcherProjects.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $codeWatcherProjects = $this->paginate($codeWatcherProjects);
            $this->set(compact('codeWatcherProjects'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->CodeWatcherProjects->getAllAlertsLogSequence());
            return;
        }

        $this->set('codeWatcherProjects', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Code Watcher Project id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $codeWatcherProject = $this->CodeWatcherProjects->get($id, [
            'contain' => ['CodeWatcherFolders'],
        ]);

        $this->set(compact('codeWatcherProject'));

        $timeStart = (new DateTime())->subMonths(1)->startOfMonth();
        $timeEnd = (clone $timeStart)->endOfMonth();
        $activityData = $this->CodeWatcherProjects->getDailyActivityInTimeRange($codeWatcherProject->id, $timeStart, $timeEnd);
        $activityData = json_encode($activityData);
        $activitySum = $this->CodeWatcherProjects->sumActivityInTimeRange($codeWatcherProject->id, $timeStart, $timeEnd);
        $this->set('activityDataLastMonth', $activityData);
        $this->set('activitySumLastMonth', $activitySum);

        $activityData = $this->CodeWatcherProjects->getDailyActivityInTimeRange($codeWatcherProject->id);
        $activityData = json_encode($activityData);
        $activitySum = $this->CodeWatcherProjects->sumActivityInTimeRange($codeWatcherProject->id);
        $this->set('activityDataThisMonth', $activityData);
        $this->set('activitySumThisMonth', $activitySum);


        $activityDataRawThisMonth = $this->CodeWatcherProjects->getDailyActivityInTimeRange($codeWatcherProject->id, asRawData: true);
        $activityDataRawLastMonth = $this->CodeWatcherProjects->getDailyActivityInTimeRange($codeWatcherProject->id, $timeStart, $timeEnd, true);
        $this->set('activityDataRawThisMonth', $activityDataRawThisMonth);
        $this->set('activityDataRawLastMonth', $activityDataRawLastMonth);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $codeWatcherProject = $this->CodeWatcherProjects->newEmptyEntity();
        if ($this->request->is('post')) {
            $codeWatcherProject = $this->CodeWatcherProjects->patchEntity($codeWatcherProject, $this->request->getData());
            if ($this->CodeWatcherProjects->save($codeWatcherProject)) {
                $this->Flash->success(__('The code watcher project has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The code watcher project could not be saved. Please, try again.'));
        }
        $this->set(compact('codeWatcherProject'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Code Watcher Project id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $codeWatcherProject = $this->CodeWatcherProjects->get($id, [
            'contain' => ['CodeWatcherFolders'],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['code_watcher_folders'] = $this->CodeWatcherProjects->CodeWatcherFolders->sanitiseFormData($data['code_watcher_folders']);
            //dd($data);
            $codeWatcherProject = $this->CodeWatcherProjects->patchEntity($codeWatcherProject, $data);
            if ($this->CodeWatcherProjects->save($codeWatcherProject)) {
                $this->Flash->success(__('The code watcher project has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The code watcher project could not be saved. Please, try again.'));
        }
        $this->set(compact('codeWatcherProject'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Code Watcher Project id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $codeWatcherProject = $this->CodeWatcherProjects->get($id);
        if ($this->CodeWatcherProjects->delete($codeWatcherProject)) {
            $this->Flash->success(__('The code watcher project has been deleted.'));
        } else {
            $this->Flash->error(__('The code watcher project could not be deleted. Please, try again.'));
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

        $recordData = $this->CodeWatcherProjects->redactEntity($id, ['']);

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
     * Edit method
     *
     * @param string|null $id Code Watcher Project id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function compare()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $this->viewBuilder()->setTemplate('compare-projects');

            $data = $this->request->getData();
            $leftId = intval($data['left-project']);
            $rightId = intval($data['right-project']);

            $Sweeper = new Sweeper();

            /**
             * @var CodeWatcherProject $leftProject
             */
            $leftProject = $this->CodeWatcherProjects->find('all')->where(['id' => $leftId])->first();
            $this->set("leftProject", $leftProject);
            $leftProjectFiles = $Sweeper->captureFso($leftProject);
            $this->set("leftProjectFiles", $leftProjectFiles);

            /**
             * @var CodeWatcherProject $rightProject
             */
            $rightProject = $this->CodeWatcherProjects->find('all')->where(['id' => $rightId])->first();
            $this->set("rightProject", $rightProject);
            $rightProjectFiles = $Sweeper->captureFso($rightId);
            $this->set("rightProjectFiles", $rightProjectFiles);

            return;
        }

        $codeWatcherProjects = $this->CodeWatcherProjects->find('all');
        $this->viewBuilder()->setTemplate('compare-select');
        $this->set(compact('codeWatcherProjects'));
    }


    public function compareFiles($l = null, $r = null, $leftFile = null, $rightFile = null, $leftFileFullPath = null, $rightFileFullPath = null)
    {
        if (is_null($l) || is_null($r) || is_null($leftFile) || is_null($rightFile)) {
            return $this->redirect(['controller' => 'select']);
        }

        /**
         * @var CodeWatcherProject $leftProject
         */
        $leftProject = $this->CodeWatcherProjects->find('all')->where(['id' => $l])->first();
        $this->set("leftProject", $leftProject);

        /**
         * @var CodeWatcherProject $rightProject
         */
        $rightProject = $this->CodeWatcherProjects->find('all')->where(['id' => $r])->first();
        $this->set("rightProject", $rightProject);


        $this->set("leftFile", $leftFile);
        $this->set("rightFile", $rightFile);
        $this->set("leftFileFullPath", $leftFileFullPath);
        $this->set("rightFileFullPath", $rightFileFullPath);

    }

    public function pushFile()
    {
        $data = $this->request->getData();
        if ($data['direction'] === 'left-to-right') {
            $input = $data['leftFile'];
            $output = $data['rightFile'];

            $inputName = $data['leftProjectName'];
            $outputName = $data['rightProjectName'];
        } elseif ($data['direction'] === 'right-to-left') {
            $input = $data['rightFile'];
            $output = $data['leftFile'];

            $inputName = $data['rightProjectName'];
            $outputName = $data['leftProjectName'];
        } else {
            $this->Flash->error(__('Incorrect direction specified.'));
            return $this->redirect(['action' => 'code-watcher-projects']);
        }

        if (is_file($input) && is_file($output)) {
            $contents = file_get_contents($input);
            $result = file_put_contents($output, $contents);

            if ($result) {
                $this->Flash->success(__('File copied from {0} to {1}', $inputName, $outputName));
            } else {
                $this->Flash->error(__('Could not copy file from {0} to {1}', $inputName, $outputName));
            }

        } else {
            $this->Flash->error(__('The specified files do not exist.'));
            return $this->redirect(['action' => 'code-watcher-projects']);
        }

    }

}
