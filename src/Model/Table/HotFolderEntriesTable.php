<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\HotFolderEntry;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * HotFolderEntries Model
 *
 * @property \App\Model\Table\HotFoldersTable&\Cake\ORM\Association\BelongsTo $HotFolders
 *
 * @method \App\Model\Entity\HotFolderEntry newEmptyEntity()
 * @method \App\Model\Entity\HotFolderEntry newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\HotFolderEntry> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\HotFolderEntry get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\HotFolderEntry findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\HotFolderEntry patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\HotFolderEntry> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\HotFolderEntry|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\HotFolderEntry saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\HotFolderEntry>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\HotFolderEntry>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\HotFolderEntry>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\HotFolderEntry> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\HotFolderEntry>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\HotFolderEntry>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\HotFolderEntry>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\HotFolderEntry> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class HotFolderEntriesTable extends AppTable
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

        $this->setTable('hot_folder_entries');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('HotFolders', [
            'foreignKey' => 'hot_folder_id',
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
            ->integer('hot_folder_id')
            ->notEmptyString('hot_folder_id');

        $validator
            ->scalar('path')
            ->maxLength('path', 512)
            ->allowEmptyString('path');

        $validator
            ->scalar('path_hash_sum')
            ->maxLength('path_hash_sum', 50)
            ->allowEmptyString('path_hash_sum');

        $validator
            ->scalar('listing_hash_sum')
            ->maxLength('listing_hash_sum', 50)
            ->allowEmptyString('listing_hash_sum');

        $validator
            ->scalar('contents_hash_sum')
            ->maxLength('contents_hash_sum', 50)
            ->allowEmptyString('contents_hash_sum');

        $validator
            ->dateTime('last_check_time')
            ->allowEmptyDateTime('last_check_time');

        $validator
            ->dateTime('next_check_time')
            ->allowEmptyDateTime('next_check_time');

        $validator
            ->integer('lock_code')
            ->allowEmptyString('lock_code');

        $validator
            ->integer('errand_link')
            ->allowEmptyString('errand_link');

        $validator
            ->scalar('status')
            ->maxLength('status', 10)
            ->allowEmptyString('status');

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
        $rules->add($rules->existsIn(['hot_folder_id'], 'HotFolders'), ['errorField' => '0']);

        return $rules;
    }


    /**
     * @param HotFolderEntry $hotFolderEntry
     * @return false|HotFolderEntry
     */
    public function flagEntryStatusAsInfo(HotFolderEntry $hotFolderEntry): false|HotFolderEntry
    {
        $hotFolderEntry->status = 'info';
        return $this->save($hotFolderEntry);
    }


    /**
     * @param HotFolderEntry $hotFolderEntry
     * @return false|HotFolderEntry
     */
    public function flagEntryStatusAsSuccess(HotFolderEntry $hotFolderEntry): false|HotFolderEntry
    {
        $hotFolderEntry->status = 'success';
        return $this->save($hotFolderEntry);
    }


    /**
     * @param HotFolderEntry $hotFolderEntry
     * @return false|HotFolderEntry
     */
    public function flagEntryStatusAsWarning(HotFolderEntry $hotFolderEntry): false|HotFolderEntry
    {
        $hotFolderEntry->status = 'warning';
        return $this->save($hotFolderEntry);
    }


    /**
     * @param HotFolderEntry $hotFolderEntry
     * @return false|HotFolderEntry
     */
    public function flagEntryStatusAsDanger(HotFolderEntry $hotFolderEntry): false|HotFolderEntry
    {
        $hotFolderEntry->status = 'danger';
        return $this->save($hotFolderEntry);
    }

    /**
     * Wrapper function
     */
    public function clearInfoEntries(int $hotFolderId): int
    {
        return $this->_clearEntriesByType($hotFolderId, ['info']);
    }

    /**
     * Wrapper function
     */
    public function clearSuccessEntries(int $hotFolderId): int
    {
        return $this->_clearEntriesByType($hotFolderId, ['success']);
    }

    /**
     * Wrapper function
     */
    public function clearWarningEntries(int $hotFolderId): int
    {
        return $this->_clearEntriesByType($hotFolderId, ['warning']);
    }

    /**
     * Wrapper function
     */
    public function clearDangerEntries(int $hotFolderId): int
    {
        return $this->_clearEntriesByType($hotFolderId, ['danger']);
    }

    /**
     * Wrapper function
     */
    public function clearAllEntries(int $hotFolderId): int
    {
        return $this->_clearEntriesByType($hotFolderId, ['info', 'success', 'warning', 'danger']);
    }

    /**
     * Clear info|success|warning|danger alerts
     * @param int $hotFolderId
     * @param string $type
     * @return int
     */
    private function _clearEntriesByType(int $hotFolderId, array $types): int
    {
        /** @var ErrandsTable $ErrandsTable */
        $ErrandsTable = TableRegistry::getTableLocator()->get('Errands');
        $queryCompletedErrands = $ErrandsTable->find('all')->select(['id'], true)->where(['started IS NOT NULL', 'completed IS NOT NULL']);

        $options = [
            'errand_link IN' => $queryCompletedErrands,
            'status IN' => $types,
            'hot_folder_id IN' => $hotFolderId,
        ];

        return $this->deleteAll($options);
    }


    public function clearOrphaned(int $hotFolderId): int
    {
        /** @var ErrandsTable $ErrandsTable */
        $ErrandsTable = TableRegistry::getTableLocator()->get('Errands');
        $queryAllErrands = $ErrandsTable->find('all')->select(['id'], true);

        $options = [
            'errand_link NOT IN' => $queryAllErrands,
            'hot_folder_id IN' => $hotFolderId,
        ];

        return $this->deleteAll($options);
    }

}
