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
 * UserLocalizations Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\UserLocalization newEmptyEntity()
 * @method \App\Model\Entity\UserLocalization newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserLocalization[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserLocalization get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserLocalization findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserLocalization patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserLocalization[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserLocalization|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserLocalization saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserLocalization[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLocalization[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLocalization[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserLocalization[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserLocalizationsTable extends AppTable
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

        $this->setTable('user_localizations');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('location')
            ->maxLength('location', 50)
            ->allowEmptyString('location');

        $validator
            ->scalar('locale')
            ->maxLength('locale', 10)
            ->allowEmptyString('locale');

        $validator
            ->scalar('timezone')
            ->maxLength('timezone', 50)
            ->allowEmptyString('timezone');

        $validator
            ->scalar('time_format')
            ->maxLength('time_format', 50)
            ->allowEmptyString('time_format');

        $validator
            ->scalar('date_format')
            ->maxLength('date_format', 50)
            ->allowEmptyString('date_format');

        $validator
            ->scalar('datetime_format')
            ->maxLength('datetime_format', 50)
            ->allowEmptyString('datetime_format');

        $validator
            ->scalar('week_start')
            ->maxLength('week_start', 50)
            ->allowEmptyString('week_start');

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
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
