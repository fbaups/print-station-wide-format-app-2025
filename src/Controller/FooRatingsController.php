<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * FooRatings Controller
 *
 * @property \App\Model\Table\FooRatingsTable $FooRatings
 * @method \App\Model\Entity\FooRating[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FooRatingsController extends AppController
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
        $this->set('typeMap', $this->FooRatings->getSchema()->typeMap());

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
                'score',
                'actions',
            ];

            $recordsTotal = $this->FooRatings->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->FooRatings->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $fooRatings = $this->FooRatings->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['id', 'rank', ' priority'],
                'text_fields' => ['name', 'description', 'text', 'first_name', 'last_name'],
            ];
            $fooRatings = $this->FooRatings->applyDatatablesQuickSearchFilter($fooRatings, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $fooRatings = $this->FooRatings->applyDatatablesColumnFilters($fooRatings, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $fooRatings->count());

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
                        $order['FooRatings.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $fooRatings = $this->paginate($fooRatings);
            $this->set(compact('fooRatings'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->FooRatings->getAllAlertsLogSequence());
            return;
        }

        $this->set('fooRatings', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Foo Rating id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $fooRating = $this->FooRatings->get($id, contain: ['FooRecipes']);

        $this->set(compact('fooRating'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $fooRating = $this->FooRatings->newEmptyEntity();
        if ($this->request->is('post')) {
            $fooRating = $this->FooRatings->patchEntity($fooRating, $this->request->getData());
            if ($this->FooRatings->save($fooRating)) {
                $this->Flash->success(__('The foo rating has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo rating could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooRatings->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooRating', 'fooRecipes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Foo Rating id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $fooRating = $this->FooRatings->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $fooRating = $this->FooRatings->patchEntity($fooRating, $this->request->getData());
            if ($this->FooRatings->save($fooRating)) {
                $this->Flash->success(__('The foo rating has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo rating could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooRatings->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooRating', 'fooRecipes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Foo Rating id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $fooRating = $this->FooRatings->get($id);
        if ($this->FooRatings->delete($fooRating)) {
            $this->Flash->success(__('The foo rating has been deleted.'));
        } else {
            $this->Flash->error(__('The foo rating could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function randomise($randomCount = 100)
    {
        $randomCount = min(2000, $randomCount);

        $deleteCount = $this->FooRatings->deleteAll([1 => 1]);
        if ($deleteCount) {
            $this->Flash->success(__('Deleted {0} foo ratings from the DB', $deleteCount));
        }

        $firstRecord = $this->FooRatings->FooRecipes->find('all')->orderByAsc('id')->limit(1)->first();
        $lastRecord = $this->FooRatings->FooRecipes->find('all')->orderByDesc('id')->limit(1)->first();

        $range = range(1, $randomCount);
        $ratings = [];
        foreach ($range as $number) {
            $ratings[] = [
                'foo_recipe_id' => mt_rand($firstRecord->id, $lastRecord->id),
                'score' => mt_rand(1, 100),
            ];
        }

        $this->FooRatings->massInsert($ratings);
        $insertCount = ($this->FooRatings->find('all')->count());
        $this->Flash->success(__('Inserted {0} foo ratings into the DB', $insertCount));

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

        $recordData = $this->FooRatings->redactEntity($id, ['']);

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
