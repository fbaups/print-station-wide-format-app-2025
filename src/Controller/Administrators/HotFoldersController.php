<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Table\SeedsTable;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Cake\Utility\Text;
use Exception;

/**
 * HotFolders Controller
 *
 * @property \App\Model\Table\HotFoldersTable $HotFolders
 * @method \App\Model\Entity\HotFolder[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class HotFoldersController extends AppController
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
        $this->set('typeMap', $this->HotFolders->getSchema()->typeMap());
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);
        $this->FormProtection->setConfig('unlockedActions', ['submit']);
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
                'path',
                'workflow',
                'is_enabled',
                'submit_url_enabled',
                'actions',
            ];

            $recordsTotal = $this->HotFolders->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->HotFolders->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $hotFolders = $this->HotFolders->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['HotFolders.id', 'HotFolders.polling_interval', 'HotFolders.stable_interval'],
                'text_fields' => ['HotFolders.name', 'HotFolders.description', 'HotFolders.path', 'HotFolders.workflow'],
            ];
            $hotFolders = $this->HotFolders->applyDatatablesQuickSearchFilter($hotFolders, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $hotFolders = $this->HotFolders->applyDatatablesColumnFilters($hotFolders, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $hotFolders->count());

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
                        $order['HotFolders.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $hotFolders = $this->paginate($hotFolders);
            $this->set(compact('hotFolders'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->HotFolders->getAllAlertsLogSequence());
            return;
        }

        $this->set('hotFolders', []);
        $this->set('isAjax', $isAjax);

        $servicesStats = $this->BackgroundServicesAssistant->_getServicesStats();
        $this->set('servicesStats', $servicesStats);
    }

    /**
     * View method
     *
     * @param string|null $id Hot Folder id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $hotFolder = $this->HotFolders->get($id, contain: []);

        $this->set(compact('hotFolder'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $workflows = $this->HotFolders->getWorkflowClasses();
        $hotFolder = $this->HotFolders->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['path'] = TextFormatter::makeDirectoryTrailingBackwardSlash($data['path']);
            $data['submit_url'] = strtolower(Text::slug($data['name']));
            $hotFolder = $this->HotFolders->patchEntity($hotFolder, $data);
            if ($this->HotFolders->save($hotFolder)) {
                $this->Flash->success(__('The hot folder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The hot folder could not be saved. Please, try again.'));
        }
        $this->set(compact('hotFolder'));
        $this->set(compact('workflows'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Hot Folder id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $workflows = $this->HotFolders->getWorkflowClasses();
        $hotFolder = $this->HotFolders->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['path'] = TextFormatter::makeDirectoryTrailingBackwardSlash($data['path']);
            $data['submit_url'] = strtolower(Text::slug($data['name']));
            $hotFolder = $this->HotFolders->patchEntity($hotFolder, $data);
            if ($this->HotFolders->save($hotFolder)) {
                $this->Flash->success(__('The hot folder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The hot folder could not be saved. Please, try again.'));
        }
        $this->set(compact('hotFolder'));
        $this->set(compact('workflows'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Hot Folder id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $hotFolder = $this->HotFolders->get($id);
        if ($this->HotFolders->delete($hotFolder)) {
            $this->Flash->success(__('The hot folder has been deleted.'));
        } else {
            $this->Flash->error(__('The hot folder could not be deleted. Please, try again.'));
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

        $recordData = $this->HotFolders->redactEntity($id, ['']);

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
