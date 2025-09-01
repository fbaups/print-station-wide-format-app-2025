<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * FooRecipes Controller
 *
 * @property \App\Model\Table\FooRecipesTable $FooRecipes
 * @method \App\Model\Entity\FooRecipe[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FooRecipesController extends AppController
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
        $this->set('typeMap', $this->FooRecipes->getSchema()->typeMap());

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
                'name',
                'description',
                'publish_date',
                'ingredient_count',
                'method_count',
                'is_active',
                'actions',
            ];

            $recordsTotal = $this->FooRecipes->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->FooRecipes->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $fooRecipes = $this->FooRecipes->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['id', 'rank', ' priority', 'ingredient_count', 'method_count',],
                'text_fields' => ['name', 'description', 'text', 'first_name', 'last_name'],
            ];
            $fooRecipes = $this->FooRecipes->applyDatatablesQuickSearchFilter($fooRecipes, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $fooRecipes = $this->FooRecipes->applyDatatablesColumnFilters($fooRecipes, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $fooRecipes->count());

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
                        $order['FooRecipes.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $fooRecipes = $this->paginate($fooRecipes);
            $this->set(compact('fooRecipes'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->FooRecipes->getAllAlertsLogSequence());
            return;
        }

        $this->set('fooRecipes', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Foo Recipe id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $fooRecipe = $this->FooRecipes->get($id, contain: ['FooAuthors', 'FooTags', 'FooIngredients', 'FooMethods', 'FooRatings']);

        $this->set(compact('fooRecipe'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $fooRecipe = $this->FooRecipes->newEmptyEntity();
        if ($this->request->is('post')) {
            $fooRecipe = $this->FooRecipes->patchEntity($fooRecipe, $this->request->getData());
            if ($this->FooRecipes->save($fooRecipe)) {
                $this->Flash->success(__('The foo recipe has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo recipe could not be saved. Please, try again.'));
        }
        $fooAuthors = $this->FooRecipes->FooAuthors->find('list', ['limit' => 200])->all();
        $fooTags = $this->FooRecipes->FooTags->find('list', ['limit' => 200])->all();
        $this->set(compact('fooRecipe', 'fooAuthors', 'fooTags'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Foo Recipe id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $fooRecipe = $this->FooRecipes->get($id, contain: ['FooAuthors', 'FooTags']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $fooRecipe = $this->FooRecipes->patchEntity($fooRecipe, $this->request->getData());
            if ($this->FooRecipes->save($fooRecipe)) {
                $this->Flash->success(__('The foo recipe has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo recipe could not be saved. Please, try again.'));
        }
        $fooAuthors = $this->FooRecipes->FooAuthors->find('list', ['limit' => 200])->all();
        $fooTags = $this->FooRecipes->FooTags->find('list', ['limit' => 200])->all();
        $this->set(compact('fooRecipe', 'fooAuthors', 'fooTags'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Foo Recipe id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $fooRecipe = $this->FooRecipes->get($id);
        if ($this->FooRecipes->delete($fooRecipe)) {
            $this->Flash->success(__('The foo recipe has been deleted.'));
        } else {
            $this->Flash->error(__('The foo recipe could not be deleted. Please, try again.'));
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

        $recordData = $this->FooRecipes->redactEntity($id, ['']);

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
