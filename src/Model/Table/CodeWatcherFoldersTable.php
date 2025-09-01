<?php
declare(strict_types=1);

namespace App\Model\Table;

use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CodeWatcherFolders Model
 *
 * @property \App\Model\Table\CodeWatcherProjectsTable&\Cake\ORM\Association\BelongsTo $CodeWatcherProjects
 * @property \App\Model\Table\CodeWatcherFilesTable&\Cake\ORM\Association\HasMany $CodeWatcherFiles
 *
 * @method \App\Model\Entity\CodeWatcherFolder newEmptyEntity()
 * @method \App\Model\Entity\CodeWatcherFolder newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CodeWatcherFolder> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFolder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CodeWatcherFolder findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFolder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CodeWatcherFolder> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFolder|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFolder saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFolder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFolder>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFolder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFolder> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFolder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFolder>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFolder>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFolder> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CodeWatcherFoldersTable extends AppTable
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

        $this->setTable('code_watcher_folders');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('CodeWatcherProjects', [
            'foreignKey' => 'code_watcher_project_id',
        ]);
        $this->hasMany('CodeWatcherFiles', [
            'foreignKey' => 'code_watcher_folder_id',
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
            ->integer('code_watcher_project_id')
            ->allowEmptyString('code_watcher_project_id');

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
            ->scalar('base_path')
            ->maxLength('base_path', 850)
            ->allowEmptyString('base_path');

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
        $rules->add($rules->existsIn(['code_watcher_project_id'], 'CodeWatcherProjects'), ['errorField' => '0']);

        return $rules;
    }

    /**
     * Sanitises the array of entries with no base paths.
     * Deletes the entity if it exists and has no base path
     *
     * @param array $codeWatcherFolders
     * @return array
     */
    public function sanitiseFormData(array $codeWatcherFolders): array
    {
        $sanitised = [];
        foreach ($codeWatcherFolders as $codeWatcherFolder) {
            if (isset($codeWatcherFolder['id'])) {
                if (is_numeric($codeWatcherFolder['id']) && !empty($codeWatcherFolder['base_path'])) {
                    $codeWatcherFolder['base_path'] = TextFormatter::makeDirectoryTrailingSmartSlash($codeWatcherFolder['base_path']);
                    $sanitised[] = $codeWatcherFolder;
                    continue;
                } else {
                    $toDelete = $this->asEntity($codeWatcherFolder['id']);
                    $this->delete($toDelete);
                }
            }

            if (isset($codeWatcherFolder['base_path'])) {
                if (!empty($codeWatcherFolder['base_path'])) {
                    $codeWatcherFolder['base_path'] = TextFormatter::makeDirectoryTrailingSmartSlash($codeWatcherFolder['base_path']);
                    $sanitised[] = $codeWatcherFolder;
                }
            }
        }

        return $sanitised;
    }
}
