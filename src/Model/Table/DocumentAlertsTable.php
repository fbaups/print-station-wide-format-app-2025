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
 * DocumentAlerts Model
 *
 * @property \App\Model\Table\DocumentsTable&\Cake\ORM\Association\BelongsTo $Documents
 *
 * @method \App\Model\Entity\DocumentAlert newEmptyEntity()
 * @method \App\Model\Entity\DocumentAlert newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\DocumentAlert> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DocumentAlert get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\DocumentAlert findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\DocumentAlert patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\DocumentAlert> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DocumentAlert|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\DocumentAlert saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentAlert>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentAlert> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentAlert>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DocumentAlert>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DocumentAlert> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DocumentAlertsTable extends AppTable
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

        $this->setTable('document_alerts');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Documents', [
            'foreignKey' => 'document_id',
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
            ->scalar('level')
            ->maxLength('level', 10)
            ->allowEmptyString('level');

        $validator
            ->scalar('message')
            ->maxLength('message', 2048)
            ->allowEmptyString('message');

        $validator
            ->integer('code')
            ->allowEmptyString('code');

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

        return $rules;
    }
}
