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
 * DocumentsUsers Model
 *
 * @property \App\Model\Table\DocumentsTable&\Cake\ORM\Association\BelongsTo $Documents
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\DocumentsUser newEmptyEntity()
 * @method \App\Model\Entity\DocumentsUser newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\DocumentsUser> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DocumentsUser get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\DocumentsUser findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\DocumentsUser patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\DocumentsUser> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DocumentsUser|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\DocumentsUser saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentsUser>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentsUser> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentsUser>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentsUser>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentsUser> deleteManyOrFail(iterable $entities, array $options = [])
 */
class DocumentsUsersTable extends AppTable
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

        $this->setTable('documents_users');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Documents', [
            'foreignKey' => 'document_id',
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
            ->integer('document_id')
            ->notEmptyString('document_id');

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
        $rules->add($rules->existsIn(['document_id'], 'Documents'), ['errorField' => '0']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => '1']);

        return $rules;
    }
}
