<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\XmpieUproduceCompositionJob;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Closure;
use Psr\SimpleCache\CacheInterface;

/**
 * XmpieUproduceCompositionJobs Model
 *
 * @property XmpieUproduceCompositionsTable&BelongsTo $XmpieUproduceCompositions
 * @property XmpieUproduceCompositionJobCallbacksTable&HasMany $XmpieUproduceCompositionJobCallbacks
 *
 * @method XmpieUproduceCompositionJob newEmptyEntity()
 * @method XmpieUproduceCompositionJob newEntity(array $data, array $options = [])
 * @method array<XmpieUproduceCompositionJob> newEntities(array $data, array $options = [])
 * @method XmpieUproduceCompositionJob get(mixed $primaryKey, array|string $finder = 'all', CacheInterface|string|null $cache = null, Closure|string|null $cacheKey = null, mixed ...$args)
 * @method XmpieUproduceCompositionJob findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method XmpieUproduceCompositionJob patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method array<XmpieUproduceCompositionJob> patchEntities(iterable $entities, array $data, array $options = [])
 * @method XmpieUproduceCompositionJob|false save(EntityInterface $entity, array $options = [])
 * @method XmpieUproduceCompositionJob saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<XmpieUproduceCompositionJob>|ResultSetInterface<XmpieUproduceCompositionJob>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceCompositionJob>|ResultSetInterface<XmpieUproduceCompositionJob> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceCompositionJob>|ResultSetInterface<XmpieUproduceCompositionJob>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceCompositionJob>|ResultSetInterface<XmpieUproduceCompositionJob> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin TimestampBehavior
 */
class XmpieUproduceCompositionJobsTable extends AppTable
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

        $this->setTable('xmpie_uproduce_composition_jobs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('XmpieUproduceCompositions', [
            'foreignKey' => 'xmpie_uproduce_composition_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('XmpieUproduceCompositionJobCallbacks', [
            'foreignKey' => 'xmpie_uproduce_composition_job_id',
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
            ->integer('xmpie_uproduce_composition_id')
            ->notEmptyString('xmpie_uproduce_composition_id');

        $validator
            ->integer('job_number')
            ->allowEmptyString('job_number');

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
        $rules->add($rules->existsIn(['xmpie_uproduce_composition_id'], 'XmpieUproduceCompositions'), ['errorField' => '0']);

        return $rules;
    }
}
