<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Table\SubscriptionsTable;
use App\Model\Table\UsersTable;
use App\Utility\Instances\InstanceTasks;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use TinyAuth\Controller\Component\AuthComponent;

/**
 * UserHub Controller
 *
 * This controller splits of Actions that outside of an authenticated session.
 * e.g. password resets and login/logout
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UserHubController extends AppController
{
    protected Table|UsersTable $Users;

    /**
     * Initialization hook method.
     */
    public function initialize(): void
    {
        parent::initialize();

        // Initialize the Users table
        $this->Users = TableRegistry::getTableLocator()->get('Users');
        $this->set('typeMap', $this->Users->getSchema()->typeMap());

        // Allow unauthenticated access to login and related actions
        $this->Authentication->addUnauthenticatedActions(['login', 'logout']);
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

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

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
        $this->viewBuilder()->setLayout('authentication-form');

        $this->set('token', $token);

        if (!$token) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if (!$isTokenValid) {
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
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to reset your password is no longer valid for this User.";
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
                            'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'login', '{token}'],
                            'expiration' => new DateTime('+ 1 hour'),
                            'user_link' => $autoLoginTokenDetails->user_link,
                        ];
                        $newAutoLoginToken = $this->Seeds->createSeedReturnToken($options);

                        $this->Flash->success(__('Your password has been updated.'));
                        return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login', $newAutoLoginToken]);
                    }
                }

                $this->Flash->success(__('Password updated, please sign in.'));
                return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
            } else {
                $this->Flash->error(__('Error updating password. Please, please try again.'));
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
        $this->viewBuilder()->setLayout('authentication-form');

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
        $this->viewBuilder()->setLayout('authentication-form');

        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
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
                    'username',
                    'email',
                    'password',
                    'password_1',
                    'is_confirmed',
                    'user_statuses_id',
                ]
            ];

            $patchData = $this->request->getData();
            $patchData['password_expiry'] = $this->Settings->getPasswordExpiryDate();
            $patchData['is_confirmed'] = true;
            $patchData['user_statuses_id'] = $this->Users->UserStatuses->getIdByNameOrAlias('active');
            $user = $this->Users->patchEntity($user, $patchData, $options);

            if ($this->Users->save($user)) {
                //increase bid on the token
                $this->Seeds->increaseBid($token);

                //auto-login the user
                $options = [
                    'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'login', '{token}'],
                    'expiration' => new DateTime('+ 1 hour'),
                    'user_link' => $user->id,
                ];
                $newAutoLoginToken = $this->Seeds->createSeedReturnToken($options);

                $this->Flash->success(__('Your email and account have been confirmed.'));
                return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login', $newAutoLoginToken]);

            } else {
                $this->Flash->error(__('Error confirming your email and account. Please, please try again.'));
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
        $this->viewBuilder()->setLayout('authentication-form');

        $allowedToRequest = ['self', 'admin'];
        if (!in_array($this->Settings->getSetting('self_registration'), $allowedToRequest)) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
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
            $this->viewBuilder()->setTemplate('generic_message');

            $selfRegistrationMode = $this->Settings->getSetting('self_registration');

            $data = $this->request->getData();

            //check id user already exists
            $userCheck = $this->Users->getUserByData($data);
            if ($userCheck) {
                $header = "Oops!";
                $message = "Sorry, This account already exists.";
            } elseif ($selfRegistrationMode === 'self') {
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
        $this->viewBuilder()->setLayout('authentication-form');

        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
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
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to approve the request is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }


        if ($this->request->is(['patch', 'post', 'put'])) {
            //avoid mass-assignment attack
            $options = [
                'fieldList' => [
                    'username',
                    'email',
                    'password',
                    'password_1',
                    'is_confirmed',
                    'user_statuses_id',
                ]
            ];

            $patchData = $this->request->getData();
            $patchData['password_expiry'] = $this->Settings->getPasswordExpiryDate();
            $patchData['is_confirmed'] = false; //they still need to confirm their email
            $patchData['user_statuses_id'] = $this->Users->UserStatuses->getIdByNameOrAlias('invitation');
            $user = $this->Users->patchEntity($user, $patchData, $options);

            if ($this->Users->save($user)) {
                //increase bid on the token
                $this->Seeds->increaseBid($token);

                //show a success message
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
                $this->Flash->error(__('Could not save the User, please try again.'));
            }
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
    public function deny(string $token = ''): ?Response
    {
        $this->viewBuilder()->setLayout('authentication-form');

        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
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
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Failed to deny the User, please try again.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }
    }

    /**
     * Prime the application for use.
     * Put logic in here to warm up the site for the User (e.g. build caches or sync operations)
     *
     * @return Response|null
     */
    public function primer(): ?Response
    {
        //only allow ajax requests
        if (!$this->request->is('ajax')) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
        }

        //warm up the site
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

                if ($this->Users->doesUserHaveRole($user, 'superadmin')) {
                    //do FSO stats
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
            $this->response = $this->response->withStringBody(json_encode(true));
            return $this->response;
        }
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
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            // User is already authenticated, redirect them
            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);
        }

        $user = $this->Users->newEmptyEntity();

        $userDetails = false;

        //Form Authentication
        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();

            if ($result->isValid()) {
                //Get the authenticated user data
                $identifiedUser = $result->getData();
                $userDetails = $this->Users->getExtendedUserSessionData($identifiedUser->id, true);
                        } else {
                $this->Flash->error(__('Invalid username or password, please try again.'));
            }
        }

        //Token Authentication
        if ($autoLoginToken) {
            $isAutoLoginTokenValid = $this->Seeds->validateSeed($autoLoginToken);
            if ($isAutoLoginTokenValid) {
                $this->Seeds->increaseBid($autoLoginToken);
                $autoLoginTokenDetails = $this->Seeds->getSeed($autoLoginToken);
                if (is_numeric($autoLoginTokenDetails->user_link)) {
                    /** @var User $user */
                    $user = $this->Users->find('all')
                        ->where(['id' => $autoLoginTokenDetails->user_link])
                        ->first();
                    if ($user) {
                        $userDetails = $this->Users->getExtendedUserSessionData($user->id, true);
                    }
                }
            }

            if (!$userDetails) {
                $this->Flash->error(__('Invalid login token, please try again.'));
                return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
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
                    'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'reset', '{token}', $autoLoginToken],
                    'expiration' => new DateTime('+ 1 hour'),
                    'user_link' => $userDetails['id'],
                ];
                $token = $this->Seeds->createSeedReturnToken($options);

                $this->Auditor->auditInfo(__('Forcing password reset for ID:{0} {1} {2}.', $userDetails['id'], $userDetails['first_name'], $userDetails['last_name']));

                return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'reset', $token, $autoLoginToken]);
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
            // Persist the user session using the new Authentication plugin
            $this->Authentication->setIdentity($userDetails);
            $this->Auditor->auditInfo(__('User ID:{0} {1} {2} logged in.', $userDetails['id'], $userDetails['first_name'], $userDetails['last_name']));

            $target = $this->Authentication->getLoginRedirect() ?? '/';
            return $this->redirect($target);
        }

        $this->viewBuilder()->setLayout('authentication-form');
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
        //destroy the dynamically created user cache
        $identity = $this->Authentication->getIdentity();
        if ($identity && isset($identity->id) && $identity->id) {
            $this->Users->destroyCacheForUser($identity->id);
        }

        $this->Authentication->logout();
        $this->request->getSession()->destroy();
        $this->Flash->success(__('Successfully logged out'));

        //this works
        header('Location: ' . APP_LINK_POST_LOGOUT);
        exit;
    }


    /**
     * Users can manage their subscription
     *
     * @param string $token
     * @return Response|null
     */
    public function subscription(string $token = ''): ?Response
    {
        $this->viewBuilder()->setLayout('authentication-form');

        $this->set('token', $token);

        if ($token == false) {
            return $this->redirect(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']);
        }

        $isTokenValid = $this->Seeds->validateSeed($token);

        if ($isTokenValid == false) {
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to manage your subscription is no longer valid. Please try a more recent link from a more recent Bulletin sent to you.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $this->Seeds->getSeed($token);
        /** @var User $user */
        $user = $this->Users->find('all')->where(['id' => $seed->user_link])->first();

        if (!$user) {
            $this->viewBuilder()->setTemplate('generic_message');
            $header = "Oops!";
            $message = "Sorry, the link to manage your subscription is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        /** @var SubscriptionsTable $Subscriptions */
        $Subscriptions = $this->fetchTable('Subscriptions');
        $allSubscriptions = $Subscriptions->findAllSubscriptions();
        $myActiveSubscriptions = $Subscriptions->findmyActiveSubscriptions($user->id);
        $this->set(compact(['allSubscriptions', ['myActiveSubscriptions']]));

        if ($this->request->is(['patch', 'post', 'put'])) {
            //avoid mass-assignment attack
            $options = [
                'fieldList' => [
                    'subscriptions' => [
                        '_ids'
                    ]
                ]
            ];

            $patchData = $this->request->getData();

            //get what they can subscribe to
            $subscriptionIds = [];
            foreach ($patchData['subscriptions'] as $subKey => $subValue) {
                if (asBool($subValue) === true) {
                    $subscriptionIds[] = $subKey;
                }
            }
            unset($patchData['subscriptions']);
            $patchData['subscriptions']['_ids'] = $subscriptionIds;

            $user = $this->Users->patchEntity($user, $patchData, $options);

            if ($this->Users->save($user)) {
                //show a success message
                $this->viewBuilder()->setTemplate('generic_message');
                $header = "Success!";
                $message = "Your subscription preferences have been updated.";
                $this->set('header', $header);
                $this->set('message', $message);

                return null;
            } else {
                //show a fail message
                $this->Flash->error(__('Could not save the User, please try again.'));
            }
        }


        $this->set(compact('user'));
        $this->set('_serialize', ['user']);

        return null;
    }

}
