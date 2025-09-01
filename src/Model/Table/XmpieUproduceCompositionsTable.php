<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\XmpieUproduceComposition;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use Closure;
use Psr\SimpleCache\CacheInterface;

/**
 * XmpieUproduceCompositions Model
 *
 * @property XmpieUproduceCompositionJobsTable&HasMany $XmpieUproduceCompositionJobs
 *
 * @method XmpieUproduceComposition newEmptyEntity()
 * @method XmpieUproduceComposition newEntity(array $data, array $options = [])
 * @method array<XmpieUproduceComposition> newEntities(array $data, array $options = [])
 * @method XmpieUproduceComposition get(mixed $primaryKey, array|string $finder = 'all', CacheInterface|string|null $cache = null, Closure|string|null $cacheKey = null, mixed ...$args)
 * @method XmpieUproduceComposition findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method XmpieUproduceComposition patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method array<XmpieUproduceComposition> patchEntities(iterable $entities, array $data, array $options = [])
 * @method XmpieUproduceComposition|false save(EntityInterface $entity, array $options = [])
 * @method XmpieUproduceComposition saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<XmpieUproduceComposition>|ResultSetInterface<XmpieUproduceComposition>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceComposition>|ResultSetInterface<XmpieUproduceComposition> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceComposition>|ResultSetInterface<XmpieUproduceComposition>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<XmpieUproduceComposition>|ResultSetInterface<XmpieUproduceComposition> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin TimestampBehavior
 */
class XmpieUproduceCompositionsTable extends AppTable
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

        $this->setTable('xmpie_uproduce_compositions');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('XmpieUproduceCompositionJobs', [
            'foreignKey' => 'xmpie_uproduce_composition_id',
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
            ->scalar('guid')
            ->maxLength('guid', 50)
            ->allowEmptyString('guid');

        $validator
            ->dateTime('activation')
            ->allowEmptyDateTime('activation');

        $validator
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->boolean('auto_delete')
            ->allowEmptyString('auto_delete');

        $validator
            ->scalar('name')
            ->maxLength('name', 256)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->integer('errand_link')
            ->allowEmptyString('errand_link');

        $validator
            ->integer('artifact_link')
            ->allowEmptyString('artifact_link');

        $validator
            ->integer('integration_credential_link')
            ->allowEmptyString('integration_credential_link');

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
     * Overwrite the delete method to include cascade deletion
     *
     * @param EntityInterface|XmpieUproduceComposition $entity
     * @param array $options
     * @return bool
     */
    public function delete(EntityInterface $entity, array $options = []): bool
    {
        $deleteResult = parent::delete($entity, $options);


        $artifactLink = $entity->artifact_link;
        if ($artifactLink) {
            /** @var ArtifactsTable $Artifacts */
            $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
            $Artifacts->deleteAll(['id' => $artifactLink]);
        }

        $errandLink = $entity->errand_link;
        if ($errandLink) {
            /** @var ErrandsTable $Artifacts */
            $Errands = TableRegistry::getTableLocator()->get('Errands');
            $Errands->deleteAll(['id' => $errandLink]);
        }
        return $deleteResult;
    }
}
