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
 * OrderStatusMovements Model
 *
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\BelongsTo $Orders
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\OrderStatusMovement newEmptyEntity()
 * @method \App\Model\Entity\OrderStatusMovement newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\OrderStatusMovement> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrderStatusMovement get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\OrderStatusMovement findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\OrderStatusMovement patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\OrderStatusMovement> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrderStatusMovement|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\OrderStatusMovement saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\OrderStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderStatusMovement>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderStatusMovement> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderStatusMovement>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OrderStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OrderStatusMovement> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrderStatusMovementsTable extends AppTable
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

        $this->setTable('order_status_movements');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Orders', [
            'foreignKey' => 'order_id',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->allowEmptyString('order_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->integer('order_status_from')
            ->allowEmptyString('order_status_from');

        $validator
            ->integer('order_status_to')
            ->allowEmptyString('order_status_to');

        $validator
            ->scalar('note')
            ->maxLength('note', 1024)
            ->allowEmptyString('note');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => '1']);

        return $rules;
    }
}
