<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Utility\Instances\InstanceTasks;
use Cake\Core\Configure;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Routing\Router;
use TinyAuth\Controller\Component\AuthComponent;

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

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);

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
            $datatablesQuery = $this->request->getQuery();

            //$headers must match the View
            $headers = [
                'id',
                'email',
                'username',
                'first_name',
                'last_name',
                'country',
                'mobile',
                'user_statuses_id',
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

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Users.id'],
                'text_fields' => ['Users.name', 'Users.description', 'Users.text', 'Users.first_name', 'Users.last_name'],
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
            $users = $users->contain(['UserStatuses']);
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
     * Add method
     *
     * @return Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['password_1'] = $data['password'];
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $userStatuses = $this->Users->UserStatuses->find('list', ['limit' => 200])->all();
        $roles = $this->Users->Roles->find('list', ['limit' => 200])->all();
        $this->set(compact('user', 'userStatuses', 'roles'));
    }

    /**
     * invite a new user method
     *
     * @return Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function invite()
    {
        $newUser = $this->Users->newEmptyEntity();
        $peerRoles = $this->Users->Roles->getPeerRoles($this->AuthUser->roles());

        if ($this->request->is('post')) {
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

                if (in_array(strtolower(Configure::read('mode')), ['dev', 'development'])) {
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
        $this->set(compact('newUser', 'peerRoles'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, contain: ['Roles']);
        if ($this->request->is(['patch', 'post', 'put'])) {

            $data = $this->request->getData();

            //password check if changed
            $currentPasswordHashed = sha1($user->password);
            if ($currentPasswordHashed === $data['password']) {
                unset($data['password']);
            } else {
                $data['password_1'] = $data['password'];
            }

            $user = $this->Users->patchEntity($user, $data);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $userStatuses = $this->Users->UserStatuses->find('list', ['limit' => 200])->all();
        $roles = $this->Users->Roles->find('list', ['limit' => 200])->all();
        $this->set(compact('user', 'userStatuses', 'roles'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return Response|null|void Redirects to index.
     * @throws RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
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

    /**
     * Impersonate method.
     *
     * Allows a SuperAdmin to impersonate another User.
     * The auth_acl.ini locks this function to SuperAdmin but there is also an added check.
     *
     * @param null $id
     * @return \Cake\Http\Response|null
     */
    public function impersonate($id = null)
    {
        if (!$this->AuthUser->hasRoles(['superadmin'])) {
            $this->Flash->error(__('Sorry, you are not authorised to impersonate other Users.'));
            return $this->redirect(['controller' => 'login']);
        }

        if ($id == null) {
            return $this->redirect(['controller' => 'login']);
        }

        $currentUser = $this->AuthUser->user();

        /** @var User $newUser */
        $newUser = $this->Users
            ->get($id, contain: ['Roles'])
            ->toArray();

        if ($newUser) {
            $message = __("[{0}: {1} {2}] impersonated [{3}: {4} {5}]",
                $currentUser['id'], $currentUser['first_name'], $currentUser['last_name'],
                $newUser['id'], $newUser['first_name'], $newUser['last_name']);
            $this->Auditor->auditInfo($message);
            $this->Auth->logout();

            $roles = $this->Users->listOfRolesForUser($newUser['id']);
            $newUser['roles_list'] = $roles;

            $this->Auth->setUser($newUser);
            $this->Flash->info(__('You are now logged in as {0} {1}.', $newUser['first_name'], $newUser['last_name']));
            return $this->redirect($this->Auth->redirectUrl());
        }

        return null;
    }

    /**
     * Update method - Users can self-update their details
     *
     * @return Response|null
     */
    public function profile(): ?Response
    {
        $id = $this->Auth->user('id');
        $user = $this->Users->get($id, contain: ['UserStatuses', 'Roles', 'UserLocalizations']);

        if ($this->request->is(['patch', 'post', 'put'])) {
            //avoid mass-assignment attack
            $options = [
                'fieldList' => [
                    'email',
                    'username',
                    'password',
                    'password_1',
                    'first_name',
                    'last_name',
                    'address_1',
                    'address_2',
                    'suburb',
                    'state',
                    'post_code',
                    'mobile',
                    'phone',
                    'user_localizations' => [
                        'user_id',
                        'location',
                        'locale',
                        'date_format',
                        'time_format',
                        'datetime_format',
                        'week_start',
                        'timezone',
                    ],
                ]
            ];

            $data = $this->request->getData();
            $data['user_localizations'][0]['user_id'] = $id;

            //password check if changed
            $currentPasswordHashed = sha1($user->password);
            if ($currentPasswordHashed === $data['password']) {
                unset($data['password']);
            } else {
                $data['password_1'] = $data['password'];
            }

            $user = $this->Users->patchEntity($user, $data, $options);
            if ($this->Users->save($user)) {
                $userDetails = $user->toArray();
                $roles = $this->Users->listOfRolesForUser($userDetails['id']);
                $userDetails['roles_list'] = $roles;

                $this->Auth->setUser($userDetails);

                $this->Flash->success(__('Your profile has been updated.'));
                return $this->redirect("/");
            } else {
                $this->Flash->error(__('Your profile could not be updated. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        $localizationSettings = $this->Settings->findByPropertyGroup('localization');
        $this->set('localizationSettings', $localizationSettings);
        return null;
    }

    /**
     * Pre-login to the application.
     * Put logic in here to warm up the site for the User (e.g. build caches or sync operations)
     *
     * @return Response|null
     */
    public function preLogin(): ?Response
    {
        //warm up the site
        if ($this->request->is('ajax')) {
            if ($this->request->getData('username')) {
                //do stuff here to warm up the user account

                $username = $this->request->getData('username');
                $this->response = $this->response->withType('json');

                $user = $this->Users->find('all')
                    ->where(['OR' => ['username' => $username, 'email' => $username]])
                    ->contain(['Roles'])
                    ->first();
                if ($user) {
                    /*
                     * Insert warm-up routines here
                     */

                    //if SuperAdmin do FSO stats
                    if ($this->Users->doesUserHaveRole($user, 'superadmin')) {
                        $InstanceTasks = new InstanceTasks();
                        $InstanceTasks->generateFsoStatsErrand();
                    }

                    $this->response = $this->response->withStringBody(json_encode(true));
                } else {
                    $this->response = $this->response->withStringBody(json_encode(true));
                }
                return $this->response;

            } else {
                $this->response = $this->response->withType('json');
                $this->response = $this->response->withStringBody(json_encode(false));
                return $this->response;
            }
        }

        return $this->redirect(['action' => 'login']);
    }

    /**
     * Login to the application
     *
     * @param null $autoLoginToken
     * @return Response|null
     */
    public function login($autoLoginToken = null): ?Response
    {
        $dbDriver = ($this->Users->getConnection())->getDriver();
        if ($dbDriver instanceof Sqlite) {
            $caseSensitive = true;
        } else {
            $caseSensitive = false;
        }
        $this->set('caseSensitive', $caseSensitive);

        //see if they are already logged in
        if (@$this->Auth instanceof AuthComponent && $this->Auth->user()) {
            return $this->redirect($this->Auth->redirectUrl());
        }

        $user = $this->Users->newEmptyEntity();

        $userDetails = false;

        //Form Authentication
        if ($this->request->is('post')) {
            $userDetails = $this->Auth->identify();

            if (!$userDetails) {
                $this->Flash->error(__('Invalid username or password, try again.'));
            }
        }

        //Token Authentication
        if ($autoLoginToken) {
            $isAutoLoginTokenValid = $this->Seeds->validateSeed($autoLoginToken);
            if ($isAutoLoginTokenValid) {
                $this->Seeds->increaseBid($autoLoginToken);
                $autoLoginTokenDetails = $this->Seeds->getSeed($autoLoginToken);
                if (is_numeric($autoLoginTokenDetails->user_link)) {
                    $user = $this->Users->find('all')
                        ->where(['id' => $autoLoginTokenDetails->user_link])
                        ->first();
                    if ($user) {
                        $userDetails = $user->toArray();
                    }
                }
            }

            if (!$userDetails) {
                $this->Flash->error(__('Invalid login token, try again.'));
                return $this->redirect(['controller' => 'login']);
            }
        }

        //check if we need to reset password
        if ($userDetails) {
            if ($this->Users->isPasswordExpired($userDetails)) {
                $this->Flash->warning(__('Your password has expired, please enter a new password.'));
                $options = [
                    'expiration' => new DateTime('+ 1 hour'),
                    'user_link' => $userDetails['id'],
                ];
                $autoLoginToken = $this->Seeds->createSeedReturnToken($options);

                $options = [
                    'url' => ['controller' => 'users', 'action' => 'reset', '{token}', $autoLoginToken],
                    'expiration' => new DateTime('+ 1 hour'),
                    'user_link' => $userDetails['id'],
                ];
                $token = $this->Seeds->createSeedReturnToken($options);

                $this->Auditor->auditInfo(__('Forcing password reset for ID:{0} {1} {2}.', $userDetails['id'], $userDetails['first_name'], $userDetails['last_name']));

                return $this->redirect(['controller' => 'users', 'action' => 'reset', $token, $autoLoginToken]);
            }
        }

        //check if the user account is valid (i.e. stuff other than username and password)
        if ($userDetails) {
            $accountStatus = $this->Users->validateAccountStatus($userDetails);
            if (!$accountStatus) {
                $userDetails = false;
                $messages = $this->Users->getAuthError();
                foreach ($messages as $message) {
                    $this->Flash->error($message);
                }
            }
        }

        //finally, log them in
        if ($userDetails) {
            $roles = $this->Users->listOfRolesForUser($userDetails['id']);
            $userDetails['roles_list'] = $roles;

            $this->Auth->setUser($userDetails);
            $this->Auditor->auditInfo(__('User ID:{0} {1} {2} logged in.', $userDetails['id'], $userDetails['first_name'], $userDetails['last_name']));
            return $this->redirect($this->Auth->redirectUrl());
        }

        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        return null;
    }

    /**
     * Logout of the application
     *
     * @return Response|null
     */
    public function logout(): ?Response
    {
        $this->Auth->logout();
        $this->request->getSession()->destroy();
        $this->Flash->success(__('Successfully logged out'));

        //todo launch bug as this does not work
        //return $this->redirect($this->Auth->logout());

        //this works
        $url = Router::fullBaseUrl();
        header('Location: ' . $url);
        exit;
    }

    /**
     * Reset the password
     *
     * @param string $token
     * @param string $autoLoginToken
     * @return Response|null
     */
    public function reset(string $token = '', string $autoLoginToken = ''): ?Response
    {
        $this->set('token', $token);

        if (!$token) {
            return $this->redirect(['controller' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('reset_error_message');
            $header = "Oops!";
            $message = "Sorry, the link to reset your password is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $this->Seeds->getSeed($token);
        $user = $this->Users->find('all')->where(['id' => $seed->user_link])->first();

        if (!$user) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to reset your password is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            //avoid mass-assignment attack
            $options = [
                'fieldList' => [
                    'password',
                    'password_1',
                    'is_confirmed'
                ]
            ];

            $patchData = $this->request->getData();
            $patchData['is_confirmed'] = true;
            $user = $this->Users->patchEntity($user, $patchData, $options);

            $user->password_expiry = $this->Settings->getPasswordExpiryDate();

            if ($this->Users->save($user)) {
                //increase bid on the token
                $this->Seeds->increaseBid($token);

                if ($autoLoginToken) {
                    $isAutoLoginTokenValid = $this->Seeds->validateSeed($autoLoginToken);
                    if ($isAutoLoginTokenValid) {

                        $this->Seeds->increaseBid($autoLoginToken);
                        $autoLoginTokenDetails = $this->Seeds->getSeed($autoLoginToken);

                        $options = [
                            'url' => ['controller' => 'users', 'action' => 'login', '{token}'],
                            'expiration' => new DateTime('+ 1 hour'),
                            'user_link' => $autoLoginTokenDetails->user_link,
                        ];
                        $newAutoLoginToken = $this->Seeds->createSeedReturnToken($options);

                        $this->Flash->success(__('Your password has been updated.'));
                        return $this->redirect(['controller' => 'users', 'action' => 'login', $newAutoLoginToken]);
                    }
                }

                $this->Flash->success(__('Password updated, please sign in.'));
                return $this->redirect(['action' => 'login']);
            } else {
                $this->Flash->error(__('Error updating password. Please, try again.'));
            }
        }

        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        return null;
    }

    /**
     * Login to the application
     *
     * @return Response|null
     */
    public function forgot(): ?Response
    {
        $dbDriver = ($this->Users->getConnection())->getDriver();
        if ($dbDriver instanceof Sqlite) {
            $caseSensitive = true;
        } else {
            $caseSensitive = false;
        }
        $this->set('caseSensitive', $caseSensitive);

        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $user = $this->Users->getUserByData($data);

            if ($user) {
                $this->Users->sendResetLink($user);
            }

            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Done!";
            $message = "If an account exists, an email will be sent.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        return null;
    }

    /**
     * Confirm the account (usually via link sent by email)
     *
     * @param string $token
     * @return Response|null
     */
    public function confirm(string $token = ''): ?Response
    {
        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['controller' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('reset_error_message');
            $header = "Oops!";
            $message = "Sorry, the link to confirm your email and account is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $this->Seeds->getSeed($token);
        /** @var User $user */
        $user = $this->Users->find('all')->where(['id' => $seed->user_link])->first();

        if (!$user) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to confirm your email and account is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        //can only confirm if user_status is active or invitation
        $allowedIds = $this->Users->UserStatuses->getIdsByNameOrAlias(['active', 'invitation']);
        if (!in_array($user->user_statuses_id, $allowedIds)) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to confirm your email and account is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            //avoid mass-assignment attack
            $options = [
                'fieldList' => [
                    'password',
                    'password_1',
                    'is_confirmed',
                    'user_statuses_id',
                ]
            ];

            $patchData = $this->request->getData();
            $patchData['is_confirmed'] = true;
            $patchData['user_statuses_id'] = $this->Users->UserStatuses->getIdByNameOrAlias('active');
            $user = $this->Users->patchEntity($user, $patchData, $options);

            $user->password_expiry = $this->Settings->getPasswordExpiryDate();

            if ($this->Users->save($user)) {
                //increase bid on the token
                $this->Seeds->increaseBid($token);

                //auto-login the user
                $options = [
                    'url' => ['controller' => 'users', 'action' => 'login', '{token}'],
                    'expiration' => new DateTime('+ 1 hour'),
                    'user_link' => $user->id,
                ];
                $newAutoLoginToken = $this->Seeds->createSeedReturnToken($options);

                $this->Flash->success(__('Your email and account have been confirmed.'));
                return $this->redirect(['controller' => 'users', 'action' => 'login', $newAutoLoginToken]);

            } else {
                $this->Flash->error(__('Error confirming your email and account. Please, try again.'));
            }
        }

        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        return null;
    }

    /**
     * Login to the application
     *
     * @return Response|null
     */
    public function request(): ?Response
    {
        $allowedToRequest = ['self', 'admin'];
        if (!in_array($this->Settings->getSetting('self_registration'), $allowedToRequest)) {
            return $this->redirect(['controller' => 'login']);
        }

        $dbDriver = ($this->Users->getConnection())->getDriver();
        if ($dbDriver instanceof Sqlite) {
            $caseSensitive = true;
        } else {
            $caseSensitive = false;
        }
        $this->set('caseSensitive', $caseSensitive);

        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $selfRegistrationMode = $this->Settings->getSetting('self_registration');

            $data = $this->request->getData();

            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');

            if ($selfRegistrationMode === 'self') {
                $this->Users->sendSelfRegistrationLink($data);
                $header = "Done!";
                $message = "Please check your Inbox for a confirmation email.";
            } elseif ($selfRegistrationMode === 'admin') {
                $this->Users->sendAdminApprovalRegistrationLink($data);
                $header = "Done!";
                $message = "An Administrator has been notified of your request. Please wait for them to confirm your account.";
            } else {
                $header = "Oops!";
                $message = "Sorry, access is by invitation only.";
            }
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        return null;
    }

    /**
     * Administrator to Approve/Deny account request (usually via link sent by email)
     *
     * @param string $token
     * @return Response|null
     */
    public function approve(string $token = ''): ?Response
    {

        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['controller' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to approve the request is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $this->Seeds->getSeed($token);
        /** @var User $user */
        $user = $this->Users->find('all')->where(['id' => $seed->user_link])->first();

        if (!$user) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to approve the request is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $user->password_expiry = $this->Settings->getPasswordExpiryDate();
        $user->is_confirmed = false; //they still need to confirm their email
        $user->user_statuses_id = $this->Users->UserStatuses->getIdByNameOrAlias('active');

        if ($this->Users->save($user)) {
            //increase bid on the token
            $this->Seeds->increaseBid($token);

            //show a success message
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Success!";
            $message = "The User has been approved and they will be notified by email.";
            $this->set('header', $header);
            $this->set('message', $message);

            //schedule an email to the new user
            $this->Users->sendApprovedEmail($user);

            return null;
        } else {
            //show a fail message
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Failed to approve the User, please try again.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }
    }

    /**
     * Administrator to Approve/Deny account request (usually via link sent by email)
     *
     * @param string $token
     * @return Response|null
     */
    public function deny(string $token = ''): ?Response
    {

        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['controller' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to deny the request is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $this->Seeds->getSeed($token);
        /** @var User $user */
        $user = $this->Users->find('all')->where(['id' => $seed->user_link])->first();

        if (!$user) {
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to deny the request is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $user->password_expiry = $this->Settings->getPasswordExpiryDate();
        $user->is_confirmed = false; //they still need to confirm their email
        $user->user_statuses_id = $this->Users->UserStatuses->getIdByNameOrAlias('rejected');

        if ($this->Users->save($user)) {
            //increase bid on the token
            $this->Seeds->increaseBid($token);

            //show a success message
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Success!";
            $message = "The User has been denied and they will be notified by email.";
            $this->set('header', $header);
            $this->set('message', $message);

            //schedule an email to the new user
            $this->Users->sendDeniedEmail($user);

            return null;
        } else {
            //show a fail message
            $this->viewBuilder()->setLayout('one-page-form');
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Failed to deny the User, please try again.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }
    }

}
