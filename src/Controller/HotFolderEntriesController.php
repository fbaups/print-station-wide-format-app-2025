<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Exception;

/**
 * HotFolderEntries Controller
 *
 * @property \App\Model\Table\HotFolderEntriesTable $HotFolderEntries
 * @method \App\Model\Entity\HotFolderEntry[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class HotFolderEntriesController extends AppController
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
        $this->set('typeMap', $this->HotFolderEntries->getSchema()->typeMap());

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
                'id',
                'hot_folder_id',
                'path',
                'last_check_time',
                'status',
                'actions',
            ];

            $recordsTotal = $this->HotFolderEntries->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->HotFolderEntries->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $hotFolderEntries = $this->HotFolderEntries->find('all');
            $hotFolderEntries->contain(['HotFolders']);

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['HotFolderEntries.id', 'HotFolderEntries.lock_code', 'HotFolderEntries.errand_link'],
                'text_fields' => ['HotFolderEntries.path', 'HotFolderEntries.status'],
            ];
            $hotFolderEntries = $this->HotFolderEntries->applyDatatablesQuickSearchFilter($hotFolderEntries, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $hotFolderEntries = $this->HotFolderEntries->applyDatatablesColumnFilters($hotFolderEntries, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $hotFolderEntries->count());

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
                        $order['HotFolderEntries.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $hotFolderEntries = $this->paginate($hotFolderEntries);
            $this->set(compact('hotFolderEntries'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->HotFolderEntries->getAllAlertsLogSequence());
            return;
        }

        $this->set('hotFolderEntries', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Hot Folder Entry id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $hotFolderEntry = $this->HotFolderEntries->get($id, [
            'contain' => ['HotFolders'],
        ]);

        $this->set(compact('hotFolderEntry'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Hot Folder Entry id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $hotFolderEntry = $this->HotFolderEntries->get($id);
        if ($this->HotFolderEntries->delete($hotFolderEntry)) {
            $this->Flash->success(__('The hot folder entry has been deleted.'));
        } else {
            $this->Flash->error(__('The hot folder entry could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

}
