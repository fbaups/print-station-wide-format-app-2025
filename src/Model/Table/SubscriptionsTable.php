<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\Database\Query\SelectQuery;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Subscriptions Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 * @property \App\Model\Table\SubscriptionsTable&\Cake\ORM\Association\BelongsToMany $Subscriptions
 *
 * @method \App\Model\Entity\Subscription newEmptyEntity()
 * @method \App\Model\Entity\Subscription newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Subscription> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Subscription get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Subscription findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Subscription patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Subscription> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Subscription|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Subscription saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Subscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscription>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Subscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscription> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Subscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscription>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Subscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Subscription> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SubscriptionsTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('subscriptions');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Roles', [
            'foreignKey' => 'subscription_id',
            'targetForeignKey' => 'role_id',
            'joinTable' => 'roles_subscriptions',
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'subscription_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'subscriptions_users',
        ]);

        $this->initializeSchemaJsonFields($this->getJsonFields());
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 128)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 512)
            ->allowEmptyString('description');

        $validator
            ->integer('priority')
            ->allowEmptyString('priority');

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
     * @return SelectQuery|Query\SelectQuery
     */
    public function findAllSubscriptions(): SelectQuery|Query\SelectQuery
    {
        $user = $this->getCurrentAuthenticatedUser();
        return $this->find('all');
    }

    /**
     * Find Subscriptions a User can access based on their Roles
     *
     * @param $userId
     * @return SelectQuery|Query\SelectQuery
     */
    public function findMyAllowedSubscriptions(int $userId = null): SelectQuery|Query\SelectQuery
    {
        $currentUserId = $this->getCurrentAuthenticatedUserId();
        if (empty($userId) && empty($currentUserId)) {
            $userId = 0;
        } else {
            $userId = $currentUserId;
        }

        $roles = $this->Users->listOfRolesForUserByIdAndName($userId);

        return $this->find('all')
            ->leftJoin(
                ['RolesSubscriptions' => 'roles_subscriptions'], // Alias and table name
                ['RolesSubscriptions.subscription_id = Subscriptions.id'] // Join condition
            )
            ->where(['RolesSubscriptions.role_id IN' => array_keys($roles)]);
    }

    /**
     * Find Subscriptions a user is subscribed to
     *
     * @param int|null $userId
     * @return SelectQuery|Query\SelectQuery
     */
    public function findmyActiveSubscriptions(int $userId = null): SelectQuery|Query\SelectQuery
    {
        $currentUserId = $this->getCurrentAuthenticatedUserId();
        if (empty($userId) && empty($currentUserId)) {
            $userId = 0;
        } else {
            $userId = $currentUserId;
        }

        return $this->find('all')
            ->leftJoin(
                ['SubscriptionsUsers' => 'subscriptions_users'], // Alias and table name
                ['SubscriptionsUsers.subscription_id = Subscriptions.id'] // Join condition
            )
            ->where(['SubscriptionsUsers.user_id' => $userId]);
    }

    /**
     * Find Subscriptions a user is subscribed to
     *
     * @param int|null $userId
     * @return SelectQuery|Query\SelectQuery
     */
    public function findMyInactiveSubscriptions(int $userId = null): SelectQuery|Query\SelectQuery
    {
        $currentUserId = $this->getCurrentAuthenticatedUserId();
        if (empty($userId) && empty($currentUserId)) {
            $userId = 0;
        } else {
            $userId = $currentUserId;
        }

        $allowedSubscriptions = $this->findMyAllowedSubscriptions($userId)->select(['id'], true);
        $myActiveSubscriptions = $this->findmyActiveSubscriptions($userId)->select(['id'], true);

        return $this->find('all')
            ->where(['id IN' => $allowedSubscriptions])
            ->where(['id NOT IN' => $myActiveSubscriptions]);
    }
}
