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
 * JobStatusMovements Model
 *
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\BelongsTo $Jobs
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\JobStatusMovement newEmptyEntity()
 * @method \App\Model\Entity\JobStatusMovement newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\JobStatusMovement> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\JobStatusMovement get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\JobStatusMovement findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\JobStatusMovement patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\JobStatusMovement> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\JobStatusMovement|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\JobStatusMovement saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatusMovement>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatusMovement> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatusMovement>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatusMovement> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class JobStatusMovementsTable extends AppTable
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

        $this->setTable('job_status_movements');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
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
            ->integer('job_id')
            ->allowEmptyString('job_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->integer('job_status_from')
            ->allowEmptyString('job_status_from');

        $validator
            ->integer('job_status_to')
            ->allowEmptyString('job_status_to');

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
        $rules->add($rules->existsIn(['job_id'], 'Jobs'), ['errorField' => '0']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => '1']);

        return $rules;
    }
}
