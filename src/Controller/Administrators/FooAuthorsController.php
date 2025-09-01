<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Exception;

/**
 * FooAuthors Controller
 *
 * @property \App\Model\Table\FooAuthorsTable $FooAuthors
 * @method \App\Model\Entity\FooAuthor[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FooAuthorsController extends AppController
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
        $this->set('typeMap', $this->FooAuthors->getSchema()->typeMap());

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
                'name',
                'is_active',
                'actions',
            ];

            $recordsTotal = $this->FooAuthors->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->FooAuthors->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $fooAuthors = $this->FooAuthors->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['id', 'rank', ' priority'],
                'text_fields' => ['name', 'description', 'text', 'first_name', 'last_name'],
            ];
            $fooAuthors = $this->FooAuthors->applyDatatablesQuickSearchFilter($fooAuthors, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $fooAuthors = $this->FooAuthors->applyDatatablesColumnFilters($fooAuthors, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $fooAuthors->count());

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
                        $order['FooAuthors.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $fooAuthors = $this->paginate($fooAuthors);
            $this->set(compact('fooAuthors'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->FooAuthors->getAllAlertsLogSequence());
            return;
        }

        $this->set('fooAuthors', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $fooAuthor = $this->FooAuthors->get($id, contain: ['FooRecipes']);

        $this->set(compact('fooAuthor'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $fooAuthor = $this->FooAuthors->newEmptyEntity();
        if ($this->request->is('post')) {
            $fooAuthor = $this->FooAuthors->patchEntity($fooAuthor, $this->request->getData());
            if ($this->FooAuthors->save($fooAuthor)) {
                $this->Flash->success(__('The foo author has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo author could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooAuthors->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooAuthor', 'fooRecipes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $fooAuthor = $this->FooAuthors->get($id, contain: ['FooRecipes']);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $fooAuthor = $this->FooAuthors->patchEntity($fooAuthor, $this->request->getData());
            if ($this->FooAuthors->save($fooAuthor)) {
                $this->Flash->success(__('The foo author has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The foo author could not be saved. Please, try again.'));
        }
        $fooRecipes = $this->FooAuthors->FooRecipes->find('list', ['limit' => 200])->all();
        $this->set(compact('fooAuthor', 'fooRecipes'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $fooAuthor = $this->FooAuthors->get($id);
        if ($this->FooAuthors->delete($fooAuthor)) {
            $this->Flash->success(__('The foo author has been deleted.'));
        } else {
            $this->Flash->error(__('The foo author could not be deleted. Please, try again.'));
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

        $recordData = $this->FooAuthors->redactEntity($id, ['']);

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
