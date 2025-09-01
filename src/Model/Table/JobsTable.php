<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Artifact;
use App\Model\Entity\Job;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Jobs Model
 *
 * @property \App\Model\Table\OrdersTable&\Cake\ORM\Association\BelongsTo $Orders
 * @property \App\Model\Table\JobStatusesTable&\Cake\ORM\Association\BelongsTo $JobStatuses
 * @property \App\Model\Table\DocumentsTable&\Cake\ORM\Association\HasMany $Documents
 * @property \App\Model\Table\JobAlertsTable&\Cake\ORM\Association\HasMany $JobAlerts
 * @property \App\Model\Table\JobPropertiesTable&\Cake\ORM\Association\HasMany $JobProperties
 * @property \App\Model\Table\JobStatusMovementsTable&\Cake\ORM\Association\HasMany $JobStatusMovements
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method \App\Model\Entity\Job newEmptyEntity()
 * @method \App\Model\Entity\Job newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Job> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Job get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Job findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Job patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Job> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Job|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Job saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class JobsTable extends AppTable
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

        $this->setTable('jobs');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Orders', [
            'foreignKey' => 'order_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('JobStatuses', [
            'foreignKey' => 'job_status_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Documents', [
            'foreignKey' => 'job_id',
        ]);
        $this->hasMany('JobAlerts', [
            'foreignKey' => 'job_id',
        ]);
        $this->hasMany('JobProperties', [
            'foreignKey' => 'job_id',
        ]);
        $this->hasMany('JobStatusMovements', [
            'foreignKey' => 'job_id',
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'job_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'jobs_users',
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
            ->scalar('guid')
            ->maxLength('guid', 50)
            ->allowEmptyString('guid');

        $validator
            ->integer('order_id')
            ->notEmptyString('order_id');

        $validator
            ->integer('job_status_id')
            ->notEmptyString('job_status_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 1024)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmptyString('quantity');

        $validator
            ->scalar('external_job_number')
            ->maxLength('external_job_number', 50)
            ->allowEmptyString('external_job_number');

        $validator
            ->dateTime('external_creation_date')
            ->allowEmptyDateTime('external_creation_date');

        $validator
            ->integer('priority')
            ->allowEmptyString('priority');

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
        $rules->add($rules->existsIn(['job_status_id'], 'JobStatuses'), ['errorField' => '1']);

        return $rules;
    }

    public function create(array $data): false|Job
    {
        $defaultData = [
            'quantity' => 1,
        ];

        $data = array_merge($defaultData, $data);

        if (empty($data['hash_sum'])) {
            $hashEntries = [
                'name' => true,
                'description' => true,
                'quantity' => true,
                'external_job_number' => true,
                'external_creation_date' => true,
                'payload' => true,
            ];
            $dataForHashing = array_intersect_key($data, $hashEntries);
            $data['hash_sum'] = sha1(serialize($dataForHashing));
        }

        $isPresent = $this->findByHashSum($data['hash_sum'])->first();
        if ($isPresent) {
            $this->addDangerAlerts(__("Job {0} already exists in the Database.", $data['name']));
            return false;
        }

        $ent = $this->newEntity($data);

        $saveResult = $this->save($ent);
        if ($saveResult) {
            $this->addSuccessAlerts(__("Created Job ID:{0}", $saveResult->id));
        } else {
            $this->addDangerAlerts(__("Failed to save the Job."));
        }

        return $saveResult;
    }

    /**
     * @param int|Job $idOrEntity
     * @param bool $validated
     * @return Artifact[]
     */
    public function getArtifacts(int|Job $idOrEntity, bool $validated = true): array
    {
        if (is_int($idOrEntity)) {
            $id = $idOrEntity;
        } else {
            $id = $idOrEntity->id;
        }

        /** @var Job $job */
        $job = $this->find('all')
            ->where(['id' => $id])
            ->contain([
                'Documents' => [
                    'sort' => ['Documents.id' => 'ASC']
                ]
            ])
            ->first();

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        $artifacts = [];
        foreach ($job->documents as $document) {
            $artifactToken = $document->artifact_token;
            if ($artifactToken) {
                $artifacts[$artifactToken] = null; //populated later
            }
        }

        $tokens = array_keys($artifacts);

        /** @var Artifact[] $artifactEntities */
        $artifactEntities = $Artifacts->findByTokens($tokens);

        foreach ($artifactEntities as $artifactEntity) {
            if ($validated && is_file($artifactEntity->full_unc)) {
                $artifacts[$artifactEntity->token] = $artifactEntity;
            } else {
                $artifacts[$artifactEntity->token] = $artifactEntity;
            }
        }

        return $artifacts;
    }
}
