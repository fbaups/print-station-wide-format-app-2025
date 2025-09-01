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
 * JobsUsers Model
 *
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\BelongsTo $Jobs
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\JobsUser newEmptyEntity()
 * @method \App\Model\Entity\JobsUser newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\JobsUser> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\JobsUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\JobsUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\JobsUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\JobsUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\JobsUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\JobsUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\JobsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobsUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobsUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobsUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobsUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class JobsUsersTable extends AppTable
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

        $this->setTable('jobs_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

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
