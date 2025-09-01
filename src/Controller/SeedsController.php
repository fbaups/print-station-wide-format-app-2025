<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
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

}
