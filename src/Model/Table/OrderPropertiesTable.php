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
 * OrderProperties Model
 *
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\BelongsTo $Orders
 *
 * @method \App\Model\Entity\OrderProperty newEmptyEntity()
 * @method \App\Model\Entity\OrderProperty newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\OrderProperty> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrderProperty get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\OrderProperty findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\OrderProperty patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\OrderProperty> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrderProperty|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\OrderProperty saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\OrderProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderProperty>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderProperty> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderProperty>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderProperty> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrderPropertiesTable extends AppTable
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

        $this->setTable('order_properties');
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
            ->scalar('meta_data')
            ->allowEmptyString('meta_data');

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
