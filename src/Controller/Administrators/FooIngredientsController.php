<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Exception;

/**
 * FooIngredients Controller
 *
 * @property \App\Model\Table\FooIngredientsTable $FooIngredients
 * @method \App\Model\Entity\FooIngredient[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FooIngredientsController extends AppController
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
        $this->set('typeMap', $this->FooIngredients->getSchema()->typeMap());

    }

    public function beforeFilter(EventInterface $event)
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
                'foo_recipe_id',
                'rank',
                'text',
                'actions',
            ];

            $recordsTotal = $this->FooIngredients->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->FooIngredients->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $fooIngredients = $this->FooIngredients->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['id', 'rank', ' priority'],
                'text_fields' => ['name', 'description', 'text', 'first_name', 'last_name'],
            ];
            $fooIngredients = $this->FooIngredients->applyDatatablesQuickSearchFilter($fooIngredients, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $fooIngredients = $this->FooIngredients->applyDatatablesColumnFilters($fooIngredients, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $fooIngredients->count());

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
                        $order['FooIngredients.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $fooIngredients = $this->paginate($fooIngredients);
            $this->set(compact('fooIngredients'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->FooIngredients->getAllAlertsLogSequence());
            return;
        }

        $this->set('fooIngredients', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Foo Ingredient id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $fooIngredient = $this->FooIngredients->get($id, contain: ['FooRecipes']);

        $this->set(compact('fooIngredient'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $fooIngredient = $this->FooIngredients->newEmptyEntity();
        if ($this->request->is('post')) {
            $fooIngredient = $this->FooIngredients->patchEntity($fooIngredient, $this->request->getData());
            if ($this->FooIngredients->save($fooIngredient)) {
                $this->Flash->success(__('The foo ingredient has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo ingredient could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooIngredients->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooIngredient', 'fooRecipes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Foo Ingredient id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $fooIngredient = $this->FooIngredients->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $fooIngredient = $this->FooIngredients->patchEntity($fooIngredient, $this->request->getData());
            if ($this->FooIngredients->save($fooIngredient)) {
                $this->Flash->success(__('The foo ingredient has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo ingredient could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooIngredients->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooIngredient', 'fooRecipes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Foo Ingredient id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $fooIngredient = $this->FooIngredients->get($id);
        if ($this->FooIngredients->delete($fooIngredient)) {
            $this->Flash->success(__('The foo ingredient has been deleted.'));
        } else {
            $this->Flash->error(__('The foo ingredient could not be deleted. Please, try again.'));
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

        $recordData = $this->FooIngredients->redactEntity($id, ['']);

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
