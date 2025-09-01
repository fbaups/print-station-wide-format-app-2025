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
 * JobStatuses Model
 *
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\HasMany $Jobs
 *
 * @method \App\Model\Entity\JobStatus newEmptyEntity()
 * @method \App\Model\Entity\JobStatus newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\JobStatus> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\JobStatus get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\JobStatus findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\JobStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\JobStatus> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\JobStatus|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\JobStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatus>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatus> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatus>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\JobStatus>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\JobStatus> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class JobStatusesTable extends AppTable
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

        $this->setTable('job_statuses');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Jobs', [
            'foreignKey' => 'job_status_id',
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
            ->integer('sort')
            ->allowEmptyString('sort');

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->scalar('allow_from_status')
            ->maxLength('allow_from_status', 50)
            ->allowEmptyString('allow_from_status');

        $validator
            ->scalar('allow_to_status')
            ->maxLength('allow_to_status', 50)
            ->allowEmptyString('allow_to_status');

        $validator
            ->scalar('icon')
            ->maxLength('icon', 50)
            ->allowEmptyString('icon');

        $validator
            ->scalar('hex_code')
            ->maxLength('hex_code', 10)
            ->allowEmptyString('hex_code');

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
