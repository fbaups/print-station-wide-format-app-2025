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
 * MessageConnections Model
 *
 * @method \App\Model\Entity\MessageConnection newEmptyEntity()
 * @method \App\Model\Entity\MessageConnection newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MessageConnection[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MessageConnection get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MessageConnection findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\MessageConnection patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MessageConnection[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MessageConnection|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MessageConnection saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MessageConnection[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MessageConnection[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\MessageConnection[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\MessageConnection[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MessageConnectionsTable extends AppTable
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

        $this->setTable('message_connections');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->integer('message_link')
            ->allowEmptyString('message_link');

        $validator
            ->scalar('direction')
            ->maxLength('direction', 10)
            ->allowEmptyString('direction');

        $validator
            ->integer('user_link')
            ->allowEmptyString('user_link');

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
    }}
