<?php
declare(strict_types=1);

namespace App\Controller;

use App\MessageGateways\CellcastSmsGateway;
use App\MessageGateways\SmsGatewayFactory;
use App\Model\Table\MessagesTable;
use App\Utility\Instances\Checker;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Exception;

/**
 * Settings Controller
 *
 * @property \App\Model\Table\SettingsTable $Settings
 * @method \App\Model\Entity\Setting[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SettingsController extends AppController
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
        $this->set('typeMap', $this->Settings->getSchema()->typeMap());
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        $this->FormProtection->setConfig('unlockedActions', ['testEmailServer', 'testSmsGateway']);

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
                'property_group',
                'property_key',
                'property_value',
                'actions',
            ];

            $recordsTotal = $this->Settings->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            $this->Settings->convertJsonFieldsToString(['selections']);

            //create a Query
            $settings = $this->Settings->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Settings.id'],
                'text_fields' => ['Settings.name', 'Settings.description', 'Settings.property_group', 'Settings.property_key', 'Settings.property_value'],
            ];
            $settings = $this->Settings->applyDatatablesQuickSearchFilter($settings, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $settings = $this->Settings->applyDatatablesColumnFilters($settings, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $settings->count());

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
                        $order['Settings.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $settings = $this->paginate($settings);
            $this->set(compact('settings'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Settings->getAllAlertsLogSequence());
            return;
        }

        $this->set('settings', []);
        $this->set('isAjax', $isAjax);
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

        $recordData = $this->Settings->redactEntity($id, ['']);

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

    /**
     * Edit method
     *
     * @param string|null $id Setting id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $setting = $this->Settings->get($id, contain: []);

        $groupEdits = $this->Settings->listPropertyGroups();;
        if (in_array($setting->property_group, $groupEdits)) {
            return $this->redirect(['action' => 'edit-group', $setting->property_group]);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $setting = $this->Settings->patchEntity($setting, $this->request->getData());
            if ($this->Settings->save($setting)) {
                $this->Flash->success(__('The setting has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The setting could not be saved. Please, try again.'));
        }
        $this->set(compact('setting'));
    }

    /**
     * Edit group method.
     * Each group needs to be individually set due to nuances within the group.
     *
     * @param string|null $groupName
     * @return Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function editGroup($groupName = null)
    {
        if ($groupName === null) {
            return $this->redirect(['action' => 'index']);
        }

        $groupName = strtolower($groupName);
        $this->set('groupName', $groupName);
        $groupNameHuman = Inflector::humanize($groupName);
        $groupNameHuman = str_replace("Sms", "SMS", $groupNameHuman);
        $groupNameHuman = str_replace("Mms", "MMS", $groupNameHuman);
        $this->set('groupNameHuman', $groupNameHuman);

        $settings = $this->Settings->find('all')->where(['property_group' => $groupName]);
        if ($settings->count() <= 0) {
            $this->Flash->error(__('Sorry, no settings found for the {0} group.', $groupName));
            return $this->redirect(['action' => 'index']);
        }
        $this->set('settings', $settings);

        //some groups have a specific page as they have some nuances
        if ($groupName === 'repository') {
            $this->_varsRepository();
            $this->viewBuilder()->setTemplate('edit_group_' . $groupName);
        } elseif ($groupName === 'email_server') {
            $this->_varsRepository();
            $this->viewBuilder()->setTemplate('edit_group_' . $groupName);
        } elseif ($groupName === 'sms_gateway') {
            $this->_varsRepository();
            $this->viewBuilder()->setTemplate('edit_group_' . $groupName);
        } else {
            $this->viewBuilder()->setTemplate('edit_group_generic');
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $dataToSave = $this->request->getData();

            $saveResult = false;
            if ($groupName === 'repository') {
                //special saving for repo settings
                $saveResult = $this->Settings->setRepositoryDetails($dataToSave);
            } else {
                $saveResult = $this->Settings->setSettings($dataToSave);
            }

            if ($saveResult) {
                $this->Flash->success(__('The {0} settings has been saved.', $groupNameHuman));

                //update Configure
                $this->Settings->saveSettingsToConfigure(false);

                if (isset($dataToSave['forceRefererRedirect']) && strlen($dataToSave['forceRefererRedirect']) > 10) {
                    return $this->redirect($dataToSave['forceRefererRedirect']);
                }
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The {0} settings could not be saved. Please, try again.', $groupName));
        }

        return null;
    }

    /**
     * Send a test email
     *
     * @return Response|null
     */
    public function testEmailServer(): ?Response
    {
        if (!$this->request->is(['ajax',])) {
            return $this->redirect(['action' => 'index']);
        }
        $data = $this->request->getParsedBody();


        //transport config
        if (!empty($data['email_username'])) {
            $email_username = $data['email_username'];
        } else {
            $email_username = null;
        }

        if (!empty($data['email_password'])) {
            if ($data['email_password_is_hashed']) {
                //user has not changed the password so retrieve password from DB as the one passed in via form is hashed
                $email_password = $this->Settings->getSetting('email_password');
            } else {
                //user has changed the password so use the new password
                $email_password = $data['email_password'];
            }
        } else {
            $email_password = null;
        }

        $tmpTransportConfig = [
            'host' => $data['email_smtp_host'],
            'port' => $data['email_smtp_port'],
            'username' => $email_username,
            'password' => $email_password,
            'className' => 'Smtp',
        ];

        $data['email_tls'] = asBool($data['email_tls']);
        if ($data['email_tls']) {
            $tmpTransportConfig['tls'] = true;
        }

        TransportFactory::setConfig('quickMailer', $tmpTransportConfig);

        try {
            $mailer = new Mailer();
            $mailer
                ->setTransport('quickMailer')
                ->setCharset('utf-8')
                ->setHeaderCharset('utf-8')
                ->setFrom([$data['email_from_address'] => $data['email_from_name']])
                ->setTo($data['test_send_to'])
                ->setSubject($data['test_subject'])
                ->setEmailFormat('html')
                ->deliver($data['test_body']);

            $response = [
                'status' => true,
                'message' => $mailer->getMessage(),
            ];
        } catch (\Throwable $exception) {
            $response = [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }

//        $response['r-data'] = $tmpTransportConfig;
//        $response['t-data'] = $tmpTransportConfig;

        $responseData = json_encode($response, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        TransportFactory::drop('quickMailer');

        return $this->response;
    }

    /**
     * Send a test email
     *
     * @return Response|null
     */
    public function testSmsGateway(): ?Response
    {
        if (!$this->request->is(['ajax',])) {
            return $this->redirect(['action' => 'index']);
        }
        $data = $this->request->getParsedBody();

        if (!empty($data['sms_gateway_api_key'])) {
            if ($data['sms_gateway_api_key_is_hashed']) {
                //user has not changed the api key so retrieve api key from DB as the one passed in via form is hashed
                $sms_gateway_api_key = $this->Settings->getSetting('sms_gateway_api_key');
            } else {
                //user has changed the password so use the new password
                $sms_gateway_api_key = $data['sms_gateway_api_key'];
            }
        } else {
            $sms_gateway_api_key = null;
        }

        if (!empty($data['sms_gateway_password'])) {
            if ($data['sms_gateway_password_is_hashed']) {
                //user has not changed the password so retrieve password from DB as the one passed in via form is hashed
                $sms_gateway_password = $this->Settings->getSetting('sms_gateway_password');
            } else {
                //user has changed the password so use the new password
                $sms_gateway_password = $data['sms_gateway_password'];
            }
        } else {
            $sms_gateway_password = null;
        }

        $sms_gateway_provider = $data['sms_gateway_provider'];

        try {
            $SmsGateway = (new SmsGatewayFactory(null, null, null, $sms_gateway_api_key, $sms_gateway_provider))->getSmsGateway();

            /** @var MessagesTable $Messages */
            $Messages = TableRegistry::getTableLocator()->get('Messages');
            $message = $Messages->newEmptyEntity();
            $message->type = "sms";
            $message->email_to = $data['test_mobile'];
            $message->subject = $data['test_message'];
            $message = $Messages->save($message);
            $message = $SmsGateway->sendSms($message);
            $responseMessage = $message->smtp_message;
            $responseStatusCode = $message->smtp_code;

            if ($responseStatusCode >= 200 && $responseStatusCode <= 299) {
                $isStatusOk = true;
            } else {
                $isStatusOk = false;
            }

            $response = [
                'status' => $isStatusOk,
                'message' => $responseMessage,
            ];

        } catch (\Throwable $exception) {
            $response = [
                'status' => false,
                'message' => $exception->getMessage(),
            ];
        }


        $responseData = json_encode($response, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

    /* START_BLOCKED_CODE */

    /**
     * Generate seed data for Migrations
     *
     * Will not appear in PROD as this generates insecure output.
     *
     * @param null $gte
     * @param null $lte
     * @return Response|null
     */
    public function seed($gte = null, $lte = null): ?Response
    {
        if (strtolower(Configure::read('mode')) !== 'prod') {
            $seeds = $this->Settings->find('all')
                ->orderByAsc('id')
                ->enableHydration(false);

            if ($gte) {
                $seeds = $seeds->where(['id >=' => $gte]);
            }

            if ($lte) {
                $seeds = $seeds->where(['id <=' => $lte]);
            }

            $this->set('seeds', $seeds);
            return null;
        } else {
            return $this->redirect(['action' => 'index']);
        }
    }

    /* END_BLOCKED_CODE */

    private function _varsRepository()
    {
        $repoCheckResult = $this->Settings->checkRepositoryDetails(true);

        $this->set('repo_url', $repoCheckResult['repo_url']);
        $this->set('repo_unc', $repoCheckResult['repo_unc']);
        $this->set('repo_sftp_host', $repoCheckResult['repo_sftp_host']);
        $this->set('repo_sftp_port', $repoCheckResult['repo_sftp_port']);
        $this->set('repo_sftp_username', $repoCheckResult['repo_sftp_username']);
        $this->set('repo_sftp_password', $repoCheckResult['repo_sftp_password']);
        $this->set('repo_sftp_timeout', $repoCheckResult['repo_sftp_timeout']);
        $this->set('repo_sftp_path', $repoCheckResult['repo_sftp_path']);
        $this->set('isURL', $repoCheckResult['isURL']);
        $this->set('isSFTP', $repoCheckResult['isSFTP']);
        $this->set('isUNC', $repoCheckResult['isUNC']);
        $this->set('remoteUpdateDebug', $repoCheckResult['remoteUpdateDebug']);
    }
}
