<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Exception;

/**
 * Messages Controller
 *
 * @property \App\Model\Table\MessagesTable $Messages
 * @method \App\Model\Entity\Message[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MessagesController extends AppController
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
        $this->set('typeMap', $this->Messages->getSchema()->typeMap());

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
                'type',
                'name',
                'completed',
                'subject',
                'email_to',
                'smtp_message',
                'actions',
            ];

            $recordsTotal = $this->Messages->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            $this->Messages->convertJsonFieldsToString(['email_to', 'email_from', 'email_cc', 'email_bcc', 'view_vars']);

            //create a Query
            $messages = $this->Messages->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Messages.id', 'Messages.smtp_code', 'Messages.priority'],
                'text_fields' => ['Messages.name', 'Messages.description', 'Messages.email_to', 'Messages.email_from', 'Messages.email_cc', 'Messages.email_bcc', 'Messages.subject', 'Messages.view_vars'],
            ];
            $messages = $this->Messages->applyDatatablesQuickSearchFilter($messages, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $messages = $this->Messages->applyDatatablesColumnFilters($messages, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $messages->count());

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
                        $order['Messages.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $messages = $this->paginate($messages);
            $this->set(compact('messages'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Messages->getAllAlertsLogSequence());
            return;
        }

        $this->set('messages', []);
        $this->set('emailDomain', $this->Settings->getEmailDomain());
        $this->set('smsGatewayProvider', $this->Settings->getSetting('sms_gateway_provider'));
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $message = $this->Messages->get($id, contain: []);

        $this->set(compact('message'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id);
        if ($this->Messages->delete($message)) {
            $this->Flash->success(__('The message has been deleted.'));
        } else {
            $this->Flash->error(__('The message could not be deleted. Please, try again.'));
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

        $recordData = $this->Messages->redactEntity($id, ['']);

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
