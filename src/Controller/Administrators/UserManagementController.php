<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Model\Table\SubscriptionsTable;
use App\Model\Table\UsersTable;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * UserManagement Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UserManagementController extends AppController
{
    protected Table|UsersTable $Users;
    protected Table|SubscriptionsTable $Subscriptions;

    /**
     * Initialize controller
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Users = TableRegistry::getTableLocator()->get('Users');
        $this->set('typeMap', $this->Users->getSchema()->typeMap());

        $this->Subscriptions = TableRegistry::getTableLocator()->get('Subscriptions');
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
     * Index method
     *
     * @return Response|null
     */
    public function index()
    {
        return $this->redirect(['action' => 'profile']);
    }

    /**
     * Update method - Users can self-update their details
     *
     * @return Response|null
     */
    public function profile(): ?Response
    {
        $id = $this->Auth->user('id');
        $user = $this->Users->get($id, contain: ['UserStatuses', 'Roles', 'UserLocalizations', 'Subscriptions']);

        $allSubscriptions = $this->Subscriptions->findAllSubscriptions();
        $myAllowedSubscriptions = $this->Subscriptions->findMyAllowedSubscriptions($id);
        $myActiveSubscriptions = $this->Subscriptions->findmyActiveSubscriptions($id);
        $this->set(compact(['allSubscriptions', 'myActiveSubscriptions', 'myAllowedSubscriptions']));

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
                    'subscriptions' => [
                        '_ids'
                    ]
                ],
            ];

            $data = $this->request->getData();
            $data['user_localizations'][0]['user_id'] = $id;
            $data['subscriptions'] = empty($data['subscriptions']) ? [] : $data['subscriptions'];

            //get what they can subscribe to
            $subscriptionIds = [];
            foreach ($data['subscriptions'] as $subKey => $subValue) {
                if (asBool($subValue) === true) {
                    $subscriptionIds[] = $subKey;
                }
            }
            unset($data['subscriptions']);
            $data['subscriptions']['_ids'] = $subscriptionIds;

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


}
