<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Artifact;
use App\Model\Entity\MediaClip;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * MediaClips Model
 *
 * @method \App\Model\Entity\MediaClip newEmptyEntity()
 * @method \App\Model\Entity\MediaClip newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MediaClip> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MediaClip get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MediaClip findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MediaClip patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MediaClip> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MediaClip|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MediaClip saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MediaClip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MediaClip>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MediaClip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MediaClip> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MediaClip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MediaClip>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MediaClip>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MediaClip> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MediaClipsTable extends AppTable
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

        $this->setTable('media_clips');
        $this->setDisplayField('name');
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->integer('rank')
            ->allowEmptyString('rank');

        $validator
            ->integer('artifact_link')
            ->allowEmptyString('artifact_link');

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
            ->numeric('trim_start')
            ->allowEmptyString('trim_start');

        $validator
            ->numeric('trim_end')
            ->allowEmptyString('trim_end');

        $validator
            ->numeric('duration')
            ->allowEmptyString('duration');

        $validator
            ->scalar('fitting')
            ->maxLength('fitting', 16)
            ->allowEmptyString('fitting');

        $validator
            ->boolean('muted')
            ->allowEmptyString('muted');

        $validator
            ->boolean('loop')
            ->allowEmptyString('loop');

        $validator
            ->boolean('autoplay')
            ->allowEmptyString('autoplay');

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
     * Get the Artifact behind the Media Clip
     *
     * @param int|MediaClip $idOrEntity
     * @return false|Artifact
     */
    public function getArtifactBehindMediaClip(int|MediaClip $idOrEntity): false|Artifact
    {
        /** @var MediaClip $mediaClip */
        $mediaClip = $this->asEntity($idOrEntity);

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        /** @var Artifact $artifact */
        $artifact = $Artifacts->find('all')
            ->where(['Artifacts.id' => $mediaClip->artifact_link])
            ->contain(['ArtifactMetadata'])
            ->first();

        return $artifact ?? false;
    }

}
