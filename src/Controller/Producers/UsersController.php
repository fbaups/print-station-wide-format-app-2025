<?php
declare(strict_types=1);

namespace App\Controller\Producers;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->set('typeMap', $this->Users->getSchema()->typeMap());

    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Skip authorization - TinyAuth middleware handles this
        if (isset($this->Authorization)) {
            $this->Authorization->skipAuthorization();
        }

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);
        $this->FormProtection->setConfig('unlockedActions', ['index']); //allow index for DataTables index refresh

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

    }

    /**
     * Index method
     *
     * @return void Renders view
     */
    public function index()
    {
        $isAjax = false;

        if ($this->request->is('ajax')) {
            //DataTables POSTed the data as a querystring, parse and assign to $datatablesQuery
            parse_str($this->request->getBody()->getContents(), $datatablesQuery);

            //$headers must match the View
            $headers = [
                'Users.id',
                'Users.email',
                'Users.username',
                'Users.first_name',
                'Users.last_name',
                'Users.country',
                'Users.mobile',
                'Users.user_statuses_id',
                'Roles.id',
                'actions',
            ];

            $recordsTotal = $this->Users->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->Users->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $users = $this->Users->find('all');
            $users->contain(['UserStatuses']);
            $users->contain(['Roles']);

            //special handler for Roles.id as this is a HABTM relationship and not supported in ->applyDatatablesColumnFilters()
            $rolesIdFilter = $datatablesQuery['columns'][8];
            unset($datatablesQuery['columns'][8]);
            unset($headers[8]);
            $roleId = false;
            if ($rolesIdFilter['search']['value'] === 0 || $rolesIdFilter['search']['value'] === '0') {
                $roleId = 0;
            } elseif (!empty($rolesIdFilter['search']['value'])) {
                $roleId = intval($rolesIdFilter['search']['value']);
            }
            if ($roleId) {
                $users = $users->matching('Roles', function ($q) use ($roleId) {
                    return $q->where(['Roles.id' => $roleId]);
                });
            }

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Users.id'],
                'text_fields' => ['Users.username', 'Users.email', 'Users.first_name', 'Users.last_name'],
            ];
            $users = $this->Users->applyDatatablesQuickSearchFilter($users, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $users = $this->Users->applyDatatablesColumnFilters($users, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $users->count());

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
                        $order['Users.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $users = $this->paginate($users);
            $this->set(compact('users'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Users->getAllAlertsLogSequence());

            $userStatuses = $this->Users->UserStatuses->find('list', ['limit' => 200])->all();
            $roles = $this->Users->Roles->find('list', ['limit' => 200])->all();
            $this->set(compact('userStatuses', 'roles'));
            return;
        }

        $this->set('users', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return void Renders view
     * @throws RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, contain: ['UserStatuses', 'Roles', 'UserLocalizations']);

        $this->set(compact('user'));
    }

    /**
     * invite a new user method
     *
     * @return Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function invite()
    {
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $peerRoles = $this->Users->Roles->getPeerRoles($this->getCurrentUserRoles(), false);
            $rolesToApply = $this->request->getData('roles');
            $rolesToApplyCleaned = $this->Users->Roles->validatePeerRoles($peerRoles, $rolesToApply);

            if (empty($rolesToApplyCleaned)) {
                $this->Flash->error(__('Failed to create invitation as a matching Role could not be found. Please, try again.'));
                return $this->redirect(['controller' => 'users', 'action' => 'invite']);
            }

            $data = $this->request->getData();
            $data['roles']['_ids'] = array_keys($rolesToApplyCleaned);
            $data['username'] = $data['email'];
            /** @var User $user */
            $user = $this->Users->sendInvitationLink($data);

            if ($user) {
                $this->Flash->success(__('An invitation has been sent to {0}.', $user->email));

                $mode = Configure::read('mode');
            if ($mode && in_array(strtolower($mode), ['dev', 'development'])) {
                    $userInfo = $this->Users->userInvitationData;
                    $this->Flash->info(
                        __('Invitation URL for {0}: <strong>{1}</strong>', $userInfo['full_name'], $userInfo['invitation_url']),
                        ['escape' => false, 'params' => ['clickHide' => false]]
                    );
                }

                $this->Auditor->auditInfo(__('Invitation sent to ID:{0} {1} {2} <{3}>.', $user->id, $user->first_name, $user->last_name, $user->email));

                return $this->redirect('/');
            }
            $this->Flash->error(__('Failed to create an invitation. Please, try again.'));

        }
        $peerRoles = $this->Users->Roles->getPeerRoles($this->getCurrentUserRoles());
        $this->set(compact('user', 'peerRoles'));
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

        $recordData = $this->Users->redactEntity($id, ['']);

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
