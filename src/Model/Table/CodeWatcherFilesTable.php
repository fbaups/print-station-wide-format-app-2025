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
 * CodeWatcherFiles Model
 *
 * @property \App\Model\Table\CodeWatcherFoldersTable&\Cake\ORM\Association\BelongsTo $CodeWatcherFolders
 *
 * @method \App\Model\Entity\CodeWatcherFile newEmptyEntity()
 * @method \App\Model\Entity\CodeWatcherFile newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CodeWatcherFile> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFile get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CodeWatcherFile findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFile patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CodeWatcherFile> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFile|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CodeWatcherFile saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFile>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFile> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFile>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherFile>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherFile> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CodeWatcherFilesTable extends AppTable
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

        $this->setTable('code_watcher_files');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('CodeWatcherFolders', [
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
            ->integer('code_watcher_folder_id')
            ->allowEmptyString('code_watcher_folder_id');

        $validator
            ->scalar('local_timezone')
            ->maxLength('local_timezone', 50)
            ->allowEmptyString('local_timezone');

        $validator
            ->integer('local_year')
            ->allowEmptyString('local_year');

        $validator
            ->integer('local_month')
            ->allowEmptyString('local_month');

        $validator
            ->integer('local_day')
            ->allowEmptyString('local_day');

        $validator
            ->integer('local_hour')
            ->allowEmptyString('local_hour');

        $validator
            ->integer('local_minute')
            ->allowEmptyString('local_minute');

        $validator
            ->integer('local_second')
            ->allowEmptyString('local_second');

        $validator
            ->scalar('time_grouping_key')
            ->maxLength('time_grouping_key', 20)
            ->allowEmptyString('time_grouping_key');

        $validator
            ->scalar('path_checksum')
            ->maxLength('path_checksum', 50)
            ->allowEmptyString('path_checksum');

        $validator
            ->scalar('base_path')
            ->maxLength('base_path', 255)
            ->allowEmptyString('base_path');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 255)
            ->allowEmptyString('file_path');

        $validator
            ->scalar('sha1')
            ->maxLength('sha1', 50)
            ->allowEmptyString('sha1');

        $validator
            ->scalar('crc32')
            ->maxLength('crc32', 20)
            ->allowEmptyString('crc32');

        $validator
            ->scalar('mime')
            ->maxLength('mime', 100)
            ->allowEmptyString('mime');

        $validator
            ->integer('size')
            ->allowEmptyString('size');

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
        $rules->add($rules->existsIn(['code_watcher_folder_id'], 'CodeWatcherFolders'), ['errorField' => '0']);

        return $rules;
    }
}
