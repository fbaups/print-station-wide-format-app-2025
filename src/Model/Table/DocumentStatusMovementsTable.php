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
 * DocumentStatusMovements Model
 *
 * @property \App\Model\Table\DocumentsTable&\Cake\ORM\Association\BelongsTo $Documents
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\DocumentStatusMovement newEmptyEntity()
 * @method \App\Model\Entity\DocumentStatusMovement newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\DocumentStatusMovement> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DocumentStatusMovement get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\DocumentStatusMovement findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\DocumentStatusMovement patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\DocumentStatusMovement> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DocumentStatusMovement|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\DocumentStatusMovement saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentStatusMovement>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentStatusMovement> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentStatusMovement>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentStatusMovement>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentStatusMovement> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DocumentStatusMovementsTable extends AppTable
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

        $this->setTable('document_status_movements');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Documents', [
            'foreignKey' => 'document_id',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
            ->allowEmptyString('document_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->integer('document_status_from')
            ->allowEmptyString('document_status_from');

        $validator
            ->integer('document_status_to')
            ->allowEmptyString('document_status_to');

        $validator
            ->scalar('note')
            ->maxLength('note', 1024)
            ->allowEmptyString('note');

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
