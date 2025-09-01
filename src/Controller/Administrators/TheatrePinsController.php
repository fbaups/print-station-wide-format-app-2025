<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Exception;

/**
 * TheatrePins Controller
 *
 * @property \App\Model\Table\TheatrePinsTable $TheatrePins
 * @method \App\Model\Entity\TheatrePin[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TheatrePinsController extends AppController
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
        $this->set('typeMap', $this->TheatrePins->getSchema()->typeMap());

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
                'pin_code',
                'user_link',
                'actions',
            ];

            $recordsTotal = $this->TheatrePins->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->TheatrePins->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $theatrePins = $this->TheatrePins->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['TheatrePins.id', 'TheatrePins.user_link'],
                'text_fields' => ['TheatrePins.name', 'TheatrePins.description', 'TheatrePins.pin_code'],
            ];
            $theatrePins = $this->TheatrePins->applyDatatablesQuickSearchFilter($theatrePins, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $theatrePins = $this->TheatrePins->applyDatatablesColumnFilters($theatrePins, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $theatrePins->count());

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
                        $order['TheatrePins.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $theatrePins = $this->paginate($theatrePins);
            $this->set(compact('theatrePins'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->TheatrePins->getAllAlertsLogSequence());
            return;
        }

        $this->set('theatrePins', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Theatre Pin id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $theatrePin = $this->TheatrePins->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('theatrePin'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $theatrePin = $this->TheatrePins->newEmptyEntity();
        if ($this->request->is('post')) {
            $theatrePin = $this->TheatrePins->patchEntity($theatrePin, $this->request->getData());
            $theatrePin->user_link = $this->AuthUser->id();
            if ($this->TheatrePins->save($theatrePin)) {
                $this->Flash->success(__('The theatre pin has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The theatre pin could not be saved. Please, try again.'));
        }
        $this->set(compact('theatrePin'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Theatre Pin id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $theatrePin = $this->TheatrePins->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $theatrePin = $this->TheatrePins->patchEntity($theatrePin, $this->request->getData());
            $theatrePin->user_link = $this->AuthUser->id();
            if ($this->TheatrePins->save($theatrePin)) {
                $this->Flash->success(__('The theatre pin has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The theatre pin could not be saved. Please, try again.'));
        }
        $this->set(compact('theatrePin'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Theatre Pin id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $theatrePin = $this->TheatrePins->get($id);
        if ($this->TheatrePins->delete($theatrePin)) {
            $this->Flash->success(__('The theatre pin has been deleted.'));
        } else {
            $this->Flash->error(__('The theatre pin could not be deleted. Please, try again.'));
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

        $recordData = $this->TheatrePins->redactEntity($id, ['']);

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
