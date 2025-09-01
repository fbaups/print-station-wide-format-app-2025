<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Heartbeat;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * Heartbeats Model
 *
 * @method Heartbeat newEmptyEntity()
 * @method Heartbeat newEntity(array $data, array $options = [])
 * @method Heartbeat[] newEntities(array $data, array $options = [])
 * @method Heartbeat get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method Heartbeat findOrCreate($search, ?callable $callback = null, $options = [])
 * @method Heartbeat patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Heartbeat[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method Heartbeat|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Heartbeat saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Heartbeat[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method Heartbeat[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method Heartbeat[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method Heartbeat[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class HeartbeatsTable extends AppTable
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

        $this->setTable('heartbeats');
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
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->boolean('auto_delete')
            ->allowEmptyString('auto_delete');

        $validator
            ->scalar('server')
            ->maxLength('server', 128)
            ->allowEmptyString('server');

        $validator
            ->scalar('domain')
            ->maxLength('domain', 128)
            ->allowEmptyString('domain');

        $validator
            ->scalar('type')
            ->maxLength('type', 128)
            ->allowEmptyString('type');

        $validator
            ->scalar('context')
            ->maxLength('context', 128)
            ->allowEmptyString('context');

        $validator
            ->integer('pid')
            ->allowEmptyString('pid');

        $validator
            ->scalar('name')
            ->maxLength('name', 128)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 850)
            ->allowEmptyString('description');

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
     * Wrapper function
     *
     * @param array $options
     * @return Heartbeat|false
     */
    public function createHeartbeat(array $options): Heartbeat|bool
    {
        $options['type'] = 'heartbeat';
        return $this->_createEntry($options);
    }

    /**
     * Wrapper function
     *
     * @param array $options
     * @return Heartbeat|false
     */
    public function createPulse(array $options): Heartbeat|bool
    {
        $options['type'] = 'pulse';
        return $this->_createEntry($options);
    }

    /**
     * Create a heartbeat or pulse.
     *
     * @param array $options
     * @return Heartbeat|false
     */
    private function _createEntry(array $options): Heartbeat|bool
    {
        $expiration = new DateTime('+ 24 hours');

        $defaultOptions = [
            'expiration' => $expiration,
            'auto_delete' => true,
            'server' => gethostname(),
            'pid' => getmypid(),
            'type' => 'heartbeat',
            'context' => '',
            'domain' => parse_url(Router::url("/", true), PHP_URL_HOST),
            'name' => '',
            'description' => '',
        ];

        $options = array_merge($defaultOptions, $options);

        $schema =  $this->getSchema();

        $typeLength =  $schema->getColumn('type')['length'];
        $contextLength =  $schema->getColumn('context')['length'];
        $domainLength =  $schema->getColumn('domain')['length'];
        $nameLength =  $schema->getColumn('name')['length'];
        $descriptionLength =  $schema->getColumn('description')['length'];

        $options['type'] = $options['type'] ? substr($options['type'], 0, $typeLength) : '';
        $options['context'] = $options['context'] ? substr($options['context'], 0, $contextLength) : '';
        $options['domain'] = $options['domain'] ? substr($options['domain'], 0, $domainLength) : '';
        $options['name'] = $options['name'] ? substr($options['name'], 0, $nameLength) : '';
        $options['description'] = $options['description'] ? substr($options['description'], 0, $descriptionLength) : '';

        $heartbeat = $this->newEntity($options);
        return $this->save($heartbeat);
    }

    /**
     * Find Heartbeats
     *
     * @param null $pid
     * @param null $limit
     * @return Query
     */
    public function findHeartbeats($pid = null, $limit = null): Query
    {
        if (!$pid) {
            $pid = getmypid();
        }

        $query = $this->find('all')
            ->where(['pid' => $pid])
            ->where(['type IN' => ['heartbeats']])
            ->orderByAsc('id')
            ->limit($limit);

        return $query;
    }

    /**
     * Find Pulses
     *
     * @param null $pid
     * @param null $limit
     * @return Query
     */
    public function findPulses($pid = null, $limit = null): Query
    {
        if (!$pid) {
            $pid = getmypid();
        }

        $query = $this->find('all')
            ->where(['pid' => $pid])
            ->where(['type IN' => ['pulses']])
            ->orderByAsc('id')
            ->limit($limit);

        return $query;
    }

    /**
     * Find Heartbeats and Pulses
     *
     * @param null $pid
     * @param null $limit
     * @return Query
     */
    public function findHeartbeatsAndPulses($pid = null, $limit = null): Query
    {
        if (!$pid) {
            $pid = getmypid();
        }

        $query = $this->find('all')
            ->where(['pid' => $pid])
            ->where(['type IN' => ['heartbeats', 'pulses']])
            ->orderByAsc('id')
            ->limit($limit);

        return $query;
    }

    /**
     * Purge Heartbeats based on the System PID.
     * This will also purge Pulses that belong to the Heartbeat
     *
     * @return int
     */
    public function purgeHeartbeats(): int
    {
        $pid = getmypid();
        return $this->deleteAll(['pid' => $pid, ['OR' => ['type /*is pulse*/' => 'pulse', 'type /*is heartbeat*/' => 'heartbeat']]]);
    }

    /**
     * Purge Pulses based on the System PID.
     *
     * @return int
     */
    public function purgePulses(): int
    {
        $pid = getmypid();
        return $this->deleteAll(['pid' => $pid, 'type' => 'pulse']);
    }

    /**
     * Effectively deletes all records in the table.
     *
     * @return int
     */
    public function truncate(): int
    {
        return $this->deleteAll(['id >' => 0]);
    }

    /**
     * Find the last Heartbeats
     *
     * @return Query
     */
    public function findLastHeartbeats(): Query
    {
        $cte = ";WITH cte AS ( SELECT *, ROW_NUMBER() OVER (PARTITION BY context ORDER BY id DESC) AS rn FROM Heartbeats Where type = 'heartbeat' )SELECT * FROM cte WHERE rn = 1";

        $conn = $this->getConnection();
        $results = $conn->execute($cte)->fetchAll('assoc');
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['id'];
        }

        $selectList = [
            'created',
            'context',
            'pid',
        ];

        if (empty($ids)) {
            //return a Query that will give an empty result
            return $this->find('all')->where(['id' => 0]);
        }

        $contexts = $this->getHeartbeatContexts();

        $query = $this->find('all')
            ->select($selectList, true)
            ->where(['context IN' => $contexts])
            ->where(['id IN' => $ids])
            ->orderByAsc('context');

        return $query;
    }

    /**
     * Find the last pulse for the given Heartbeat
     *
     * @param Heartbeat $heartbeat
     * @param int $limit
     * @return Query
     */
    public function findPulsesForHeartbeat(Heartbeat $heartbeat, int $limit = 10): Query
    {
        $query = $this->find('all')
            ->where(['pid' => $heartbeat->pid])
            ->where(['type' => 'pulse'])
            ->limit($limit)
            ->orderByDesc('id');

        return $query;
    }

    /**
     * List out the Heartbeat types
     *
     * @return array
     */
    public function getHeartbeatTypes(): array
    {
        $selectList = [
            'type'
        ];
        $query = $this->find('list', keyField: 'id', valueField: 'type')
            ->select($selectList, true)
            ->distinct(['type'])
            ->where(['type !=' => 'pulse'])
            ->group(['type'])
            ->orderByAsc('type');

        return $query->toArray();
    }

    /**
     * List out the Heartbeat contexts
     *
     * @return array
     */
    public function getHeartbeatContexts(): array
    {
        $selectList = [
            'context'
        ];
        $query = $this->find('all')
            ->limit(50)
            ->select($selectList, true)
            ->distinct(['context'])
            ->where(['type !=' => 'pulse'])
            ->group(['context'])
            ->orderByAsc('context');

        return $query->toArray();
    }
}
