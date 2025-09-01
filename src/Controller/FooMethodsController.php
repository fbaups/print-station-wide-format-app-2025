<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * FooMethods Controller
 *
 * @property \App\Model\Table\FooMethodsTable $FooMethods
 * @method \App\Model\Entity\FooMethod[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FooMethodsController extends AppController
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
        $this->set('typeMap', $this->FooMethods->getSchema()->typeMap());

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
                'foo_recipe_id',
                'rank',
                'text',
                'actions',
            ];

            $recordsTotal = $this->FooMethods->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->FooMethods->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $fooMethods = $this->FooMethods->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['id', 'rank', ' priority'],
                'text_fields' => ['name', 'description', 'text', 'first_name', 'last_name'],
            ];
            $fooMethods = $this->FooMethods->applyDatatablesQuickSearchFilter($fooMethods, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $fooMethods = $this->FooMethods->applyDatatablesColumnFilters($fooMethods, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $fooMethods->count());

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
                        $order['FooMethods.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $fooMethods = $this->paginate($fooMethods);
            $this->set(compact('fooMethods'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->FooMethods->getAllAlertsLogSequence());
            return;
        }

        $this->set('fooMethods', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Foo Method id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $fooMethod = $this->FooMethods->get($id, contain: ['FooRecipes']);

        $this->set(compact('fooMethod'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $fooMethod = $this->FooMethods->newEmptyEntity();
        if ($this->request->is('post')) {
            $fooMethod = $this->FooMethods->patchEntity($fooMethod, $this->request->getData());
            if ($this->FooMethods->save($fooMethod)) {
                $this->Flash->success(__('The foo method has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo method could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooMethods->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooMethod', 'fooRecipes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Foo Method id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $fooMethod = $this->FooMethods->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $fooMethod = $this->FooMethods->patchEntity($fooMethod, $this->request->getData());
            if ($this->FooMethods->save($fooMethod)) {
                $this->Flash->success(__('The foo method has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo method could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooMethods->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooMethod', 'fooRecipes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Foo Method id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $fooMethod = $this->FooMethods->get($id);
        if ($this->FooMethods->delete($fooMethod)) {
            $this->Flash->success(__('The foo method has been deleted.'));
        } else {
            $this->Flash->error(__('The foo method could not be deleted. Please, try again.'));
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

        $recordData = $this->FooMethods->redactEntity($id, ['']);

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
