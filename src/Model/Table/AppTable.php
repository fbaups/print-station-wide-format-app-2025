<?php


namespace App\Model\Table;


use App\Database\Limits;
use App\Log\Engine\Auditor;
use App\Model\Entity\Artifact;
use App\Utility\Feedback\DebugSqlCapture;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\PrePressTricks\Utilities\Pages;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\Query\DeleteQuery;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use League\Csv\Reader;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

/**
 * Class AppTable
 *
 * @property Limits $Limits
 * @property array $successAlerts
 * @property array $dangerAlerts
 * @property array $warningAlerts
 * @property array $infoAlerts
 * @property int $returnValue
 *
 * @property array $typeMap
 * @package App\Model\Table
 */
class AppTable extends Table
{
    use ReturnAlerts;

    private int $boundParamLimit;
    private Limits|null $Limits = null;

    protected array $typeMap = []; //hold the TypeMap as  $this->getSchema()->typeMap() is deprecated in this parent

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        if (!$this->Limits) {
            $dbDriver = $this->getDriver();
            $this->Limits = new Limits($dbDriver);
        }

        $this->boundParamLimit = $this->Limits->getBoundParamLimit();

        $this->initializeSchemaJsonFields($this->getJsonFields());
    }

    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void
    {
        $typeMap = $this->getSchema()->typeMap();

        if (defined('LCL_TZ') && strtolower(LCL_TZ) !== 'utc') {
            $dateCols = [];
            foreach ($typeMap as $colName => $colType) {
                if ($colType === 'datetime') {
                    if ($colName !== 'created' && $colName !== 'modified') {
                        $dateCols[] = $colName;
                    }
                }
            }
            foreach ($dateCols as $dateCol) {
                if (isset($data[$dateCol])) {
                    if (is_string($data[$dateCol])) {
                        $data[$dateCol] = (new DateTime($data[$dateCol], LCL_TZ))->setTimezone('utc');
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->getConnection()->config()['driver'];
    }

    /**
     * @return TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        return parent::getSchema();
    }

    /**
     * Default list of JSON fields - default for child classes
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [];

        return $jsonFields;
    }

    /**
     * Initialise JSON Fields
     *
     * @param $jsonFields
     * @return void
     */
    public function initializeSchemaJsonFields($jsonFields): void
    {
        //try/catch required for installation as DB does not exist yet
        try {
            $schema = $this->getSchema();
            foreach ($jsonFields as $jsonField) {
                $schema->setColumnType($jsonField, 'json');
            }
        } catch (\Throwable $exception) {

        }
    }

    /**
     * Insert mass data into a table. Basically raw SQL to do the insert so much faster than the ORM.
     * Validation is NOT applied to the records.
     *
     *
     * @param $records
     * @return int
     */
    public function massInsert($records): int
    {
        $rowCount = 0;

        $typeMap = $this->getSchema()->typeMap();
        if (isset($typeMap['id'])) {
            unset($typeMap['id']);
        }

        $typeMapUsed = [];

        if (isset($typeMap['created'])) {
            $typeMapUsed['created'] = $typeMap['created'];
        }

        if (isset($typeMap['modified'])) {
            $typeMapUsed['modified'] = $typeMap['modified'];
        }

        //loop once to find what fields are being used
        foreach ($records as $i => $record) {
            foreach ($record as $fieldKey => $fieldValue) {
                if (isset($typeMap[$fieldKey])) {
                    $typeMapUsed[$fieldKey] = $typeMap[$fieldKey];
                }
            }
        }

        $defaultFieldsValuesUsed = array_fill_keys(array_keys($typeMapUsed), null);
        $cleanedRecords = [];
        //square up the array
        foreach ($records as $i => $record) {
            //add default columns
            $record = array_merge($defaultFieldsValuesUsed, $record);
            //filter extra columns
            $record = array_intersect_key($record, $defaultFieldsValuesUsed);
            $cleanedRecords[$i] = $record;
        }
        $timeObjCurrent = new DateTime();

        $totalCount = count($cleanedRecords);
        $counter = 1;
        $folderQueriesToExec = [];
        $query = null;
        $tableName = $this->getTable();
        $batchLimit = intval(floor($this->boundParamLimit / count($typeMapUsed))); //based on bound param limit in SQL
        $batchLimit = max($batchLimit, 2); //cannot have a batch of 1

        foreach ($cleanedRecords as $data) {

            if ($counter % $batchLimit == 1) {
                $query = $this->insertQuery()
                    ->into($tableName)
                    ->insert(array_keys($typeMapUsed), $typeMapUsed);
            }

            if (isset($typeMap['created'])) {
                $data['created'] = $timeObjCurrent;
            }

            if (isset($typeMap['modified'])) {
                $data['modified'] = $timeObjCurrent;
            }

            $query->values($data);

            if ($counter % $batchLimit == 0 || $counter == $totalCount) {
                $folderQueriesToExec[] = $query;
            }

            $counter++;
        }

        foreach ($folderQueriesToExec as $query) {
            $rowCount += $query->rowCountAndClose();
        }

        return $rowCount;
    }

    public function getApplicationPids()
    {
        $tasks = $this->getApplicationTasks();

        $pids = [];
        foreach ($tasks as $task) {
            if (isset($task['PID'])) {
                $pids[] = $task['PID'];
            }
        }

        return $pids;
    }


    public function getApplicationTasks()
    {
        $tasks = Cache::read('ApplicationTasks', 'micro');
        if (!empty($tasks)) {
            return $tasks;
        }

        $cmd = __("tasklist /FO CSV");
        exec($cmd, $out, $ret);

        $out = implode("\n", $out);
        $csv = Reader::createFromString($out);
        try {
            $csv->setHeaderOffset(0);
        } catch (\Throwable $exception) {

        }

        //these are the potential Background Services that exist, based on name of PHP & NSSM executable.
        $searchImages = ["php.exe", "nssm.exe"];
        $cleaned = [];
        foreach ($csv->getRecords() as $record) {
            if (isset($record['Image Name'])) {
                if (in_array(strtolower($record['Image Name']), $searchImages)) {
                    $cleaned[] = $record;
                }
            }
        }

        Cache::write('ApplicationTasks', $cleaned, 'micro');

        return $cleaned;
    }

    /**
     * Function to get the *FIRST* ID by the given name or alias
     *
     * @param array|string $nameOrAlias
     * @return int|null
     */
    public function getIdByNameOrAlias(array|string $nameOrAlias): int|null
    {
        $record = $this->findByNameOrAlias($nameOrAlias)->first();

        if ($record) {
            return $record->id;
        } else {
            return null;
        }
    }

    /**
     * Function to get *ALL* IDs by the given name or alias
     *
     * @param array|string $nameOrAlias
     * @return array
     */
    public function getIdsByNameOrAlias(array|string $nameOrAlias): array
    {
        $records = $this->findByNameOrAlias($nameOrAlias);

        /** @var EntityInterface $record */
        $ids = [];
        foreach ($records as $record) {
            $ids[] = $record->id;
        }
        return $ids;
    }

    /**
     * @param $idOrEntity
     * @return int
     */
    public function asId($idOrEntity): int
    {
        if (isset($idOrEntity->id)) {
            return $idOrEntity->id;
        }

        if (is_numeric($idOrEntity)) {
            return intval($idOrEntity);
        }

        return 0;
    }

    /**
     * Convert the given unknown input to an entity
     * Will find and pass back and Entity based on:
     *  (int) id
     *  (string) hash_sum or token
     *  (Entity) pass it back
     *
     * NOTE: hash_sum may not get the right entity.
     * If for example the same image is uploaded twice, they will have the same hash_sum.
     * The first entity found will be returned.
     * Try providing a token as they are unique.
     *
     * @param $idOrEntity
     * @return EntityInterface|false
     */
    public function asEntity($idOrEntity): false|EntityInterface
    {
        $testEntity = $this->newEmptyEntity();
        $hasToken = $testEntity->getAccessible()['token'] ?? false;
        $hasHashSum = $testEntity->getAccessible()['hash_sum'] ?? false;

        if (is_int($idOrEntity) || is_numeric($idOrEntity)) {
            $idOrEntity = intval($idOrEntity);
            $entity = $this->find('all')
                ->where(['id' => $idOrEntity])
                ->first();
            if (!$entity) {
                $this->addWarningAlerts("No Entity based in the passed in ID.");
                return false;
            }
        } elseif (is_string($idOrEntity) && ($hasToken || $hasHashSum)) {
            $entity = $this->find('all')
                ->where(["OR" => ['token' => $idOrEntity, 'hash_sum' => $idOrEntity,]])
                ->first();
            if (!$entity) {
                $this->addWarningAlerts("No Entity based in the passed in Token or Hash Sum.");
                return false;
            }
        } elseif (is_object($idOrEntity) && (get_class($idOrEntity) === get_class($this->newEmptyEntity()))) {
            $entity = $idOrEntity;
        } else {
            $this->addDangerAlerts("Failed to parse ID/Token/HashSum/Entity to Entity.");
            $entity = false;
        }

        $this->addSuccessAlerts("Parsed input to Entity.");
        return $entity;
    }

    /**
     * Rolls the Activation and Expiration date forward for the given ID.
     * Automatically rolled forward to the current date and time (+ the specified offset)
     * Keeps the same spread between the Activation and Expiration
     *
     * @param $idOrEntity
     * @param bool $checkActivationExpiration Only rolls forward if the both Activation and Expiration dates are in the past.
     * @param int $futureOffsetSeconds Added to the current date and time when rolling forward
     * @return bool
     */
    public function rollForwardActivationExpiration($idOrEntity, bool $checkActivationExpiration = true, int $futureOffsetSeconds = 0): bool
    {
        /** @var Entity $entity */
        $entity = $this->asEntity($idOrEntity);

        if (!$entity) {
            return false;
        }

        if (!$entity->has('activation') || !$entity->has('expiration')) {
            return false;
        }

        $timeObjCurrentWithOffset = (new DateTime('now'))->addSeconds($futureOffsetSeconds);

        /** @var DateTime $activation */
        /** @var DateTime $expiration */
        $activation = $entity->activation;
        $expiration = $entity->expiration;

        if ($checkActivationExpiration) {
            if ($timeObjCurrentWithOffset->lessThanOrEquals($activation) || $timeObjCurrentWithOffset->lessThanOrEquals($expiration)) {
                return false;
            }
        }

        $differenceSeconds = $timeObjCurrentWithOffset->diffInSeconds($activation);
        $activation = $activation->addSeconds($differenceSeconds);
        $expiration = $expiration->addSeconds($differenceSeconds);

        $patch = [
            'activation' => $activation,
            'expiration' => $expiration,
        ];
        $entity = $this->patchEntity($entity, $patch);
        $result = $this->save($entity);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Logs saving errors to the DB
     *
     * @param EntityInterface $entity
     * @param array $options
     * @return EntityInterface|false
     */
    public function save(EntityInterface $entity, array $options = []): EntityInterface|false
    {
        $result = parent::save($entity, $options);

        $alias = $this->getAlias();

        if (!$result) {
            $errors = $entity->getErrors();
            $Auditor = new Auditor();
            $message = __("Error when saving {$alias}.\r\n\r\n{0}", json_encode($errors, JSON_PRETTY_PRINT));
            $Auditor->logError($message);
        }

        return $result;
    }

    /**
     * Delete expired records
     * Takes into account the 'auto_delete' flag if it exists.
     *
     * @return bool|int
     */
    public function deleteExpired(): bool|int
    {
        $typeMap = $this->getSchema()->typeMap();
        if (!isset($typeMap['expiration'])) {
            return false;
        }

        $currentDatetime = new DateTime();

        if (isset($typeMap['auto_delete'])) {
            $conditions = ['expiration <=' => $currentDatetime->format("Y-m-d H:i:s"), 'auto_delete' => true];
        } else {
            $conditions = ['expiration <=' => $currentDatetime];
        }

        return $this->deleteAll($conditions);
    }

    /**
     * Delete expired records
     * Deletes regardless of the 'auto_delete' flag.
     *
     * @return bool|int
     */
    public function deleteExpiredForce(): bool|int
    {
        $typeMap = $this->getSchema()->typeMap();
        if (!isset($typeMap['expiration'])) {
            return false;
        }

        $currentDatetime = new DateTime();
        $conditions = ['expiration <=' => $currentDatetime];

        return $this->deleteAll($conditions);
    }

    /**
     * Delete records if they are orphaned from a BelongsTo association
     *
     * @return int
     */
    public function deleteOrphaned(): int
    {
        $Associations = $this->associations();

        $deletions = [];
        $deletionRecordCounter = 0;
        $deletionTableCounter = 0;
        foreach ($Associations->getIterator() as $Association) {
            if (!($Association instanceof BelongsTo)) {
                continue;
            }

            $ParentModel = $Association->getTableLocator()->get($Association->getAlias());

            $parentIds = $ParentModel->find('all')->select('id');
            $foreignKey = $Association->getForeignKey();

            $conditions = ["{$foreignKey} NOT IN" => $parentIds];
            $deleteQuery = (new DeleteQuery($this))->where($conditions);
            $numDeleted = $deleteQuery->rowCountAndClose();

            $deletionRecordCounter = $deletionRecordCounter + $numDeleted;
            $deletionTableCounter++;

            $deletions[] = [
                $this->getAlias() => $numDeleted,
            ];
        }

        $this->addInfoAlerts("Deleted {$deletionRecordCounter} records from {$deletionTableCounter} tables. " . json_encode($deletions));

        return $deletionRecordCounter;
    }

    /**
     * @param array|string $nameOrAlias
     * @return Query|null
     */
    public function findByNameOrAlias(array|string $nameOrAlias): ?Query
    {
        $tableColumns = $this->getSchema()->columns();

        $searchColumns = ['name', 'alias'];

        $cols = array_intersect($searchColumns, $tableColumns);

        if (empty($cols)) {
            return null;
        }

        $searchClause = [];
        foreach ($cols as $col) {
            $searchClause[$col . ' IN'] = $nameOrAlias;
        }

        $finder = $this->find('all')
            ->where(['OR' => $searchClause])
            ->orderByAsc('id');

        return $finder;
    }

    /**
     * @param array|string $hashSum
     * @return Query|null
     */
    public function findByHashSum(array|string $hashSum): ?Query
    {
        $tableColumns = $this->getSchema()->columns();

        $searchColumns = ['hash_sum', 'hashsum'];

        $cols = array_intersect($searchColumns, $tableColumns);

        if (empty($cols)) {
            return null;
        }

        $searchClause = [];
        foreach ($cols as $col) {
            $searchClause[$col . ' IN'] = $hashSum;
        }

        $finder = $this->find('all')
            ->where(['OR' => $searchClause])
            ->orderByAsc('id');

        return $finder;
    }

    /**
     * Take the given Entity (or ID) and redact insecure fields.
     *
     * @param Entity|int|string $idOrEntity
     * @param array $toRedact
     * @return array|false
     */
    public function redactEntity(Entity|int|string $idOrEntity, array $toRedact = [], array $toKeep = []): Entity
    {
        $ent = $this->asEntity($idOrEntity);

        $toRedactDefault = ['password', 'api_key', 'token', 'secret', 'payload'];
        $toRedact = array_merge($toRedactDefault, $toRedact);
        $toRedact = array_diff($toRedact, $toKeep);

        $entFields = $ent->getAccessible();
        foreach ($entFields as $fieldName => $fieldAccessibility) {
            if (in_array($fieldName, $toRedact)) {
                $ent->setAccess($fieldName, false);
                unset($ent->{$fieldName});
            }
        }

        return $ent;
    }

    /**
     * Get the currently authenticated User entity
     *
     * @return array|EntityInterface|false
     */
    public function getCurrentAuthenticatedUser(array $contain = null): bool|array|EntityInterface
    {
        $id = $this->getCurrentAuthenticatedUserId();

        if (empty($id)) {
            return false;
        }

        /** @var UsersTable $Users */
        $Users = TableRegistry::getTableLocator()->get('Users');

        $user = $Users->find('all')->where(['id' => $id]);

        if ($contain) {
            $user = $user->contain($contain);
        }

        $user = $user->first();
        if ($user) {
            return $user;
        } else {
            return false;
        }
    }

    /**
     * Get the currently authenticated User entity
     *
     * @return false|int
     */
    public function getCurrentAuthenticatedUserId(): false|int
    {
        // Try to get from current request context first
        $request = Router::getRequest();
        if ($request && $request->getAttribute('authentication')) {
            $identity = $request->getAttribute('authentication')->getIdentity();
            if ($identity && isset($identity->id)) {
                return $identity->id;
            }
        }

        // Fallback to session for backward compatibility
        $session = new Session();
        $id = $session->read('Auth.User.id');

        if (empty($id)) {
            return false;
        }

        return $id;
    }

    public function convertJsonFieldsToString(array|string $jsonFields): void
    {
        if (is_string($jsonFields)) {
            $jsonFields = [$jsonFields];
        }

        $Schema = $this->getSchema();

        foreach ($jsonFields as $jsonField) {
            if (strtolower($Schema->getColumnType($jsonField)) === 'json') {
                $Schema->setColumnType($jsonField, 'string');
            }
        }
    }

    /**
     * @param Query $modelFinder Query such as $this->find('all')
     * @param array $datatablesQuery the search request as generated by Datatables
     * @param array $options
     * @return Query
     */
    public function applyDatatablesQuickSearchFilter(Query $modelFinder, array $datatablesQuery, array $options = []): Query
    {
        $searchValue = $datatablesQuery['search']['value'] ?? null;
        if (empty($searchValue)) {
            if ($searchValue !== 0 && $searchValue !== '0') {
                return $modelFinder;
            }
        }

        $defaultOptions = [
            'numeric_fields' => ['id', 'rank', ' priority'],
            'text_fields' => ['name', 'description', 'text', 'first_name', 'last_name'],
        ];

        $options = array_merge($defaultOptions, $options);

        $alias = $this->getAlias();
        $modelFields = $this->getSchema()->columns();
        $modelFieldsWithAlias = [];
        foreach ($modelFields as $modelField) {
            $modelFieldsWithAlias[] = $modelField;
            $modelFieldsWithAlias[] = $alias . "." . $modelField;
        }

        $numericFilter = [];
        foreach ($options['numeric_fields'] as $field) {
            if (!in_array($field, $modelFieldsWithAlias)) {
                continue;
            }
            $numericFilter[$field] = intval($searchValue);
        }

        $textFilter = [];
        foreach ($options['text_fields'] as $field) {
            if (!in_array($field, $modelFieldsWithAlias)) {
                continue;
            }
            $textFilter["{$field} LIKE"] = '%' . $searchValue . '%';
        }

        if (is_numeric($searchValue) && !empty($numericFilter)) {
            $modelFinder = $modelFinder->where(
                ['OR' => $numericFilter]
            );
        } elseif (is_string($searchValue) && !empty($textFilter)) {
            $modelFinder = $modelFinder->where(
                ['OR' => $textFilter]
            );
        }

//        $sql = DebugSqlCapture::captureDump($modelFinder, false);
//        Log::write('info', json_encode($sql, JSON_PRETTY_PRINT));
//        file_put_contents(LOGS . "datatables-quick-search.sql", DebugSqlCapture::captureDump($modelFinder));
//        file_put_contents(LOGS . "datatables-column-search.json", json_encode($datatablesQuery, JSON_PRETTY_PRINT));

        return $modelFinder;
    }

    /**
     * @param Query $modelFinder Query such as $this->find('all')
     * @param array $datatablesQuery the search request as generated by Datatables
     * @param array $headers
     * @return Query
     */
    public function applyDatatablesColumnFilters(Query $modelFinder, array $datatablesQuery, array $headers = []): Query
    {
        $modelName = $this->getAlias();

        if (count($headers) !== count($datatablesQuery['columns'])) {
            return $modelFinder;
        }

        foreach ($headers as $k => $headerName) {
            if (strtolower($headerName) === 'actions') {
                continue;
            }

            $searchValue = $datatablesQuery['columns'][$k]['search']['value'] ?? false;
            if (empty($searchValue) && ($searchValue !== 0 || $searchValue !== '0')) {
                if ($searchValue !== 0 && $searchValue !== '0') {
                    continue;
                }
            }

            if (str_contains($headerName, '.')) {
                $headerNameWithHeaderModel = $headerName;
                $relatedModelName = explode(".", $headerName)[0];
                $headerName = explode(".", $headerName);
                $headerName = array_pop($headerName);
            } else {
                $headerNameWithHeaderModel = "{$modelName}.{$headerName}";
                $relatedModelName = false;
            }

            if ($relatedModelName) {
                $relatedTable = TableRegistry::getTableLocator()->get($relatedModelName);
                $colType = $relatedTable->getSchema()->getColumnType($headerName);
            } else {
                $colType = $this->getSchema()->getColumnType($headerName);
            }


            if ($colType === 'datetime') {
                try {
                    $dateParts = explode('-', $searchValue);
                    $year = !empty($dateParts[0]) ? intval($dateParts[0]) : false;
                    $month = !empty($dateParts[1]) ? intval($dateParts[1]) : false;
                    $day = !empty($dateParts[2]) ? intval($dateParts[2]) : false;

                    $date = new DateTime(null, LCL_TZ);
                    if ($year) {
                        $date = $date->year($year);
                    }
                    if ($month) {
                        $date = $date->month($month);
                    }
                    if ($day) {
                        $date = $date->day($day);
                    }

                    //lowest year in DB is 1753 - when Gregorian calendar started
                    if ($year && $year > 1753 && $year < 9998) {
                        if ($year && $month && $day) {
                            $date = $date->year($year)->month($month)->day($day);
                            $startDate = (clone $date)->startOfDay()->setTimezone('utc')->format("Y-m-d H:i:s");
                            $endDate = (clone $date)->endOfDay()->setTimezone('utc')->format("Y-m-d H:i:s");
                            $modelFinder = $modelFinder->where([$headerNameWithHeaderModel . ' >=' => $startDate]);
                            $modelFinder = $modelFinder->where([$headerNameWithHeaderModel . ' <=' => $endDate]);
                        } elseif ($year && $month && !$day) {
                            $date = $date->year($year)->month($month);
                            $startDate = (clone $date)->startOfMonth()->setTimezone('utc')->format("Y-m-d H:i:s");
                            $endDate = (clone $date)->endOfMonth()->setTimezone('utc')->format("Y-m-d H:i:s");
                            $modelFinder = $modelFinder->where([$headerNameWithHeaderModel . ' >=' => $startDate]);
                            $modelFinder = $modelFinder->where([$headerNameWithHeaderModel . ' <=' => $endDate]);
                        } elseif ($year && !$month && !$day) {
                            $date = $date->year($year);
                            $startDate = (clone $date)->startOfYear()->setTimezone('utc')->format("Y-m-d H:i:s");
                            $endDate = (clone $date)->endOfYear()->setTimezone('utc')->format("Y-m-d H:i:s");
                            $modelFinder = $modelFinder->where([$headerNameWithHeaderModel . ' >=' => $startDate]);
                            $modelFinder = $modelFinder->where([$headerNameWithHeaderModel . ' <=' => $endDate]);
                        }
                    } else {
                        //force no results as date is out of range
                        $modelFinder = $modelFinder->where(['id' => 0]);
                    }
                } catch (\Throwable $exception) {
                    $this->addDangerAlerts($exception->getMessage());
                }
            } elseif ($colType === 'string') {
                $modelFinder = $modelFinder->where(["{$headerNameWithHeaderModel} LIKE" => '%' . $searchValue . '%']);
            } elseif ($colType === 'integer') {
                $searchColumn = $headerNameWithHeaderModel;
                $conditions = $this->parseIntegerSearchString($searchValue, $searchColumn);
                if ($conditions) {
                    $modelFinder = $modelFinder->where($conditions);
                }
            } elseif ($colType === 'float') {
                $searchValueAsFloat = floatval($searchValue);
                $modelFinder = $modelFinder->where([$headerNameWithHeaderModel => $searchValueAsFloat]);
            } elseif ($colType === 'boolean') {
                $searchValueAsBool = asBool($searchValue);
                $modelFinder = $modelFinder->where([$headerNameWithHeaderModel => $searchValueAsBool]);
            } else {
                $modelFinder = $modelFinder->where(["{$headerNameWithHeaderModel} LIKE" => '%' . $searchValue . '%']);
            }
        }

//        $sql = DebugSqlCapture::captureDump($modelFinder, false);
//        Log::write('info', json_encode($sql, JSON_PRETTY_PRINT));
//        file_put_contents(LOGS . "datatables-column-search.sql", DebugSqlCapture::captureDump($modelFinder));
//        file_put_contents(LOGS . "datatables-column-search.json", json_encode($datatablesQuery, JSON_PRETTY_PRINT));
//        file_put_contents(LOGS . "datatables-column-search.json", json_encode($datatablesQuery, JSON_PRETTY_PRINT));

        return $modelFinder;
    }

    /**
     * @param Query $modelFinder Query such as $this->find('all')
     * @param array $datatablesQuery the search request as generated by Datatables
     * @param array $headers
     * @return array
     */
    public function applyDatatablesSorting(Query $modelFinder, array $datatablesQuery, array $headers = []): array
    {
        $modelName = $this->getAlias();
        $modelNameWithDot = "{$modelName}.";

        $sorting = [];
        if (isset($datatablesQuery['order']) && is_array($datatablesQuery['order'])) {
            foreach ($datatablesQuery['order'] as $item) {
                if (isset($headers[$item['column']])) {
                    $orderBy = $headers[$item['column']];
                    $sortDirection = $item['dir'];

                    if (count(explode(".", $orderBy)) === 1) {
                        $modelPrefix = $modelNameWithDot;
                    } else {
                        $modelPrefix = '';
                    }

                    $sorting[$modelPrefix . $orderBy] = $sortDirection;
                }
            }
        }

        //file_put_contents(LOGS . "datatables-sorting.json", json_encode($sorting, JSON_PRETTY_PRINT));
        //file_put_contents(LOGS . "datatables-headers.json", json_encode($headers, JSON_PRETTY_PRINT));

        return $sorting;
    }

    /**
     * @param string $searchString
     * @param string $searchColumn
     * @return false|array
     */
    public function parseIntegerSearchString(string $searchString, string $searchColumn): false|array
    {
        //trim
        $searchString = trim($searchString);
        $searchString = trim($searchString, ',.-–—');

        //if space delimited 1 2 3 45 56 (convert to comma delimited)
        if (strlen(str_replace(['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', ' ', ','], '', $searchString)) === 0) {
            $searchString = str_replace(' ', ',', $searchString);
        }

        //basic number e.g. 42
        if (is_numeric($searchString) && intval($searchString) == $searchString) {
            return ["{$searchColumn} IN" => [intval($searchString)]];
        }

        //operator style >=20<=40
        $re = '/(!=|<>|<=|<|>=|>)(\d+)/m';
        $matchCount = preg_match_all($re, $searchString, $matches, PREG_SET_ORDER, 0);
        if (is_numeric($matchCount) && $matchCount > 0) {
            $filters = [];
            foreach ($matches as $match) {
                $operator = $match[1];
                $number = intval($match[2]);
                $filters[] = ["{$searchColumn} {$operator}" => $number];
            }
            if ($matchCount > 1) {
                return ["AND" => $filters];
            } else {
                return $filters;
            }
        }

        //range style e.g. 1-5,7,12,42-50
        try {
            $Pages = new Pages();
            $options = ['returnFormat' => 'array', 'duplicateStringSingles' => true];
            $numbers = $Pages->rangeCompact($searchString, $options);

            $filters = [];
            foreach ($numbers as $number) {
                $filters[] = ["{$searchColumn} >=" => intval($number['lower']), "{$searchColumn} <=" => intval($number['upper'])];
            }
            return ["OR" => $filters];
        } catch (\Throwable $exception) {

        }

        return false;
    }

    public function getBlobInfo()
    {

    }

    /**
     * @return string[]
     */
    public function getSafeMimeTypes(): array
    {
        $pdf = $this->getPdfMimeTypes();

        $image = $this->getImageMimeTypes();

        $video = $this->getVideoMimeTypes();

        $audio = $this->getAudioMimeTypes();

        $archive = $this->getArchiveMimeTypes();

        $text = $this->getTextMimeTypes();

        $font = $this->getFontMimeTypes();

        $office = $this->getMicrosoftOfficeMimeTypes();

        return array_unique(array_merge($pdf, $image, $video, $audio, $archive, $office, $text, $font));
    }

    /**
     * @return string[]
     */
    public function getPdfMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/x-pdf',
            'application/acrobat',
            'applications/vnd.pdf',
            'text/pdf',
            'text/x-pdf',
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isPdf(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getPdfMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * @return string[]
     */
    public function getTextMimeTypes(): array
    {
        return [
            'text/plain',                     // .txt
            'text/csv',                       // .csv
            'text/css',                       // .css
            'text/html',                      // .html, .htm
            'text/rtf',                       // .rtf
            'text/xml',                       // .xml
            'application/json',               // .json
            'application/xml',                // .xml (application form)
            'application/x-yaml',             // .yaml, .yml
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isText(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getPdfMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * @return string[]
     */
    public function getFontMimeTypes(): array
    {
        return [
            'application/x-font-ttf',        // .ttf
            'application/x-font-otf',        // .otf
            'font/woff',                     // .woff
            'font/woff2',                    // .woff2
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isFont(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getPdfMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * @return string[]
     */
    public function getImageMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/tiff',
            'image/bmp',
            'image/webp',
            'image/vnd.adobe.photoshop',
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isImage(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getImageMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * @return string[]
     */
    public function getVideoMimeTypes(): array
    {
        return [
            'video/mp4',
            'video/quicktime',       // .mov
            'video/x-msvideo',       // .avi
            'video/x-ms-wmv',        // .wmv
            'video/x-flv',           // .flv
            'video/webm',            // .webm
            'video/ogg',             // .ogv
            'application/x-mpegURL', // .m3u8
            'video/MP2T',            // .ts
            'video/3gpp',            // .3gp
            'video/3gpp2',           // .3g2
            'video/x-matroska',      // .mkv
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isVideo(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getVideoMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * @return string[]
     */
    public function getAudioMimeTypes(): array
    {
        return [
            'audio/mpeg',           // .mp3
            'audio/mp4',            // .mp4 (audio)
            'audio/aac',            // .aac
            'audio/ogg',            // .ogg
            'audio/webm',           // .webm
            'audio/wav',            // .wav
            'audio/x-wav',          // .wav (variant)
            'audio/x-aiff',         // .aiff
            'audio/flac',           // .flac
            'audio/3gpp',           // .3gp (audio only)
            'audio/3gpp2',          // .3g2 (audio only)
            'audio/x-ms-wma',       // .wma
            'audio/midi',           // .mid, .midi
            'audio/x-midi',         // .mid, .midi (variant)
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isAudio(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getAudioMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * Common MIME types for archive/compressed files.
     *
     * @return string[]
     */
    public function getArchiveMimeTypes(): array
    {
        return [
            'application/zip',                      // .zip
            'application/x-zip-compressed',         // .zip (variant, esp. Windows/IIS)
            'multipart/x-zip',                      // .zip (older/rare)
            'application/x-tar',                    // .tar
            'application/gzip',                     // .gz
            'application/x-gzip',                   // .gz (variant)
            'application/x-7z-compressed',          // .7z
            'application/x-rar-compressed',         // .rar
            'application/vnd.rar',                  // .rar (newer official)
            'application/x-bzip2',                  // .bz2
            'application/x-bzip',                   // .bz
            'application/x-lzip',                   // .lz
            'application/x-xz',                     // .xz
            'application/zstd',                     // .zst (Zstandard)
            'application/x-zstd',                   // .zst (variant)
            'application/x-apple-diskimage',        // .dmg (MacOS disk image)
            'application/x-iso9660-image',          // .iso
            'application/x-cd-image',               // .iso (variant)
            'application/x-archive',                // .ar (Unix archive)
            'application/vnd.ms-cab-compressed',    // .cab (Windows Cabinet archive)
        ];
    }

    /**
     * @param Artifact|string $entity
     * @return bool
     */
    public function isArchive(Artifact|string $entity): bool
    {
        $mimeTypeOptions = $this->getAudioMimeTypes();
        $mimeType = $entity->mime_type ?? $entity;

        return in_array($mimeType, $mimeTypeOptions);
    }

    /**
     * @param string $limitToApplication
     * @return array
     */
    public function getMicrosoftOfficeMimeTypes(string $limitToApplication = ''): array
    {
        $officeMimeTypes = [
            'Word' => [
                'doc' => 'application/msword',
                'dot' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
                'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            ],
            'Excel' => [
                'xls' => 'application/vnd.ms-excel',
                'xlt' => 'application/vnd.ms-excel',
                'xla' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
                'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
                'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
                'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            ],
            'PowerPoint' => [
                'ppt' => 'application/vnd.ms-powerpoint',
                'pot' => 'application/vnd.ms-powerpoint',
                'pps' => 'application/vnd.ms-powerpoint',
                'ppa' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
                'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
                'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
                'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
                'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            ],
            'Access' => [
                'mdb' => 'application/vnd.ms-access',
                'accdb' => 'application/vnd.ms-access',
            ],
            'Visio' => [
                'vsd' => 'application/vnd.visio',
                'vst' => 'application/vnd.visio',
                'vss' => 'application/vnd.visio',
                'vsw' => 'application/vnd.visio',
                'vsdx' => 'application/vnd.ms-visio.drawing',
                'vsdm' => 'application/vnd.ms-visio.drawing.macroEnabled.12',
                'vstx' => 'application/vnd.ms-visio.template',
                'vstm' => 'application/vnd.ms-visio.template.macroEnabled.12',
                'vssx' => 'application/vnd.ms-visio.stencil',
                'vssm' => 'application/vnd.ms-visio.stencil.macroEnabled.12',
            ],
            'Publisher' => [
                'pub' => 'application/x-mspublisher',
            ],
            'Project' => [
                'mpp' => 'application/vnd.ms-project',
                'mpt' => 'application/vnd.ms-project',
            ],
            'Outlook' => [
                'msg' => 'application/vnd.ms-outlook',
            ],
            'Other' => [
                'thmx' => 'application/vnd.ms-officetheme',
                'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
                'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            ],
        ];

        $types = [];
        foreach ($officeMimeTypes as $application => $mimeTypes) {
            foreach ($mimeTypes as $mimeType) {
                if ($limitToApplication) {
                    if (strtolower($limitToApplication) === strtolower($application)) {
                        $types[] = $mimeType;
                    }
                } else {
                    $types[] = $mimeType;
                }
            }
        }

        return $types;
    }


    /**
     * @param mixed $blob
     * @param bool $logWarnings
     * @return bool
     */
    public function isBlobDataSafe(mixed $blob, bool $logWarnings = true): bool
    {
        $whitelist = $this->getSafeMimeTypes();

        $mimeType = $this->getMimeTypeFromBlob($blob);

        $isSafe = in_array(strtolower($mimeType), $whitelist);

        if (!$isSafe && $logWarnings) {
            $Auditor = new Auditor();
            $message = __("Detected unsafe data [{0}]", $mimeType);
            $this->addWarningAlerts($message);
            $Auditor->auditWarning($message);
        }

        return $isSafe;
    }


    /**
     * @param mixed $blob
     * @param string|array $group
     * @param bool $logWarnings
     * @return bool
     */
    public function isBlobInMimeTypeGroup(mixed $blob, string|array $group, bool $logWarnings = true): bool
    {
        $whitelist = $this->getSafeMimeTypes();

        $mimeType = $this->getMimeTypeFromBlob($blob);

        $isGroup = in_array(strtolower($mimeType), $whitelist);

        if (!$isGroup && $logWarnings) {
            $Auditor = new Auditor();
            $message = __("Detected unsafe data [{0}]", $mimeType);
            $this->addWarningAlerts($message);
            $Auditor->auditWarning($message);
        }

        return $isGroup;
    }

    /**
     * @param mixed $blob
     * @return false|string
     */
    public function getMimeTypeFromBlob(mixed $blob): bool|string
    {
        if ($this->isStringJson($blob)) {
            return 'application/json';
        }

        if ($this->isStringXml($blob)) {
            return 'application/xml';
        }

        $detector = new FinfoMimeTypeDetector();

        return $detector->detectMimeTypeFromBuffer($blob);
    }

    /**
     * @param mixed $blob
     * @return false|string
     */
    public function getExtensionFromBlob(mixed $blob): bool|string
    {
        $mimeType = $this->getMimeTypeFromBlob($blob);

        return $this->getExtensionFromMimeType($mimeType);
    }

    /**
     * @param $mime
     * @return false|string
     */
    public function getExtensionFromMimeType($mime): false|string
    {
        $mime_map = $this->getMimeTypesMap();

        return isset($mime_map[$mime]) === true ? $mime_map[$mime] : false;
    }

    public function getMimeTypeFromExtension(string $text): false|string
    {
        $mime_map = $this->getMimeTypesMap();
        $mime_map = array_flip($mime_map);

        return isset($mime_map[$text]) === true ? $mime_map[$text] : false;
    }

    /**
     * @return array
     */
    public function getMimeTypesMap(): array
    {
        return [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'application/x-compressed' => '7zip',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'application/postscript' => 'ai',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'application/macbinary' => 'bin',
            'application/mac-binary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/bmp' => 'bmp',
            'image/x-bmp' => 'bmp',
            'image/x-bitmap' => 'bmp',
            'image/x-xbitmap' => 'bmp',
            'image/x-win-bitmap' => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/ms-bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'application/bmp' => 'bmp',
            'application/x-bmp' => 'bmp',
            'application/x-win-bitmap' => 'bmp',
            'application/cdr' => 'cdr',
            'application/coreldraw' => 'cdr',
            'application/x-cdr' => 'cdr',
            'application/x-coreldraw' => 'cdr',
            'image/cdr' => 'cdr',
            'image/x-cdr' => 'cdr',
            'zz-application/zz-winassoc-cdr' => 'cdr',
            'application/mac-compactpro' => 'cpt',
            'application/pkix-crl' => 'crl',
            'application/pkcs-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'application/pkix-cert' => 'crt',
            'text/css' => 'css',
            'text/x-comma-separated-values' => 'csv',
            'text/comma-separated-values' => 'csv',
            'application/vnd.msexcel' => 'csv',
            'application/x-director' => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'message/rfc822' => 'eml',
            'application/x-msdownload' => 'exe',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gpg-keys' => 'gpg',
            'application/x-gtar' => 'gtar',
            'application/x-gzip' => 'gzip',
            'application/mac-binhex40' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'image/x-ico' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'text/calendar' => 'ics',
            'application/java-archive' => 'jar',
            'application/x-java-application' => 'jar',
            'application/x-jar' => 'jar',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'application/x-javascript' => 'js',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.google-earth.kml+xml' => 'kml',
            'application/vnd.google-earth.kmz' => 'kmz',
            'text/x-log' => 'log',
            'audio/x-m4a' => 'm4a',
            'application/vnd.mpegurl' => 'm4u',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/oda' => 'oda',
            'application/vnd.oasis.opendocument.text' => 'odt',
            'application/vnd.oasis.opendocument.spreadsheet' => 'ods',
            'application/vnd.oasis.opendocument.presentation' => 'odp',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'application/x-pkcs10' => 'p10',
            'application/pkcs10' => 'p10',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/pkcs7-mime' => 'p7c',
            'application/x-pkcs7-mime' => 'p7c',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'application/x-x509-user-cert' => 'pem',
            'application/x-pem-file' => 'pem',
            'application/pgp' => 'pgp',
            'application/x-httpd-php' => 'php',
            'application/php' => 'php',
            'application/x-php' => 'php',
            'text/php' => 'php',
            'text/x-php' => 'php',
            'application/x-httpd-php-source' => 'php',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'application/x-rar' => 'rar',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'application/x-pkcs7' => 'rsa',
            'text/rtf' => 'rtf',
            'text/richtext' => 'rtx',
            'video/vnd.rn-realvideo' => 'rv',
            'application/x-stuffit' => 'sit',
            'application/smil' => 'smil',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/x-gzip-compressed' => 'tgz',
            'image/tiff' => 'tiff',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'text/vtt' => 'vtt',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/wbxml' => 'wbxml',
            'video/webm' => 'webm',
            'audio/x-ms-wma' => 'wma',
            'application/wmlc' => 'wmlc',
            'video/x-ms-wmv' => 'wmv',
            'video/x-ms-asf' => 'wmv',
            'application/xhtml+xml' => 'xhtml',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'text/xsl' => 'xsl',
            'application/xspf+xml' => 'xspf',
            'application/x-compress' => 'z',
            'application/x-zip' => 'zip',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/s-compressed' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-scriptzsh' => 'zsh',
        ];
    }

    /**
     * @param string $string
     * @return bool
     */
    public function isStringJson(string $string): bool
    {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * @param string $string
     * @return bool
     */
    public function isStringXml(string $string): bool
    {
        libxml_use_internal_errors(true);  // Suppress XML errors
        $xml = simplexml_load_string($string);
        return ($xml !== false);
    }

    /**
     * Make a random name
     *
     * @return string
     */

    public function makeRandomName(): string
    {
        $names = file_get_contents(ROOT . DS . 'bin' . DS . 'BackgroundServices' . DS . 'names.txt');
        $names = str_replace(["\r\n", "\r"], "\n", $names);
        $names = explode("\n", $names);
        $firstNames = [];
        $lastNames = [];

        foreach ($names as $name) {
            $name = explode(" ", $name);

            if (isset($name[0])) {
                $firstNames[] = $name[0];
            }

            if (isset($name[1])) {
                $lastNames[] = $name[1];
            }
        }

        $firstNames = array_values(array_unique($firstNames));
        $lastNames = array_values(array_unique($lastNames));

        $f = $firstNames[mt_rand(0, count($firstNames) - 1)];
        $l = $lastNames[mt_rand(0, count($lastNames) - 1)];
        $name = trim($f . " " . $l);

        return $name;
    }

    /**
     * Extract from the header or the query-string
     *
     * @param ServerRequest $request
     * @return string|false
     */
    public function extractBearerTokenFromRequest(ServerRequest $request): string|false
    {
        //check header
        $headers = $request->getHeaders();
        if (isset($headers['Authorization'])) {
            $authorizationHeaders = $headers['Authorization'];
            if (is_string($authorizationHeaders)) {
                $authorizationHeaders = [$authorizationHeaders];
            }
            foreach ($authorizationHeaders as $authorizationHeader) {
                if (str_contains($authorizationHeader, "Bearer")) {
                    if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
                        return $matches[1];
                    }
                }
            }
        }

        //check query-string
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['bearerToken'])) {
            return $queryParams['bearerToken'];
        } elseif (isset($queryParams['BearerToken'])) {
            return $queryParams['BearerToken'];
        } elseif (isset($queryParams['bearertoken'])) {
            return $queryParams['bearertoken'];
        }

        return false;
    }
}
