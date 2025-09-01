<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Entity\IntegrationCredential;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\Utility\IntegrationCredentials\MicrosoftOpenAuth2\AuthorizationFlow;
use Cake\Event\EventInterface;
use Exception;

/**
 * IntegrationCredentials Controller
 *
 * @property \App\Model\Table\IntegrationCredentialsTable $IntegrationCredentials
 * @method \App\Model\Entity\IntegrationCredential[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class IntegrationCredentialsController extends AppController
{
    private BaseIntegrationCredentials $IC;

    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->set('typeMap', $this->IntegrationCredentials->getSchema()->typeMap());

        $this->IC = new BaseIntegrationCredentials();
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
                'type',
                'name',
                'description',
                'is_enabled',
                'last_status_text',
                'actions',
            ];

            $recordsTotal = $this->IntegrationCredentials->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->IntegrationCredentials->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $integrationCredentials = $this->IntegrationCredentials->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['IntegrationCredentials.id'],
                'text_fields' => ['IntegrationCredentials.name', 'IntegrationCredentials.description', 'IntegrationCredentials.type'],
            ];
            $integrationCredentials = $this->IntegrationCredentials->applyDatatablesQuickSearchFilter($integrationCredentials, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $integrationCredentials = $this->IntegrationCredentials->applyDatatablesColumnFilters($integrationCredentials, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $integrationCredentials->count());

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
                        $order['IntegrationCredentials.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $integrationCredentials = $this->paginate($integrationCredentials);
            $this->set(compact('integrationCredentials'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->IntegrationCredentials->getAllAlertsLogSequence());
            return;
        }

        $this->set('integrationCredentials', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($type = null)
    {
        $integrationCredential = $this->IntegrationCredentials->newEmptyEntity();

        $integrationTypes = $this->IC->getIntegrationTypes();

        if ($type === 'microsoft-open-auth-2') {
            $this->viewBuilder()->setTemplate('ms_open_auth_2');
            $integrationCredential->type = 'MicrosoftOpenAuth2';
        } elseif ($type === 'backblaze-b2') {
            $this->viewBuilder()->setTemplate('backblaze_b2');
            $integrationCredential->type = 'BackblazeB2';
        } elseif ($type === 'sftp') {
            $this->viewBuilder()->setTemplate('sftp');
            $integrationCredential->type = 'sftp';
        } elseif ($type === 'x-m-pie-u-produce') {
            $this->viewBuilder()->setTemplate('xmpie_uproduce');
            $integrationCredential->type = 'XMPie-uProduce';
        }

        if ($this->request->is('post')) {
            $integrationCredential = $this->IntegrationCredentials->patchEntity($integrationCredential, $this->request->getData());

            //reject if Backblaze B2 and API key is multi-bucket or has a name prefix
            if ($integrationCredential->type === 'BackblazeB2') {
                if (!empty($integrationCredential->tracking_data['account_authorisation']['allowed']['namePrefix'])) {
                    $this->Flash->error(__('The API Key has an associated Name Prefix. This is currently unsupported. Please try another key pair.'));
                    $this->set(compact('integrationCredential', 'integrationTypes'));
                    return;
                }
                if (empty($integrationCredential->tracking_data['account_authorisation']['allowed']['bucketId'])) {
                    $this->Flash->error(__('The API Key is linked to multiple buckets. This is currently unsupported. Please try another key pair.'));
                    $this->set(compact('integrationCredential', 'integrationTypes'));
                    return;
                }
            }

            if ($this->IntegrationCredentials->save($integrationCredential)) {
                $this->Flash->success(__('The integration credential has been saved.'));

                $this->IntegrationCredentials->updateLastStatus($integrationCredential);

                $Session = $this->request->getSession();
                $Session->delete('IntegrationCredentials.XMPie-uProduce.count');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The integration credential could not be saved. Please, try again.'));
        }
        $this->set(compact('integrationCredential', 'integrationTypes'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Integration Credential id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $integrationCredential = $this->IntegrationCredentials->get($id, [
            'contain' => [],
        ]);

        $integrationTypes = $this->IC->getIntegrationTypes();

        if ($integrationCredential->type === 'MicrosoftOpenAuth2') {
            $this->viewBuilder()->setTemplate('ms_open_auth_2');
        } elseif ($integrationCredential->type === 'BackblazeB2') {
            $this->viewBuilder()->setTemplate('backblaze_b2');
        } elseif ($integrationCredential->type === 'sftp') {
            $this->viewBuilder()->setTemplate('sftp');
        } elseif ($integrationCredential->type === 'XMPie-uProduce') {
            $this->viewBuilder()->setTemplate('xmpie_uproduce');
            $integrationCredential->type = 'XMPie-uProduce';
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $integrationCredential = $this->IntegrationCredentials->patchEntity($integrationCredential, $this->request->getData());

            //reject if Backblaze B2 and API key is multi-bucket or has a name prefix
            if ($integrationCredential->type === 'BackblazeB2') {
                if (!empty($integrationCredential->tracking_data['account_authorisation']['allowed']['namePrefix'])) {
                    $this->Flash->error(__('The API Key has an associated Name Prefix. This is currently unsupported. Please try another key pair.'));
                    $this->set(compact('integrationCredential', 'integrationTypes'));
                    return;
                }
                if (empty($integrationCredential->tracking_data['account_authorisation']['allowed']['bucketId'])) {
                    $this->Flash->error(__('The API Key is linked to multiple buckets. This is currently unsupported. Please try another key pair.'));
                    $this->set(compact('integrationCredential', 'integrationTypes'));
                    return;
                }
            }

            if ($this->IntegrationCredentials->save($integrationCredential)) {
                $this->Flash->success(__('The integration credential has been saved.'));

                $this->IntegrationCredentials->updateLastStatus($integrationCredential);

                $Session = $this->request->getSession();
                $Session->delete('IntegrationCredentials.XMPie-uProduce.count');

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The integration credential could not be saved. Please, try again.'));
        }
        $this->set(compact('integrationCredential', 'integrationTypes'));
    }

    public function authenticate($code = null)
    {
        /** @var IntegrationCredential $integrationCredential */

        if ($this->request->is(['patch', 'post', 'put'])) {
            $id = $this->request->getData('id');
            if (!$id) {
                return $this->redirect(['action' => 'index']);
            }

            $integrationCredential = $this->IntegrationCredentials->asEntity($id);
            if (!$integrationCredential) {
                return $this->redirect(['action' => 'index']);
            }

            if ($integrationCredential->type === 'MicrosoftOpenAuth2') {
                $AuthorizationFlow = new AuthorizationFlow($integrationCredential);

                $status = $AuthorizationFlow->getAuthorizationStatus();

                if ($status === 'authorized') {
                    $this->Flash->success(__("MicrosoftOpenAuth2: Tokens are valid, no need to refresh for {0}", $integrationCredential->name));
                    return $this->redirect(['action' => 'index']);
                } elseif ($status === 'expired') {
                    $result = $AuthorizationFlow->authoriseWithRefresh();
                    if ($result) {
                        $this->Flash->success(__("MicrosoftOpenAuth2: Tokens have been refreshed for {0}", $integrationCredential->name));
                    } else {
                        $this->Flash->error(__("MicrosoftOpenAuth2: Failed to get refresh tokens for {0}", $integrationCredential->name));
                    }
                    return $this->redirect(['action' => 'index']);
                } elseif ($status === 'unauthorized') {
                    $authUrl = $AuthorizationFlow->getAuthorizationUrl();
                    return $this->redirect($authUrl);
                }

                $this->Flash->error(__("MicrosoftOpenAuth2: Unknown error, please try again"));
                return $this->redirect(['action' => 'index']);
            }
        }

        if ($this->request->is(['get'])) {
            if ($code === 'microsoft-open-auth-2') {
                $code = $this->request->getQuery('code');
                $state = $this->request->getQuery('state');

                if (!$code || !$state) {
                    $this->Flash->error(__("OpenAuth2: Code and State were not provided for authorization"));
                    return $this->redirect(['action' => 'index']);
                }

                $integrationCredential = $this->IntegrationCredentials->getByTrackingHash($state);
                if (!$integrationCredential) {
                    $this->Flash->error(__("OpenAuth2: Invalid State was provided"));
                    return $this->redirect(['action' => 'index']);
                }

                $AuthorizationFlow = new AuthorizationFlow($integrationCredential);
                $result = $AuthorizationFlow->authorizeWithCode($code);
                if ($result) {
                    $this->Flash->success(__("OpenAuth2: {0} has been authorized into {1}", APP_NAME, $integrationCredential->name));
                } else {
                    $this->Flash->error(__("OpenAuth2: Failed to authorized {0} into {1}", APP_NAME, $integrationCredential->name));
                }
            }
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Integration Credential id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $integrationCredential = $this->IntegrationCredentials->get($id);
        if ($this->IntegrationCredentials->delete($integrationCredential)) {
            $this->Flash->success(__('The integration credential has been deleted.'));
        } else {
            $this->Flash->error(__('The integration credential could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /* START_BLOCKED_CODE */
    public function decrypt($id = null)
    {
        $integrationCredential = $this->IntegrationCredentials->get($id, [
            'contain' => [],
        ]);

        if ($integrationCredential->type === 'MicrosoftOpenAuth2') {
            dump("TenantId: " . $integrationCredential->microsoftOpenAuth2_getTenantId());
            dump("ClientId: " . $integrationCredential->microsoftOpenAuth2_getClientId());
            dump("ClientSecret: " . $integrationCredential->microsoftOpenAuth2_getClientSecret());
            dump("AccessToken: " . $integrationCredential->microsoftOpenAuth2_getAccessToken());
            dump("RefreshToken: " . $integrationCredential->microsoftOpenAuth2_getRefreshToken());
            dump("Expires: " . $integrationCredential->microsoftOpenAuth2_getExpires());
            die();
        } elseif ($integrationCredential->type === 'BackblazeB2') {
            dump($integrationCredential->backblazeB2_getParametersDecrypted());
            die();
        } elseif ($integrationCredential->type === 'sftp') {
            dump($integrationCredential->sftp_getParametersDecrypted());
            die();
        } elseif ($integrationCredential->type === 'XMPie-uProduce') {
            dump($integrationCredential->uProduce_getParametersDecrypted());
            die();
        } else {
            $this->Flash->error(__('The integration credential is not a known type. Please, try again.'));
            return $this->redirect(['action' => 'index']);
        }
    }
    /* END_BLOCKED_CODE */

}
