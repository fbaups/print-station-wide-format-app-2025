<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ApplicationLogs Model
 *
 * @method \App\Model\Entity\ApplicationLog newEmptyEntity()
 * @method \App\Model\Entity\ApplicationLog newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ApplicationLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ApplicationLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ApplicationLog findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\ApplicationLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ApplicationLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ApplicationLog|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ApplicationLog saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\ApplicationLog[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ApplicationLog[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\ApplicationLog[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\ApplicationLog[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ApplicationLogsTable extends AppTable
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

        $this->setTable('application_logs');
        $this->setDisplayField('id');
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
            ->dateTime('expiration')
            ->requirePresence('expiration', 'create')
            ->notEmptyDateTime('expiration');

        $validator
            ->scalar('level')
            ->maxLength('level', 50)
            ->allowEmptyString('level');

        $validator
            ->integer('user_link')
            ->allowEmptyString('user_link');

        $validator
            ->scalar('url')
            ->maxLength('url', 1024)
            ->allowEmptyString('url');

        $validator
            ->scalar('message')
            ->maxLength('message', 900)
            ->allowEmptyString('message');

        $validator
            ->scalar('message_overflow')
            ->allowEmptyString('message_overflow');

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
     * @param int $limitSeconds
     * @param array|null $limitLevel
     * @return SelectQuery
     */
    public function findReturnAlerts(int $limitSeconds = 60, null|array $limitLevel = null)
    {
        $dt = (new DateTime())->subSeconds($limitSeconds)->format('Y-m-d H:i:s');

        if (empty($limitLevel)) {
            $limitLevel = [
                'warning',
                'success',
                'danger',
                'info',
            ];
        }

        return $this->find('all')
            ->where(["url LIKE '%[return-alert]%'"])
            ->where(["created >=" => $dt])
            ->where(["level IN" => $limitLevel]);
    }
}
