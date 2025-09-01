<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Role;
use App\Model\Entity\User;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Cache\Cache;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Http\Session;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\UserStatusesTable&\Cake\ORM\Association\BelongsTo $UserStatuses
 * @property \App\Model\Table\UserLocalizationsTable&\Cake\ORM\Association\HasMany $UserLocalizations
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsToMany $Roles
 * @property \App\Model\Table\SubscriptionsTable&\Cake\ORM\Association\BelongsToMany $Subscriptions
 *
 * @method User newEmptyEntity()
 * @method User newEntity(array $data, array $options = [])
 * @method User[] newEntities(array $data, array $options = [])
 * @method User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method User findOrCreate($search, ?callable $callback = null, $options = [])
 * @method User patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method User|false save(EntityInterface $entity, $options = [])
 * @method User saveOrFail(EntityInterface $entity, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends AppTable
{
    private $authError = [];
    private \Cake\ORM\Table|SettingsTable $Settings;
    private \Cake\ORM\Table|SeedsTable $Seeds;

    public bool|array $userInvitationData = false;

    private array $userEntitiesWithRolesCache = [];

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('UserStatuses', [
            'foreignKey' => 'user_statuses_id',
        ]);
        $this->hasMany('UserLocalizations', [
            'foreignKey' => 'user_id',
        ]);

        $this->belongsToMany('Roles', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'role_id',
            'joinTable' => 'roles_users',
        ]);
        $this->belongsToMany('Subscriptions', [
            'foreignKey' => 'user_id',
            'targetForeignKey' => 'subscription_id',
            'joinTable' => 'subscriptions_users',
        ]);

        $this->Settings = TableRegistry::getTableLocator()->get('Settings');
        $this->Seeds = TableRegistry::getTableLocator()->get('Seeds');

        $this->initializeSchemaJsonFields($this->getJsonFields());
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('username')
            ->maxLength('username', 50)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        //check if strong passwords are required
        $password_strong_bool = $this->Settings->getSetting('password_strong_bool');
        if ($password_strong_bool == true) {
            $this->validationStrongPassword($validator);
        }

        $validator
            ->add('password', 'passwordsAreMatched', [
                'rule' => ['isPasswordsMatched', 'password_1'],
                'message' => __('Passwords do not match.'),
                'provider' => 'table',
            ]);

//todo implement old_password check on password changing
//        $validator
//            ->add('old_password','custom',[
//                'rule'=>  function($value, $context){
//                    $user = $this->get($context['data']['id']);
//                    if ($user) {
//                        if ((new DefaultPasswordHasher)->check($value, $user->password)) {
//                            return true;
//                        }
//                    }
//                    return false;
//                },
//                'message'=>'The old password does not match the current password!',
//            ])
//            ->notEmpty('old_password');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 255)
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 255)
            ->requirePresence('last_name', 'create')
            ->notEmptyString('last_name');

        $validator
            ->scalar('address_1')
            ->maxLength('address_1', 1024)
            ->allowEmptyString('address_1');

        $validator
            ->scalar('address_2')
            ->maxLength('address_2', 1024)
            ->allowEmptyString('address_2');

        $validator
            ->scalar('suburb')
            ->maxLength('suburb', 1024)
            ->allowEmptyString('suburb');

        $validator
            ->scalar('state')
            ->maxLength('state', 50)
            ->allowEmptyString('state');

        $validator
            ->scalar('post_code')
            ->maxLength('post_code', 50)
            ->allowEmptyString('post_code');

        $validator
            ->scalar('country')
            ->maxLength('country', 255)
            ->allowEmptyString('country');

        $validator
            ->scalar('mobile')
            ->maxLength('mobile', 50)
            ->allowEmptyString('mobile');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 50)
            ->allowEmptyString('phone');

        $validator
            ->dateTime('activation')
            ->allowEmptyDateTime('activation');

        $validator
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->boolean('is_confirmed')
            ->allowEmptyString('is_confirmed');

        $validator
            ->integer('user_statuses_id')
            ->allowEmptyString('user_statuses_id');

        $validator
            ->dateTime('password_expiry')
            ->allowEmptyDateTime('password_expiry');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [];

        return $jsonFields;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->existsIn('user_statuses_id', 'UserStatuses'), ['errorField' => 'user_statuses_id']);

        return $rules;
    }

    /**
     * Custom finder for the Auth component.
     * Roles table needs to be joined for the TinyAuth plugin
     *
     * @param Query $query
     * @param array $options
     * @return Query
     */
    public function findAuth(Query $query, array $options): Query
    {
        $query = $query
            ->contain('Roles')
            ->contain('UserStatuses');

        return $query;
    }

    /**
     * Strong password validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationStrongPassword(Validator $validator): Validator
    {

        $password_strong_length = $this->Settings->getSetting('password_strong_length');
        $password_strong_lower = $this->Settings->getSetting('password_strong_lower');
        $password_strong_upper = $this->Settings->getSetting('password_strong_upper');
        $password_strong_number = $this->Settings->getSetting('password_strong_number');
        $password_strong_special = $this->Settings->getSetting('password_strong_special');

        $validator
            ->add('password', [
                'length' => [
                    'rule' => ['minLength', $password_strong_length],
                    'message' => __('The password must be at least {0} characters long.', $password_strong_length)
                ]
            ]);

        if ($password_strong_lower == true) {
            $validator
                ->add('password', 'containsLower', [
                    'rule' => [$this, 'passwordStrongLower'],
                    'message' => 'The password must contain a lowercase character'
                ]);
        }

        if ($password_strong_upper == true) {
            $validator
                ->add('password', 'containsUpper', [
                    'rule' => [$this, 'passwordStrongUpper'],
                    'message' => 'The password must contain an uppercase character'
                ]);
        }

        if ($password_strong_number == true) {
            $validator
                ->add('password', 'containsNumber', [
                    'rule' => [$this, 'passwordStrongNumber'],
                    'message' => 'The password must contain a number'
                ]);
        }

        if ($password_strong_special == true) {
            $validator
                ->add('password', 'containsSpecial', [
                    'rule' => [$this, 'passwordStrongSpecial'],
                    'message' => 'The password must contain a special character'
                ]);
        }


        return $validator;
    }

    /**
     * Check for matching passwords
     *
     * @param $check
     * @param $field
     * @param array $context
     * @return bool
     */
    public function isPasswordsMatched($check, $field, array $context): bool
    {
        if (!isset($context['data']) || !array_key_exists($field, $context['data'])) {
            return false;
        }

        if ($check === $context['data'][$field]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks for a strong password lowercase character:
     *
     * @param string $password
     * @param array $context
     * @return boolean
     */
    public function passwordStrongLower($password, array $context): bool
    {
        $return = true;
        // lowercase
        if (!preg_match("#[a-z]#", $password)) {
            $return = false;
        }
        return $return;
    }

    /**
     * Checks for a strong password uppercase character:
     *
     * @param string $password
     * @param array $context
     * @return boolean
     */
    public function passwordStrongUpper($password, array $context): bool
    {
        $return = true;
        // uppercase
        if (!preg_match("#[A-Z]#", $password)) {
            $return = false;
        }
        return $return;
    }

    /**
     * Checks for a strong password number character:
     *
     * @param string $password
     * @param array $context
     * @return boolean
     */
    public function passwordStrongNumber($password, array $context): bool
    {
        $return = true;
        // number
        if (!preg_match("#[0-9]#", $password)) {
            $return = false;
        }
        return $return;
    }

    /**
     * Checks for a strong password special character:
     *
     * @param string $password
     * @param array $context
     * @return boolean
     */
    public function passwordStrongSpecial($password, array $context): bool
    {
        $return = true;
        // special characters
        if (!preg_match("#\W+#", $password)) {
            $return = false;
        }
        return $return;
    }

    /**
     * Check if the password has expired
     *
     * @param $userDetails
     * @return bool
     */
    public function isPasswordExpired($userDetails): bool
    {
        if (is_null($userDetails['password_expiry'])) {
            return true;
        }

        $frozenTimeObj = new DateTime('now');
        $passwordExpiry = $userDetails['password_expiry'];

        if ($frozenTimeObj->greaterThanOrEquals($passwordExpiry) === true) {
            return true;
        }

        return false;
    }

    /**
     * Check if the account status is in order
     *
     * @param array $userDetails
     * @return bool
     */
    public function validateAccountStatus(array $userDetails): bool
    {
        /**
         * @var DateTime $activation
         * @var DateTime $expiration
         */
        $return = true;

        //Check if account is 'Active'
        $user_statuses_id = $userDetails['user_statuses_id'];
        $activeStatusIds = $this->UserStatuses->getActiveStatusIds();
        $inactiveStatusList = $this->UserStatuses->getInactiveStatusList();
        if (!in_array($user_statuses_id, $activeStatusIds)) {
            if ($inactiveStatusList[$user_statuses_id] === 'banned') {
                $this->setAuthError('Sorry, your account has been banned.');
            } elseif ($inactiveStatusList[$user_statuses_id] === 'disabled') {
                $this->setAuthError('Sorry, your account has been disabled.');
            } elseif ($inactiveStatusList[$user_statuses_id] === 'pending') {
                $this->setAuthError('Sorry, your account is pending approval by an Administrator.');
            } elseif ($inactiveStatusList[$user_statuses_id] === 'rejected') {
                $this->setAuthError('Sorry, your account has been rejected by an Administrator.');
            } else {
                $this->setAuthError('Sorry, your account has been suspended');
            }
            //return immediately as there is no point in moving forward
            return false;
        }

        //Check if email address has been confirmed
        $is_confirmed = $userDetails['is_confirmed'];
        if ($is_confirmed != true) {
            $this->setAuthError('Sorry, your email address has not been confirmed. Please check your inbox for a confirmation link.');
            $return = false;
        }

        $frozenTimeObj = new DateTime('now');

        //Check if account has expiry limits
        $activation = $userDetails['activation'];
        $expiration = $userDetails['expiration'];
        $activationReadable = (!is_null($activation) ? $activation->i18nFormat("EEEE, MMMM d, yyyy @ h:mm a", LCL_TZ) : '');
        $expirationReadable = (!is_null($expiration) ? $expiration->i18nFormat("EEEE, MMMM d, yyyy @ h:mm a", LCL_TZ) : '');

        //activation and expiration set so check in between
        if ($activation && $expiration) {
            if ($frozenTimeObj->greaterThanOrEquals($activation) === false || $frozenTimeObj->lessThanOrEquals($expiration) === false) {
                if ($frozenTimeObj->greaterThanOrEquals($activation) === false) {
                    $this->setAuthError(
                        __('Sorry, your account will activate on {0}',
                            $activationReadable)
                    );
                    $return = false;
                }

                if ($frozenTimeObj->lessThanOrEquals($expiration) === false) {
                    $this->setAuthError(
                        __('Sorry, your account expired on {0}',
                            $expirationReadable)
                    );
                    $return = false;
                }
            }
        }

        //activation set so check if greater
        if ($activation && is_null($expiration)) {
            if ($frozenTimeObj->greaterThanOrEquals($activation) === false) {
                $this->setAuthError(
                    __('Sorry, your account will activate on {0}',
                        $activationReadable)
                );
                $return = false;
            }
        }

        //expiration set so check if less
        if (is_null($activation) && $expiration) {
            if ($frozenTimeObj->lessThanOrEquals($expiration) === false) {
                $this->setAuthError(
                    __('Sorry, your account expired on {0}',
                        $expirationReadable)
                );
                $return = false;
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getAuthError(): array
    {
        return $this->authError;
    }

    /**
     * @param mixed $authError
     */
    public function setAuthError(mixed $authError)
    {
        if (!is_array($authError)) {
            $authError = [$authError];
        }

        if (is_array($authError)) {
            $this->authError = array_merge($this->authError, $authError);
        }
    }

    public function getDefaultUserProperties(): array
    {
        $default = [
            'email' => '',
            'username' => '',
            'password' => sha1(Security::randomBytes(1024)),
            'first_name' => '',
            'last_name' => '',
            'address_1' => '',
            'address_2' => '',
            'suburb' => '',
            'state' => '',
            'post_code' => '',
            'country' => '',
            'mobile' => '',
            'phone' => '',
            'activation' => new DateTime(),
            'expiration' => $this->Settings->getAccountExpirationDate(),
            'is_confirmed' => '0',
            'user_statuses_id' => $this->UserStatuses->getIdByNameOrAlias('disabled'),
            'roles' => [
                '_ids' => $this->Roles->getIdsByNameOrAlias('user')
            ],
            'password_expiry' => $this->Settings->getPasswordExpiryDate(),
        ];

        return $default;
    }

    public function idToName($id = null)
    {
        if (is_null($id) || empty($id)) {
            return false;
        }

        $users = $this->findById($id)->toArray();

        if ($users) {
            return $users[0]->full_name;
        } else {
            return '';
        }
    }

    public function nameToId($name = null)
    {
        if (is_null($name) || empty($name)) {
            return false;
        }

        $nameParts = explode(' ', $name);

        $users = false;
        if (count($nameParts) == 1) {
            $users = $this->find('all');
            $users = $users->orderByAsc('id');
            $users = $users->orWhere(['first_name LIKE' => '%' . $nameParts[0] . '%',]);
            $users = $users->orWhere(['last_name LIKE' => '%' . $nameParts[0] . '%',]);
            $users = $users->toArray();
        } elseif (count($nameParts) == 2) {
            $users = $this->find('all');
            $users = $users->orderByAsc('id');
            $users = $users->where(['first_name LIKE' => '%' . $nameParts[0] . '%',]);
            $users = $users->where(['last_name LIKE' => '%' . $nameParts[1] . '%',]);
            $users = $users->toArray();
        } else {
            $users = $this->find('all');
            $users = $users->orderByAsc('id');
            $users = $users->where(["first_name + ' ' + last_name LIKE '%{$name}%' "]);
            $users = $users->toArray();
        }

        if ($users) {
            return $users[0]->id;
        } else {
            return false;
        }
    }

    /**
     * Find Users by their Role
     *
     * @param string|array $roleNameOrAlias
     * @return Query
     */
    public function findUsersByRoleNameOrRoleAlias($roleNameOrAlias = '')
    {
        if (!is_array($roleNameOrAlias)) {
            $roleNameOrAlias = [$roleNameOrAlias];
        }

        $query = $this->find('all')
            ->matching('Roles', function ($q) use (&$roleNameOrAlias) {
                return $q->where(['OR' => ['Roles.name IN' => $roleNameOrAlias, 'Roles.alias IN' => $roleNameOrAlias]]);
            });

        return $query;
    }

    /**
     * Wrapper function
     *
     * @param string $roleNameOrAlias
     * @return array
     */
    public function listUsersByRoleNameOrRoleAlias($roleNameOrAlias = '')
    {
        return $this->findUsersByRoleNameOrRoleAlias($roleNameOrAlias)
            ->find('list', keyField: 'id', valueField: 'id')
            ->toArray();
    }

    /**
     * Find a user based on some information
     *
     * $data format see $this->createNewUserForInvitationOrRequest()
     *
     * @param array $data
     * @return User|false
     */
    public function getUserByData(array $data): User|false
    {
        /** @var User $user */
        $user = $this->find()
            ->where([
                "OR" => [
                    'email' => $data['email'],
                    'username' => $data['username'],
                ]
            ])
            ->first();

        return $user ?? false;

    }

    /**
     * Includes some calculated data for Session and Inactivity timeouts.
     * Simple caching mechanism with option to refresh the cache.
     *
     * @param mixed|null $id
     * @param bool $forceRefresh
     * @return array|mixed
     */
    public function getExtendedUserSessionData(mixed $id = null, bool $forceRefresh = false): mixed
    {
        //fallback User Data to avoid warnings
        $fallbackUserData = [
            'id' => 0,
            'roles' => [],
            'roles_list' => [],
            'role_groupings_list' => [],
            'session_timeout' => 300,
            'session_timeout_minutes' => intval(300 / 600),
            'inactivity_timeout' => 300,
            'inactivity_timeout_minutes' => intval(300 / 600),
        ];

        //clean up the $id or return fallback User Data
        if ($id === null || $id === false || $id === '') {
            $id = (new Session())->read("Auth.User.id");
            if (!$id) {
                return $fallbackUserData;
            }
        } elseif (is_numeric($id)) {
            $id = intval($id);
        } else {
            return $fallbackUserData;
        }

        //create a cache config to hold User Data
        $cacheConfigName = $this->createCacheForUser($id);

        //read data form cache and return if present
        if (!$forceRefresh) {
            $userDataFromCache = Cache::read('userSessionData', $cacheConfigName);
            if ($userDataFromCache) {
                return $userDataFromCache;
            }
        }

        $userEntity = $this->find('all')
            ->where(['id' => $id])
            ->contain(['Roles'])
            ->first();

        if (!$userEntity) {
            return $fallbackUserData;
        }

        $rolesList = $this->listOfRolesForUser($userEntity);
        $roleGroupingsList = $this->listOfRoleGroupingsForUser($userEntity);
        $sessionTimeout = $this->getUserRolesSessionTimeoutSeconds($userEntity);
        $inactivityTimeout = $this->getUserRolesInactivityTimeoutSeconds($userEntity);

        $userData = $userEntity->toArray();
        $userData['roles_list'] = $rolesList;
        $userData['role_groupings_list'] = $roleGroupingsList;
        $userData['session_timeout'] = $sessionTimeout;
        $userData['session_timeout_minutes'] = intval($sessionTimeout / 60);
        $userData['inactivity_timeout'] = $inactivityTimeout;
        $userData['inactivity_timeout_minutes'] = intval($inactivityTimeout / 60);
        $userData['cacheConfigName'] = $cacheConfigName;

        //write data to cache for next time
        Cache::write('userSessionData', $userData, $cacheConfigName);

        return $userData;
    }

    /**
     * Creates a Cache for a specific User.
     * Returns the name of the Cache so you can access the Cache elsewhere.
     *
     * @param int $id
     * @param int $addMinutes
     * @return string
     */
    public function createCacheForUser(int $id, int $addMinutes = 10): string
    {
        //create a config name to hold User Data
        $cacheConfigName = "user_results_{$id}";

        //create a cache config
        if (Cache::getConfig($cacheConfigName) == null) {
            Cache::setConfig($cacheConfigName, [
                'className' => 'File',
                'prefix' => 'mine_',
                'path' => CACHE . 'users/data/' . $id,
                'duration' => "+{$addMinutes} minute",
                'url' => env('CACHE_DEFAULT_URL', null),
            ]);
        }

        return $cacheConfigName;
    }

    /**
     * Destroys the Cache created by the above function.
     *
     * @param int $id
     * @return bool
     */
    public function destroyCacheForUser(int $id): bool
    {
        $cacheConfigName = "user_results_{$id}";

        $UserCacheConfig = Cache::getConfig($cacheConfigName);
        $path = ($UserCacheConfig['path']) ?? false;
        if ($UserCacheConfig) {
            Cache::clear($cacheConfigName);
            Cache::drop($cacheConfigName);
            if (is_dir($path)) {
                @rmdir($path);
            }
            return true;
        }

        return false;
    }

    /**
     * Gets the User's session timeout in seconds.
     * If the User has multiple roles, the shortest will be returned.
     *
     * @param int|User $user
     * @return int
     */
    public function getUserRolesSessionTimeoutSeconds(int|User $user): int
    {
        $timeout = 0;
        $user = $this->asEntityWithRoles($user);

        if (!$user) {
            return 300;
        }

        $roles = $user->roles;
        foreach ($roles as $role) {
            $roleTimeout = $role['session_timeout'] * 60;
            if ($timeout === 0) {
                $timeout = $roleTimeout;
            } else {
                $timeout = min($timeout, $roleTimeout);
            }
        }

        return $timeout;
    }

    /**
     * Gets the User's inactivity timeout in seconds.
     * If the User has multiple roles, the shortest will be returned.
     *
     * @param int|User $user
     * @return int
     */
    public function getUserRolesInactivityTimeoutSeconds(int|User $user): int
    {
        $timeout = 0;
        $user = $this->asEntityWithRoles($user);

        if (!$user) {
            return 300;
        }

        $roles = $user->roles;
        foreach ($roles as $role) {
            $roleTimeout = $role['inactivity_timeout'] * 60;
            if ($timeout === 0) {
                $timeout = $roleTimeout;
            } else {
                $timeout = min($timeout, $roleTimeout);
            }
        }

        return $timeout;
    }


    /**
     * @param int|User $user
     * @param string $role
     * @return bool
     */
    public function doesUserHaveRole(int|User $user, string $role): bool
    {
        $role = strtolower($role);

        $user = $this->asEntityWithRoles($user);

        if ($user instanceof User && isset($user->roles)) {
            foreach ($user->roles as $userRole) {
                if (strtolower($userRole->name) === $role || strtolower($userRole->alias) === $role) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param int|User $user
     * @return array
     */
    public function listOfRolesForUser(int|User $user): array
    {
        $user = $this->asEntityWithRoles($user);

        $roles = [];
        if ($user instanceof User && isset($user->roles)) {
            foreach ($user->roles as $userRole) {
                $roles[] = $userRole->name;
                $roles[] = $userRole->alias;
            }
        }

        return $roles;
    }

    /**
     * @param int|User $user
     * @return array
     */
    public function listOfRolesForUserByIdAndName(int|User $user): array
    {
        $user = $this->asEntityWithRoles($user);

        $roles = [];
        if ($user instanceof User && isset($user->roles)) {
            foreach ($user->roles as $userRole) {
                $roles[$userRole->id] = $userRole->name;
            }
        }

        return $roles;
    }

    /**
     * @param int|User $user
     * @return array
     */
    public function listOfRoleGroupingsForUser(int|User $user): array
    {
        $user = $this->asEntityWithRoles($user);

        $roleGroupings = [];
        if ($user instanceof User && isset($user->roles)) {
            foreach ($user->roles as $userRole) {
                $roleGroupings[] = $userRole->grouping;
            }
        }

        return $roleGroupings;
    }

    /**
     * Some Entities need to contain Roles.
     * Basic caching to speed up DB reads
     *
     * @param int|array|User $user
     * @return false|User
     */
    public function asEntityWithRoles(int|array|User $user): false|User
    {
        $userId = false;

        if (($user instanceof User) && !empty($user->roles)) {
            return $user;
        } elseif ($user instanceof User) {
            $userId = $user->id;
        }

        if (is_array($user)) {
            if (isset($user['id'])) {
                $userId = $user['id'];
            } else {
                return false;
            }
        }

        if (is_int($user)) {
            $userId = $user;
        }

        if (!$userId) {
            return false;
        }

        if (isset($this->userEntitiesWithRolesCache[$userId])) {
            return $this->userEntitiesWithRolesCache[$userId];
        }

        $user = $this->find('all')
            ->where(['id' => $userId])
            ->contain(['Roles'])
            ->first();

        if (!$user) {
            return false;
        }

        $this->userEntitiesWithRolesCache[$userId] = $user;

        return $user;
    }

    /**
     * Send a reset link to the given User
     *
     * Like most sites, we don't send back a response if the user is found or not.
     *
     * @param User $user
     */
    public function sendResetLink(User $user)
    {
        /**
         * @var MessagesTable $Messages
         * @var User $user
         */

        //schedule an email
        $options = [
            'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'reset', '{token}'],
            'expiration' => new DateTime('+ 1 hour'),
            'user_link' => $user->id,
        ];
        $token = $this->Seeds->createSeedReturnToken($options);


        $data = [
            'name' => 'Reset Password',
            'description' => __("User {0} initiated Password Reset", $user->logger_name),
            'transport' => 'default',
            'profile' => 'default',
            'layout' => 'user_messages',
            'template' => 'user_reset_password',
            'view_vars' => [
                'entities' => [
                    'user' => $user->id,
                ],
                'token' => $token,
                'url' => Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'reset', $token], true),
            ],
            'email_to' => [$user->email => $user->full_name],
            'email_from' => $this->Settings->getEmailForCakePhpMailer(),
            'subject' => __("{0}: Password reset link for {1}", APP_NAME, $user->full_name),
        ];
        $Messages = TableRegistry::getTableLocator()->get('messages');
        $Messages->createMessage($data);

    }

    /**
     * Email the given User
     *
     * @param User $user
     */
    public function sendApprovedEmail(User $user)
    {
        /**
         * @var MessagesTable $Messages
         * @var User $user
         */

        //schedule an email
        $options = [
            'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'confirm', '{token}'],
            'expiration' => new DateTime('+ 1 hour'),
            'user_link' => $user->id,
        ];
        $token = $this->Seeds->createSeedReturnToken($options);


        $data = [
            'name' => 'Confirm Email Address',
            'description' => __("Account approved for {0} User.", $user->logger_name),
            'transport' => 'default',
            'profile' => 'default',
            'layout' => 'user_messages',
            'template' => 'user_account_approved',
            'view_vars' => [
                'entities' => [
                    'user' => $user->id,
                ],
                'token' => $token,
                'url' => Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'confirm', $token], true),
            ],
            'email_to' => [$user->email => $user->full_name],
            'email_from' => $this->Settings->getEmailForCakePhpMailer(),
            'subject' => __("{0}: {1} - Your account request has been approved.", APP_NAME, $user->full_name),
        ];
        $Messages = TableRegistry::getTableLocator()->get('messages');
        $Messages->createMessage($data);

    }

    /**
     * Email the given User
     *
     * @param User $user
     */
    public function sendDeniedEmail(User $user)
    {
        /**
         * @var MessagesTable $Messages
         * @var User $user
         */

        //schedule an email

        $data = [
            'name' => 'Confirm Email Address',
            'description' => __("Account denied for {0} User.", $user->logger_name),
            'transport' => 'default',
            'profile' => 'default',
            'layout' => 'user_messages',
            'template' => 'user_account_denied',
            'view_vars' => [
                'entities' => [
                    'user' => $user->id,
                ],
                'url' => Router::url(['prefix' => false, 'controller' => 'pages', 'action' => 'contact'], true),

            ],
            'email_to' => [$user->email => $user->full_name],
            'email_from' => $this->Settings->getEmailForCakePhpMailer(),
            'subject' => __("{0}: {1} - Your account request has been denied.", APP_NAME, $user->full_name),
        ];
        $Messages = TableRegistry::getTableLocator()->get('messages');
        $Messages->createMessage($data);

    }

    /**
     * Send an invitation link to the given User
     *
     * $data format see $this->createNewUserForInvitationOrRequest()
     *
     * @param mixed $data
     * @return bool|User
     */
    public function sendInvitationLink(mixed $data): bool|User
    {
        /**
         * @var MessagesTable $Messages
         * @var User $newUser
         * @var User $currentUser
         */

        $newUser = $this->createNewUserForInvitationOrRequest($data, 'invitation');
        $currentUser = $this->getCurrentAuthenticatedUser();

        if ($newUser && $currentUser) {
            //schedule an email
            $options = [
                'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'confirm', '{token}'],
                'expiration' => new DateTime('+ 1 year'),
                'user_link' => $newUser->id,
            ];
            $token = $this->Seeds->createSeedReturnToken($options);

            $messageData = [
                'name' => 'User Invitation',
                'description' => __("Current User {0} invited new User {1}", $currentUser->logger_name, $newUser->logger_name),
                'transport' => 'default',
                'profile' => 'default',
                'layout' => 'user_messages',
                'template' => 'user_confirm_invitation',
                'view_vars' => [
                    'entities' => [
                        'user' => $newUser->id,
                    ],
                    'token' => $token,
                    'url' => Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'confirm', $token], true),
                ],
                'email_to' => [$newUser->email => $newUser->full_name],
                'email_from' => $this->Settings->getEmailForCakePhpMailer(),
                'subject' => __("{0}: Invitation link for {1}", APP_NAME, $newUser->full_name),
            ];
            $Messages = TableRegistry::getTableLocator()->get('messages');
            $Messages->createMessage($messageData);

            $this->userInvitationData = [
                'id' => $newUser->id,
                'first_name' => $newUser->first_name,
                'last_name' => $newUser->last_name,
                'full_name' => $newUser->full_name,
                'email' => $newUser->email,
                'token' => $messageData['view_vars']['token'],
                'invitation_url' => $messageData['view_vars']['url'],
            ];
        }

        if ($newUser) {
            return $newUser;
        } else {
            return false;
        }

    }

    /**
     * A person is requesting user access to Application - self registration allowed
     *
     * $data format see $this->createNewUserForInvitationOrRequest()
     *
     * @param mixed $data
     * @return bool|User
     */
    public function sendSelfRegistrationLink(mixed $data): bool|User
    {
        /**
         * @var MessagesTable $Messages
         * @var User $newUser
         * @var User $currentUser
         */

        $selfRegistrationMode = $this->Settings->getSetting('self_registration');
        if ($selfRegistrationMode !== 'self') {
            return false;
        }

        $newUser = $this->createNewUserForInvitationOrRequest($data, 'active');
        if (!$newUser) {
            return false;
        }

        //schedule an email
        $options = [
            'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'confirm', '{token}'],
            'expiration' => new DateTime('+ 1 year'),
            'user_link' => $newUser->id,
        ];
        $token = $this->Seeds->createSeedReturnToken($options);

        $messageData = [
            'name' => 'Self Registration',
            'description' => __("New User {0} self registered.", $newUser->logger_name),
            'transport' => 'default',
            'profile' => 'default',
            'layout' => 'user_messages',
            'template' => 'user_confirm_invitation',
            'view_vars' => [
                'entities' => [
                    'user' => $newUser->id,
                ],
                'token' => $token,
                'url' => Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'confirm', $token], true),
            ],
            'email_to' => [$newUser->email => $newUser->full_name],
            'email_from' => $this->Settings->getEmailForCakePhpMailer(),
            'subject' => __("{0}: Invitation link for {1}", APP_NAME, $newUser->full_name),
        ];
        $Messages = TableRegistry::getTableLocator()->get('messages');
        $Messages->createMessage($messageData);

        return $newUser;
    }

    /**
     * A person is requesting user access to Application - Admin must approve.
     *
     * $data format see $this->createNewUserForInvitationOrRequest()
     *
     * @param mixed $data
     * @return bool|User
     */
    public function sendAdminApprovalRegistrationLink(mixed $data): bool|User
    {
        /**
         * @var MessagesTable $Messages
         * @var User $newUser
         * @var User $currentUser
         */

        $selfRegistrationMode = $this->Settings->getSetting('self_registration');
        if ($selfRegistrationMode !== 'admin') {
            return false;
        }

        $newUser = $this->createNewUserForInvitationOrRequest($data, 'pending');
        if (!$newUser) {
            return false;
        }

        //schedule an email
        $options = [
            'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'approve', '{token}'],
            'expiration' => new DateTime('+ 1 year'),
            'user_link' => $newUser->id,
        ];
        $tokenApprove = $this->Seeds->createSeedReturnToken($options);

        $options = [
            'url' => ['prefix' => false, 'controller' => 'UserHub', 'action' => 'deny', '{token}'],
            'expiration' => new DateTime('+ 1 year'),
            'user_link' => $newUser->id,
        ];
        $tokenDeny = $this->Seeds->createSeedReturnToken($options);

        /** @var Query|User[] $allAdmins */
        $allAdmins = $this->findUsersByRoleNameOrRoleAlias(['superadmin', 'admin']);
        foreach ($allAdmins as $admin) {

            $messageData = [
                'name' => 'Admin Approval Registration',
                'description' => __("Admin approval for {0} registration.", $newUser->logger_name),
                'transport' => 'default',
                'profile' => 'default',
                'layout' => 'user_messages',
                'template' => 'user_approve_deny_request',
                'view_vars' => [
                    'administrator' => $admin,
                    'entities' => [
                        'user' => $newUser->id,
                    ],
                    'token_approve' => $tokenApprove,
                    'token_deny' => $tokenDeny,
                    'url_approve' => Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'approve', $tokenApprove], true),
                    'url_deny' => Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'deny', $tokenDeny], true),
                    'url_edit' => Router::url(['prefix' => 'Administrators', 'controller' => 'Users', 'action' => 'edit', $newUser->id], true),
                ],
                'email_to' => [$admin->email => $admin->full_name],
                'email_from' => $this->Settings->getEmailForCakePhpMailer(),
                'subject' => __("{0}: Please approve or deny registration request for {1}", APP_NAME, $newUser->full_name),
            ];
            $Messages = TableRegistry::getTableLocator()->get('messages');
            $Messages->createMessage($messageData);
        }

        return $newUser;
    }

    /**
     * $data = [
     *       "username" => "bob@example.com"
     *       "email" => "bob@example.com"
     *       "first_name" => "Bob"
     *       "last_name" => "Gilson"
     *       "mobile" => "0400 000 000"
     * ];
     * @param $data
     * @param string $userStatus
     * @return User|EntityInterface|false
     */
    private function createNewUserForInvitationOrRequest($data, string $userStatus = 'invitation'): User|EntityInterface|false
    {
        if (!in_array($userStatus, ['invitation', 'pending', 'active'])) {
            $userStatus = 'invitation';
        }

        $newUser = $this->find()
            ->where([
                "OR" => [
                    'email' => $data['email'],
                    'username' => $data['username'],
                ]
            ])
            ->first();


        if (!$newUser) {

            if (!isset($data['roles']['_ids'])) {
                $data['roles']['_ids'] = [
                    $this->Roles->getIdByNameOrAlias('user'),
                ];
            }

            $newUser = $this->newEntity($data);
            //direct assignment to avoid validation
            $newUser->password = sha1(Security::randomBytes(1024));
            $newUser->password_expiry = $this->Settings->getExpiredPasswordExpiryDate();
            $newUser->user_statuses_id = $this->UserStatuses->getIdByNameOrAlias($userStatus);
            $newUser->is_confirmed = false;
            if (!$this->save($newUser)) {
                return false;
            }
        }

        return $newUser;
    }


    /**
     * @param int|User $currentUser
     * @param int|User $otherUser
     * @return bool
     */
    public function canUserManageOtherUser(int|User $currentUser, int|User $otherUser): bool
    {
        $currentUser = $this->asEntityWithRoles($currentUser);
        if (!isset($currentUser->roles[0])) {
            return false;
        }

        $otherUser = $this->asEntityWithRoles($otherUser);
        if (!isset($otherUser->roles[0])) {
            return false;
        }

        $peerRolesForCurrentUser = $this->Roles->getPeerRoles($currentUser->roles[0]->id, false);
        $peerRolesForCurrentUser = array_keys($peerRolesForCurrentUser);

        if (in_array($otherUser->roles[0]->id, $peerRolesForCurrentUser)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $subscribedTo the list/activity they are subscribed to
     * @return Query\SelectQuery
     */
    public function findSubscribers(string $subscribedTo): Query\SelectQuery
    {
        $subscription = $this->Subscriptions->findByNameOrAlias($subscribedTo)->select(['id'], true)->orderBy([], true);
        $userStatus = $this->UserStatuses->findByNameOrAlias('active')->select(['id'], true)->orderBy([], true);

        $now = new DateTime();

        return $this->find('all')
            ->matching('Subscriptions', function ($q) use ($subscription) {
                return $q->where(['Subscriptions.id IN' => $subscription]);
            })
            ->where(['is_confirmed' => true, 'user_statuses_id IN' => $userStatus])
            ->where([
                ['OR' => ['activation <=' => $now->format("Y-m-d H:i:s"), 'activation IS NULL']],
                ['OR' => ['expiration >=' => $now->format("Y-m-d H:i:s"), 'expiration IS NULL']]
            ]);
    }

}
