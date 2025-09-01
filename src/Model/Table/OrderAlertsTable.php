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
 * OrderAlerts Model
 *
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\BelongsTo $Orders
 *
 * @method \App\Model\Entity\OrderAlert newEmptyEntity()
 * @method \App\Model\Entity\OrderAlert newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\OrderAlert> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrderAlert get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\OrderAlert findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\OrderAlert patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\OrderAlert> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrderAlert|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\OrderAlert saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\OrderAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderAlert>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderAlert> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderAlert>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderAlert> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrderAlertsTable extends AppTable
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

        $this->setTable('order_alerts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Orders', [
            'foreignKey' => 'order_id',
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
            ->integer('order_id')
            ->notEmptyString('order_id');

        $validator
            ->scalar('level')
            ->maxLength('level', 10)
            ->allowEmptyString('level');

        $validator
            ->scalar('message')
            ->maxLength('message', 2048)
            ->allowEmptyString('message');

        $validator
            ->integer('code')
            ->allowEmptyString('code');

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
        $rules->add($rules->existsIn(['order_id'], 'Orders'), ['errorField' => '0']);

        return $rules;
    }
}
