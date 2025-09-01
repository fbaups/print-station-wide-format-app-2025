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
 * ArtifactMetadata Model
 *
 * @property \App\Model\Table\ArtifactsTable&\Cake\ORM\Association\BelongsTo $Artifacts
 *
 * @method \App\Model\Entity\ArtifactMetadata newEmptyEntity()
 * @method \App\Model\Entity\ArtifactMetadata newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ArtifactMetadata[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ArtifactMetadata get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ArtifactMetadata findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ArtifactMetadata patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ArtifactMetadata[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ArtifactMetadata|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ArtifactMetadata saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ArtifactMetadata[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ArtifactMetadata[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ArtifactMetadata[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ArtifactMetadata[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ArtifactMetadataTable extends AppTable
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

        $this->setTable('artifact_metadata');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Artifacts', [
            'foreignKey' => 'artifact_id',
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
            ->integer('artifact_id')
            ->allowEmptyString('artifact_id');

        $validator
            ->integer('width')
            ->allowEmptyString('width');

        $validator
            ->integer('height')
            ->allowEmptyString('height');

        $validator
            ->scalar('exif')
            ->allowEmptyString('exif');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [
            'exif',
        ];

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
        $rules->add($rules->existsIn('artifact_id', 'Artifacts'), ['errorField' => 'artifact_id']);

        return $rules;
    }
}
