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
 * JobProperties Model
 *
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\BelongsTo $Jobs
 *
 * @method \App\Model\Entity\JobProperty newEmptyEntity()
 * @method \App\Model\Entity\JobProperty newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\JobProperty> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\JobProperty get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\JobProperty findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\JobProperty patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\JobProperty> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\JobProperty|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\JobProperty saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\JobProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobProperty>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobProperty> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobProperty>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobProperty>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobProperty> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class JobPropertiesTable extends AppTable
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

        $this->setTable('job_properties');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
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
            ->integer('job_id')
            ->notEmptyString('job_id');

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
        $rules->add($rules->existsIn(['job_id'], 'Jobs'), ['errorField' => '0']);

        return $rules;
    }
}
