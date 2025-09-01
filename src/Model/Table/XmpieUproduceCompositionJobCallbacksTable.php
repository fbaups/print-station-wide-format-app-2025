<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\XmpieUproduceCompositionJobCallback;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Closure;
use Psr\SimpleCache\CacheInterface;

/**
 * XmpieUproduceCompositionJobCallbacks Model
 *
 * @property XmpieUproduceCompositionJobsTable&BelongsTo $XmpieUproduceCompositionJobs
 *
 * @method XmpieUproduceCompositionJobCallback newEmptyEntity()
 * @method XmpieUproduceCompositionJobCallback newEntity(array $data, array $options = [])
 * @method array<XmpieUproduceCompositionJobCallback> newEntities(array $data, array $options = [])
 * @method XmpieUproduceCompositionJobCallback get(mixed $primaryKey, array|string $finder = 'all', CacheInterface|string|null $cache = null, Closure|string|null $cacheKey = null, mixed ...$args)
 * @method XmpieUproduceCompositionJobCallback findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method XmpieUproduceCompositionJobCallback patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method array<XmpieUproduceCompositionJobCallback> patchEntities(iterable $entities, array $data, array $options = [])
 * @method XmpieUproduceCompositionJobCallback|false save(EntityInterface $entity, array $options = [])
 * @method XmpieUproduceCompositionJobCallback saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<XmpieUproduceCompositionJobCallback>|ResultSetInterface<XmpieUproduceCompositionJobCallback>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceCompositionJobCallback>|ResultSetInterface<XmpieUproduceCompositionJobCallback> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceCompositionJobCallback>|ResultSetInterface<XmpieUproduceCompositionJobCallback>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceCompositionJobCallback>|ResultSetInterface<XmpieUproduceCompositionJobCallback> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin TimestampBehavior
 */
class XmpieUproduceCompositionJobCallbacksTable extends AppTable
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

        $this->setTable('xmpie_uproduce_composition_job_callbacks');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('XmpieUproduceCompositionJobs', [
            'foreignKey' => 'xmpie_uproduce_composition_job_id',
            'joinType' => 'INNER',
        ]);

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
            ->integer('xmpie_uproduce_composition_job_id')
            ->notEmptyString('xmpie_uproduce_composition_job_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 256)
            ->allowEmptyString('name');

        $validator
            ->integer('job_number')
            ->allowEmptyString('job_number');

        $validator
            ->scalar('job_guid')
            ->maxLength('job_guid', 50)
            ->allowEmptyString('job_guid');

        $validator
            ->scalar('status')
            ->maxLength('status', 50)
            ->allowEmptyString('status');

        $validator
            ->dateTime('start')
            ->allowEmptyDateTime('start');

        $validator
            ->dateTime('end')
            ->allowEmptyDateTime('end');

        $validator
            ->integer('process_count')
            ->allowEmptyString('process_count');

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
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['xmpie_uproduce_composition_job_id'], 'XmpieUproduceCompositionJobs'), ['errorField' => '0']);

        return $rules;
    }
}
