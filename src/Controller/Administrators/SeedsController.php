<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Table\SeedsTable;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Exception;

/**
 * Seeds Controller
 *
 * @property \App\Model\Table\SeedsTable $Seeds
 * @method \App\Model\Entity\Seed[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SeedsController extends AppController
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
        $this->set('typeMap', $this->Seeds->getSchema()->typeMap());

    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);
        $this->FormProtection->setConfig('unlockedActions', ['index', 'dataReceiverTokens', 'hotFolderTokens']); //allow index for DataTables index refresh

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
                'activation',
                'expiration',
                'token',
                'bids',
                'bid_limit',
                'user_link',
                'actions',
            ];

            $recordsTotal = $this->Seeds->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->Seeds->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $seeds = $this->Seeds->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Seeds.id', 'Seeds.user_link'],
                'text_fields' => ['Seeds.token', 'Seeds.url'],
            ];
            $seeds = $this->Seeds->applyDatatablesQuickSearchFilter($seeds, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $seeds = $this->Seeds->applyDatatablesColumnFilters($seeds, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $seeds->count());

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
                        $order['Seeds.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $seeds = $this->paginate($seeds);
            $this->set(compact('seeds'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Seeds->getAllAlertsLogSequence());
            return;
        }

        $this->set('seeds', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Seed id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $seed = $this->Seeds->get($id, contain: []);

        $this->set(compact('seed'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $seed = $this->Seeds->createSeed();
        if ($seed) {
            $this->Flash->success(__('Seed ID {0} has been created.', $seed->id));
        } else {
            $this->Flash->error(__('Seed could not be created. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Seed id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $seed = $this->Seeds->get($id);
        if ($this->Seeds->delete($seed)) {
            $this->Flash->success(__('The seed has been deleted.'));
        } else {
            $this->Flash->error(__('The seed could not be deleted. Please, try again.'));
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

        $recordData = $this->Seeds->redactEntity($id, ['']);

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


    public function hotFolderTokens()
    {
        /** @var SeedsTable $Seeds */
        $Seeds = $this->fetchTable('Seeds');
        $this->set('typeMapSeeds', $Seeds->getSchema()->typeMap());

        $isAjax = false;

        if ($this->request->is('ajax')) {
            //DataTables POSTed the data as a querystring, parse and assign to $datatablesQuery
            parse_str($this->request->getBody()->getContents(), $datatablesQuery);

            //$headers must match the View
            $headers = [
                'id',
                'activation',
                'expiration',
                'token',
                'bids',
                'actions',
            ];

            $recordsTotal = $Seeds->find('all')
                ->select(['id'], true)
                ->where("url Like '%/hot-folders/submit%'")
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$Seeds->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $seeds = $Seeds->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Seeds.id', 'Seeds.user_link'],
                'text_fields' => ['Seeds.token', 'Seeds.url'],
            ];
            $seeds = $Seeds->applyDatatablesQuickSearchFilter($seeds, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $seeds = $Seeds->applyDatatablesColumnFilters($seeds, $datatablesQuery, $headers);

            //limit to HotFolders-Submit Seeds
            $seeds = $seeds->where("url Like '%/hot-folders/submit%'");

            //final filtered count
            $this->set('recordsFiltered', $seeds->count());

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
                        $order['Seeds.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $seeds = $this->paginate($seeds);
            $this->set(compact('seeds'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $Seeds->getAllAlertsLogSequence());
            return;
        }

        $this->set('seeds', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * @return \Cake\Http\Response|void|null
     */
    public function hotFolderTokenAdd()
    {
        $this->request->allowMethod(['post', 'patch', 'put']);

        /** @var SeedsTable $Seeds */
        $Seeds = $this->fetchTable('Seeds');
        $token = sha1(Security::randomBytes(10240));

        $options = [
            'activation' => new DateTime(),
            'expiration' => new DateTime('+ ' . 50 . ' years'),
            'token' => $token,
            'url' => ['prefix' => false, 'controller' => 'hot-folders', 'action' => 'submit'],
            'bids' => 0,
            'bid_limit' => -1,
            'user_link' => 0,
        ];
        $seed = $Seeds->createSeed($options);

        if ($seed) {
            $this->Flash->success(__('Hot Folder Token {0} has been created.', $seed->token));

            return $this->redirect(['action' => 'hot-folder-tokens']);
        }
        $this->Flash->error(__('A new Hot Folder Token could not be created. Please, try again.'));
    }

    /**
     * @return \Cake\Http\Response|void|null
     */
    public function hotFolderTokenDelete($token)
    {
        $this->request->allowMethod(['post', 'delete']);

        /** @var SeedsTable $Seeds */
        $Seeds = $this->fetchTable('Seeds');

        if ($Seeds->deleteAll(['token' => $token])) {
            $this->Flash->success(__('Hot Folder Token {0} has been deleted.', $token));

            return $this->redirect(['action' => 'hot-folder-tokens']);
        }
        $this->Flash->error(__('A new Hot Folder Token could not be deleted. Please, try again.'));
    }

    public function dataReceiverTokens()
    {
        /** @var SeedsTable $Seeds */
        $Seeds = $this->fetchTable('Seeds');
        $this->set('typeMapSeeds', $Seeds->getSchema()->typeMap());

        $isAjax = false;

        if ($this->request->is('ajax')) {
            //DataTables POSTed the data as a querystring, parse and assign to $datatablesQuery
            parse_str($this->request->getBody()->getContents(), $datatablesQuery);

            //$headers must match the View
            $headers = [
                'id',
                'activation',
                'expiration',
                'token',
                'bids',
                'actions',
            ];

            $recordsTotal = $Seeds->find('all')
                ->select(['id'], true)
                ->where("url Like '%/data-receiver/submit%'")
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$Seeds->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $seeds = $Seeds->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Seeds.id', 'Seeds.user_link'],
                'text_fields' => ['Seeds.token', 'Seeds.url'],
            ];
            $seeds = $Seeds->applyDatatablesQuickSearchFilter($seeds, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $seeds = $Seeds->applyDatatablesColumnFilters($seeds, $datatablesQuery, $headers);

            //limit to DataReceiver Seeds
            $seeds = $seeds->where("url Like '%/data-receiver%'");

            //final filtered count
            $this->set('recordsFiltered', $seeds->count());

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
                        $order['Seeds.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $seeds = $this->paginate($seeds);
            $this->set(compact('seeds'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $Seeds->getAllAlertsLogSequence());
            return;
        }

        $this->set('seeds', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * @return \Cake\Http\Response|void|null
     */
    public function dataReceiverTokenAdd()
    {
        $this->request->allowMethod(['post', 'patch', 'put']);

        /** @var SeedsTable $Seeds */
        $Seeds = $this->fetchTable('Seeds');
        $token = sha1(Security::randomBytes(10240));

        $options = [
            'activation' => new DateTime(),
            'expiration' => new DateTime('+ ' . 50 . ' years'),
            'token' => $token,
            'url' => ['prefix' => false, 'controller' => 'data-receiver'],
            'bids' => 0,
            'bid_limit' => -1,
            'user_link' => 0,
        ];
        $seed = $Seeds->createSeed($options);

        if ($seed) {
            $this->Flash->success(__('Data Receiver Token {0} has been created.', $seed->token));

            return $this->redirect(['action' => 'data-receiver-tokens']);
        }
        $this->Flash->error(__('A new Data Receiver Token could not be created. Please, try again.'));
    }

    /**
     * @return \Cake\Http\Response|void|null
     */
    public function dataReceiverTokenDelete($token)
    {
        $this->request->allowMethod(['post', 'delete']);

        /** @var SeedsTable $Seeds */
        $Seeds = $this->fetchTable('Seeds');

        if ($Seeds->deleteAll(['token' => $token])) {
            $this->Flash->success(__('Data Receiver Token {0} has been deleted.', $token));

            return $this->redirect(['action' => 'data-receiver-tokens']);
        }
        $this->Flash->error(__('A new Data Receiver Token could not be deleted. Please, try again.'));
    }

}
