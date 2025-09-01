<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Log\Engine\Auditor;
use App\Model\Entity\Errand;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Cake\Validation\Validator;
use Throwable;

/**
 * Errands Model
 *
 * @method Errand newEmptyEntity()
 * @method Errand newEntity(array $data, array $options = [])
 * @method Errand[] newEntities(array $data, array $options = [])
 * @method Errand get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method Errand findOrCreate($search, ?callable $callback = null, $options = [])
 * @method Errand patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method Errand[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method Errand|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Errand saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method Errand[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method Errand[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method Errand[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method Errand[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ErrandsTable extends AppTable
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

        $this->setTable('errands');
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
            ->dateTime('activation')
            ->allowEmptyDateTime('activation');

        $validator
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->boolean('auto_delete')
            ->allowEmptyString('auto_delete');

        $validator
            ->integer('wait_for_link')
            ->allowEmptyString('wait_for_link');

        $validator
            ->scalar('server')
            ->maxLength('server', 128)
            ->allowEmptyString('server');

        $validator
            ->scalar('domain')
            ->maxLength('domain', 128)
            ->allowEmptyString('domain');

        $validator
            ->scalar('name')
            ->maxLength('name', 128)
            ->allowEmptyString('name');

        $validator
            ->integer('background_service_link')
            ->allowEmptyString('background_service_link');

        $validator
            ->scalar('background_service_name')
            ->maxLength('background_service_name', 128)
            ->allowEmptyString('background_service_name');

        $validator
            ->scalar('class')
            ->maxLength('class', 255)
            ->allowEmptyString('class');

        $validator
            ->scalar('method')
            ->maxLength('method', 255)
            ->allowEmptyString('method');

        $validator
            ->scalar('parameters')
            ->maxLength('parameters', 1)
            ->allowEmptyString('parameters');

        $validator
            ->scalar('status')
            ->maxLength('status', 50)
            ->allowEmptyString('status');

        $validator
            ->dateTime('started')
            ->allowEmptyDateTime('started');

        $validator
            ->dateTime('completed')
            ->allowEmptyDateTime('completed');

        $validator
            ->integer('progress_bar')
            ->allowEmptyString('progress_bar');

        $validator
            ->integer('priority')
            ->allowEmptyString('priority');

        $validator
            ->integer('return_value')
            ->allowEmptyString('return_value');

        $validator
            ->scalar('return_message')
            ->maxLength('return_message', 1)
            ->allowEmptyString('return_message');

        $validator
            ->scalar('errors_thrown')
            ->maxLength('errors_thrown', 1)
            ->allowEmptyString('errors_thrown');

        $validator
            ->integer('errors_retry')
            ->allowEmptyString('errors_retry');

        $validator
            ->integer('errors_retry_limit')
            ->allowEmptyString('errors_retry_limit');

        $validator
            ->scalar('hash_sum')
            ->maxLength('hash_sum', 50)
            ->allowEmptyString('hash_sum');

        $validator
            ->integer('lock_to_thread')
            ->allowEmptyString('lock_to_thread');

        $validator
            ->scalar('grouping')
            ->maxLength('grouping', 50)
            ->allowEmptyString('grouping');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [
            'parameters',
            'return_message',
            'errors_thrown',
        ];

        return $jsonFields;
    }


    /**
     * Count how many Errands are ready to run
     *
     * @param null|int|int[] $threadNumber
     * @return int|null
     */
    public function getReadyToRunCount($threadNumber = null): ?int
    {
        //avoid deadlocks
        try {
            $errandQuery = $this->buildQueryForErrands($threadNumber);
            $count = $errandQuery->count();
        } catch (Throwable $e) {
            $count = 0;
        }

        return $count;
    }

    /**
     * @param null|int|int[] $threadNumber
     * @return array|bool|Errand|null
     */
    public function getNextErrand($threadNumber = null): bool|array|Errand|null
    {
        /**
         * @var Errand $errand
         */

        //prevent deadlocks
        try {
            //find and lock a row in one operation to prevent SQL race condition
            $errandQuery = $this->buildQueryForErrandsRowLock($threadNumber);
            $rnd = sha1(Security::guid());
            $query = $this->updateQuery();
            $res = $query
                ->set(['status' => $rnd])
                ->where(['id' => $errandQuery])
                ->rowCountAndClose();
        } catch (Throwable $e) {
            return false;
        }

        if ($res == 0) {
            //no errands to run
            return false;
        }

        $errandRetryLimit = Configure::read("Settings.errand_background_service_retry_limit");
        $errandRetryLimit = max(1, $errandRetryLimit);
        $errandRetry = 0;
        while ($errandRetry < $errandRetryLimit) {
            //prevent deadlocks
            try {
                //now get the locked row
                $errand = $this->find('all')->where(['status' => $rnd])->first();
                if ($errand) {
                    $timeObjCurrent = new DateTime();
                    $errand->started = $timeObjCurrent;
                    $errand->status = 'Allocated';
                    $errand->progress_bar = 0;
                    $this->save($errand);
                    return $errand;
                } else {
                    return false;
                }
            } catch (Throwable $e) {
                $errandRetry++;
            }
        }

        return false;
    }

    /**
     * Returns a query of a single Errand to run (i.e. the next one to run)
     *
     * @param null|int|int[] $threadNumber
     * @return Query
     */
    public function buildQueryForErrandsRowLock($threadNumber = null): Query
    {
        $selectList = [
            "Errands.id",
        ];
        $errandQuery = $this->buildQueryForErrands($threadNumber)
            ->select($selectList, true)
            ->limit(1);
        return $errandQuery;

    }

    /**
     * Returns a query of Errands that can be run
     *
     * @param null|int|int[] $threadNumber
     * @return Query
     */
    public function buildQueryForErrands($threadNumber = null): Query
    {
        $timeObjCurrent = new DateTime();

        $selectList = [
            "Errands.id",
            "Errands.created",
            "Errands.modified",
            "Errands.activation",
            "Errands.expiration",
            "Errands.auto_delete",
            "Errands.wait_for_link",
            "Errands.name",
            "Errands.class",
            "Errands.method",
            "Errands.parameters",
            "Errands.started",
            "Errands.completed",
            "Errands.status",
            "Errands.progress_bar",
            "Errands.priority",
            "Errands.return_value",
            "Errands.return_message",
            "ErrandsParent.id",
            "ErrandsParent.started",
            "ErrandsParent.completed",
        ];
        $errandQuery = $this->find('all')
            ->join([
                'ErrandsParent' => [
                    'table' => 'errands',
                    'alias' => 'ErrandsParent',
                    'type' => 'LEFT',
                    'conditions' => 'ErrandsParent.id = Errands.wait_for_link'
                ],
            ])
            ->select($selectList)
            ->where(['Errands.status IS NULL'])
            ->where(['Errands.started IS NULL'])
            ->where(['OR' => ['Errands.activation <=' => $timeObjCurrent, 'Errands.activation IS NULL']])
            ->where(['OR' => ['Errands.expiration >=' => $timeObjCurrent, 'Errands.expiration IS NULL']])
            ->where(['OR' => ['Errands.wait_for_link IS NULL', 'ErrandsParent.completed IS NOT NULL']])
            ->orderByAsc('Errands.priority')
            ->orderByAsc('Errands.id');

        if ($threadNumber) {
            if (is_array($threadNumber)) {
                $threadNumber = array_merge([0], $threadNumber);
            } else {
                $threadNumber = [0, $threadNumber];
            }
            $errandQuery = $errandQuery->where(['OR' => ['Errands.lock_to_thread IS NULL', 'Errands.lock_to_thread IN' => $threadNumber]]);
        } else {
            $errandQuery = $errandQuery->where(['OR' => ['Errands.lock_to_thread IS NULL', 'Errands.lock_to_thread IN' => [0]]]);
        }

        return $errandQuery;
    }

    /**
     * @param array $options
     * @param bool $preventDuplicateCreation
     * @return Errand|bool
     */
    public function createErrand(array $options = [], bool $preventDuplicateCreation = true): bool|Errand
    {
        $defaultOptions = $this->getDefaultErrandOptions();

        $options = array_merge($defaultOptions, $options);

        if (empty($options['hash_sum'])) {
            $hashSum = $this->calculateHashSumFromOptions($options);
            $options['hash_sum'] = $hashSum;
        }

        if ($options['parameters']) {
            $parameters = $options['parameters'];
            unset($options['parameters']);
        } else {
            $parameters = null;
        }

        $options['name'] = Text::truncateByWidth($options['name'], 128);

        $errand = $this->newEntity($options);
        $errand->parameters = $parameters;

        $countOfDuplicateErrands = $this->countDuplicateErrands($options);

        if ($preventDuplicateCreation && ($countOfDuplicateErrands > 0)) {
            $this->addWarningAlerts(__("There are {0} duplicate Errands, skipping errand creation.", $countOfDuplicateErrands));
            return false;
        }

        $saveResult = $this->save($errand);

        if (!$saveResult) {
            $errors = $errand->getErrors();
            $this->addDangerAlerts(__("Error when saving Errand"));
            $this->addDangerAlerts(json_encode($errors));
        } else {
            $this->addSuccessAlerts(__("Created Errand ID:{0}", $errand->id));
        }

        return $saveResult;
    }

    /**
     * @param $options
     * @return string
     */
    public function calculateHashSumFromOptions($options): string
    {
        $defaultOptions = $this->getDefaultErrandOptions();

        $options = array_merge($defaultOptions, $options);

        $hashSumParams = [
            $options['class'],
            $options['method'],
            $options['parameters'],
            $options['priority'],
        ];

        return sha1(json_encode($hashSumParams));
    }

    /**
     * @param $options
     * @return int
     */
    public function countDuplicateErrands($options): int
    {
        $defaultOptions = $this->getDefaultErrandOptions();

        $options = array_merge($defaultOptions, $options);

        $queryIsHashExistsCount = $this->find('all')
            ->where(['hash_sum' => $options['hash_sum']])
            ->where(['activation <=' => $options['activation']])
            ->where(['expiration >=' => $options['activation']])
            ->count();

        return $queryIsHashExistsCount;
    }

    private function getDefaultErrandOptions(): array
    {
        $defaultActivation = new DateTime();
        $defaultExpiration = new DateTime('+ ' . Configure::read('Settings.data_purge') . ' months');
        $errandRetryLimit = Configure::read("Settings.errand_background_service_retry_limit");

        $defaultOptions = [
            'activation' => $defaultActivation,
            'expiration' => $defaultExpiration,
            'auto_delete' => true,
            'wait_for_link' => null,
            'server' => null,
            'domain' => parse_url(Router::url("/", true), PHP_URL_HOST),
            'name' => ' ',
            'background_service_link' => null,
            'background_service_name' => null,
            'class' => null,
            'method' => null,
            'parameters' => null,
            'status' => null,
            'started' => null,
            'completed' => null,
            'progress_bar' => null,
            'priority' => 5,
            'return_value' => null,
            'return_message' => null,
            'errors_thrown' => null,
            'errors_retry' => 0,
            'errors_retry_limit' => $errandRetryLimit,
            'hash_sum' => null,
            'lock_to_thread' => 0,
            'grouping' => null,
        ];

        return $defaultOptions;
    }


    /**
     * Delete duplicate Errands
     *
     * @return bool|int
     */
    public function deleteDuplicates(): bool|int
    {
        $queryDelete = $this->deleteDuplicatesQuery();

        $currentTime = time();
        $futureTime = $currentTime + 10;
        $rowCount = false;
        while ($currentTime <= $futureTime && $rowCount === false) {
            try {
                $time_start = microtime(true);
                $rowCount = $queryDelete->rowCountAndClose();
                $time_end = microtime(true);
                $time_total = $time_end - $time_start;
            } catch (Throwable $e) {
            }
            $currentTime = time();
        }

        return $rowCount;
    }


    /**
     * Delete duplicate Errands
     *
     * @return \Cake\Database\Query
     */
    public function deleteDuplicatesQuery()
    {
        $utcDateString = (new DateTime())->setTimezone('UTC')->format('Y-m-d H:i:s');

        $subTableToSelectFrom = $this->findSubTableForCompare();

        $queryDistinct = $this->selectQuery()
            ->select(['MIN(id)'])
            ->from(['Errands' => $subTableToSelectFrom])
            ->where(['started IS' => null, 'completed IS' => null])
            ->where(['activation <' => $utcDateString, 'expiration >' => $utcDateString])
            ->group(['class', 'method', 'parameters', 'priority']);


        $queryWaitForParent = $this->selectQuery()
            ->select(['id'])
            ->from('errands')
            ->where(['wait_for_link IS NOT' => null]);


        $queryDelete = $this->deleteQuery()
            ->delete('errands')
            ->where(["id NOT IN" => $queryDistinct])
            ->where(["id NOT IN" => $queryWaitForParent])
            ->where(['started IS' => null, 'completed IS' => null]);

        return $queryDelete;
    }

    /**
     * Because the 'parameters' column is TEXT it cannot be directly used in a compare for SELECT DISTINCT queries.
     * This creates a sub-table where 'parameters' are converted to nvarchar(1024)
     *
     * @return Query
     */
    public function findSubTableForCompare()
    {
        $selects = [
            'id' => 'id',
            'class' => 'class',
            'method' => 'method',
            'parameters' => 'convert(nvarchar(1024), parameters)',
            'priority' => 'priority',
            'started' => 'started',
            'completed' => 'completed',
            'activation' => 'activation',
            'expiration' => 'expiration',
        ];

        $query = $this->find('all')
            ->select($selects);

        return $query;
    }

    /**
     * @param int|string|Entity $idOrEntity
     * @param int $futureOffsetSeconds
     * @param bool $includeNotStartedInGroup
     * @return int //number of rows effected
     */
    public function resetErrand(Entity|int|string $idOrEntity, int $futureOffsetSeconds = 0, bool $includeNotStartedInGroup = false): int
    {
        /** @var Errand $errand */
        $errand = $this->asEntity($idOrEntity);

        if (!$errand) {
            return 0;
        }

        $numRolledForward = 0;

        //roll forward the not started in group
        if ($includeNotStartedInGroup) {
            $result = $this->rollForwardNotStartedInGroup($errand->grouping, $futureOffsetSeconds);
            $numRolledForward = $numRolledForward + $result;
        }

        //reset and roll forward the given Errand
        $errand->started = null;
        $errand->completed = null;
        $errand->server = null;
        $errand->background_service_link = null;
        $errand->background_service_name = null;
        $errand->status = null;
        $errand->progress_bar = null;
        $errand->return_value = null;
        $errand->return_message = null;
        $errand->errors_thrown = null;
        $errand->errors_retry = null;
        $result = $this->rollForwardActivationExpiration($errand, false, $futureOffsetSeconds);
        if ($result) {
            $numRolledForward++;
        }

        return ($numRolledForward);
    }

    /**
     * @param string $grouping
     * @param int $futureOffsetSeconds
     * @return int //number of rows effected
     */
    public function rollForwardNotStartedInGroup(string $grouping, int $futureOffsetSeconds = 0): int
    {
        /** @var Errand[] $errands */
        $errands = $this->find('all')->where(['grouping' => $grouping, 'grouping IS NOT NULL', 'started IS NULL']);

        $counter = 0;
        foreach ($errands as $errand) {
            $result = $this->rollForwardActivationExpiration($errand->id, false, $futureOffsetSeconds);
            if ($result) {
                $counter++;
            }
        }

        return $counter;
    }
}
