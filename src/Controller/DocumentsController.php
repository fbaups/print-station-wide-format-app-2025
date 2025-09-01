<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * Documents Controller
 *
 * @property \App\Model\Table\DocumentsTable $Documents
 * @method \App\Model\Entity\Document[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class DocumentsController extends AppController
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
        $this->set('typeMap', $this->Documents->getSchema()->typeMap());

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
                'Jobs.order_id',
                'Documents.job_id',
                'Documents.id',
                'Documents.document_status_id',
                'Documents.name',
                'Documents.description',
                'Documents.quantity',
                'Documents.external_document_number',
                'actions',
            ];

            $recordsTotal = $this->Documents->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->Documents->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $documents = $this->Documents->find('all');
            $documents->contain(['Jobs', 'Jobs.Orders', 'DocumentStatuses']);

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Documents.id', 'Documents.rank', 'Documents.priority'],
                'text_fields' => ['Documents.name', 'Documents.description', 'Documents.text', 'Documents.first_name', 'Documents.last_name'],
            ];
            $documents = $this->Documents->applyDatatablesQuickSearchFilter($documents, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $documents = $this->Documents->applyDatatablesColumnFilters($documents, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $documents->count());

            $this->viewBuilder()->setLayout('ajax');
            $this->response = $this->response->withType('json');
            $isAjax = true;
            $this->set('datatablesQuery', $datatablesQuery);

            $sorting = $this->Documents->applyDatatablesSorting($documents, $datatablesQuery, $headers);
            $documents->orderBy($sorting);

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
            ];
            $documents = $this->paginate($documents);
            $this->set(compact('documents'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Documents->getAllAlertsLogSequence());
            return;
        }

        $this->set('documents', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Document id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $document = $this->Documents->get($id, contain: ['Jobs', 'DocumentStatuses', 'Users', 'DocumentAlerts', 'DocumentProperties', 'DocumentStatusMovements']);

        $this->set(compact('document'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Document id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $document = $this->Documents->get($id, contain: ['Users']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $document = $this->Documents->patchEntity($document, $this->request->getData());
            if ($this->Documents->save($document)) {
                $this->Flash->success(__('The document has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The document could not be saved. Please, try again.'));
        }
        $jobs = $this->Documents->Jobs->find('list', ['limit' => 200])->all();
        $documentStatuses = $this->Documents->DocumentStatuses->find('list', ['limit' => 200])->all();
        $users = $this->Documents->Users->find('list', ['limit' => 200])->all();
        $this->set(compact('document', 'jobs', 'documentStatuses', 'users'));
    }

    /**
     * Delete method
     *
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete()
    {
        $this->Flash->error(__('Deleting Documents is not allowed. Please manage the Order this Document belongs to.'));

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

        $recordData = $this->Documents->redactEntity($id, ['']);

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
