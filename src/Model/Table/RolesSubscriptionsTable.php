<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * RolesSubscriptions Model
 *
 * @property \App\Model\Table\SubscriptionsTable&\Cake\ORM\Association\BelongsTo $Subscriptions
 * @property \App\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 *
 * @method \App\Model\Entity\RolesSubscription newEmptyEntity()
 * @method \App\Model\Entity\RolesSubscription newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\RolesSubscription> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RolesSubscription get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\RolesSubscription findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\RolesSubscription patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\RolesSubscription> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RolesSubscription|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\RolesSubscription saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\RolesSubscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RolesSubscription>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RolesSubscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RolesSubscription> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RolesSubscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RolesSubscription>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\RolesSubscription>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\RolesSubscription> deleteManyOrFail(iterable $entities, array $options = [])
 */
class RolesSubscriptionsTable extends AppTable
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

        $this->setTable('roles_subscriptions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Subscriptions', [
            'foreignKey' => 'subscription_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
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
            ->integer('subscription_id')
            ->notEmptyString('subscription_id');

        $validator
            ->integer('role_id')
            ->notEmptyString('role_id');

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
        $rules->add($rules->existsIn(['subscription_id'], 'Subscriptions'), ['errorField' => '0']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => '1']);

        return $rules;
    }
}
