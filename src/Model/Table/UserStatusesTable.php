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
 * UserStatuses Model
 *
 * @method \App\Model\Entity\UserStatus newEmptyEntity()
 * @method \App\Model\Entity\UserStatus newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\UserStatus[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\UserStatus get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\UserStatus findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\UserStatus patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\UserStatus[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\UserStatus|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserStatus saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\UserStatus[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserStatus[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserStatus[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\UserStatus[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UserStatusesTable extends AppTable
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

        $this->setTable('user_statuses');
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
            ->integer('rank')
            ->allowEmptyString('rank');

        $validator
            ->scalar('name')
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->scalar('alias')
            ->maxLength('alias', 50)
            ->allowEmptyString('alias');

        $validator
            ->scalar('name_status_icon')
            ->maxLength('name_status_icon', 50)
            ->allowEmptyString('name_status_icon');

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

    public function getActiveStatusIds(): array
    {
        $activeIds = $this->findActiveStatusIds();
        $results = $this->find('list', keyField: 'id', valueField: 'id')
            ->where(['id IN' => $activeIds])
            ->toArray();

        return $results;
    }

    public function getInactiveStatusIds(): array
    {
        $activeIds = $this->findActiveStatusIds();
        $results = $this->find('list', keyField: 'id', valueField: 'id')
            ->where(['id NOT IN' => $activeIds])
            ->toArray();

        return $results;
    }

    public function getActiveStatusList(): array
    {
        $activeIds = $this->findActiveStatusIds();
        $results = $this->find('list', keyField: 'id', valueField: 'alias')
            ->where(['id IN' => $activeIds])
            ->toArray();

        return $results;
    }

    public function getInactiveStatusList(): array
    {
        $activeIds = $this->findActiveStatusIds();
        $results = $this->find('list', keyField: 'id', valueField: 'alias')
            ->where(['id NOT IN' => $activeIds])
            ->toArray();

        return $results;
    }

    private function findActiveStatusIds(): Query
    {
        $activeWords = ['active', 'approved'];
        return $this->find('all')
            ->select('id', true)
            ->where(['OR' => ['name IN' => $activeWords, 'alias IN' => $activeWords]]);
    }
}
