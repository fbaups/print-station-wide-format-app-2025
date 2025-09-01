<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\ScheduledTask;
use App\ScheduledTaskWorkflows\Base\CronExpression;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use ReflectionClass;

/**
 * ScheduledTasks Model
 *
 * @method \App\Model\Entity\ScheduledTask newEmptyEntity()
 * @method \App\Model\Entity\ScheduledTask newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ScheduledTask> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ScheduledTask get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ScheduledTask findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ScheduledTask patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ScheduledTask> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ScheduledTask saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ScheduledTask>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ScheduledTask>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ScheduledTask>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ScheduledTask> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ScheduledTask>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ScheduledTask>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ScheduledTask>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ScheduledTask> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ScheduledTasksTable extends AppTable
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

        $this->setTable('scheduled_tasks');
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
            ->scalar('name')
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 512)
            ->allowEmptyString('description');

        $validator
            ->boolean('is_enabled')
            ->allowEmptyString('is_enabled');

        $validator
            ->scalar('schedule')
            ->maxLength('schedule', 64)
            ->allowEmptyString('schedule');

        $validator
            ->scalar('workflow')
            ->maxLength('workflow', 50)
            ->allowEmptyString('workflow');

        $validator
            ->scalar('parameters')
            ->allowEmptyString('parameters');

        $validator
            ->dateTime('next_run_time')
            ->allowEmptyDateTime('next_run_time');

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
     * @return array
     */
    public function updateNextRunTimes(): array
    {
        /** @var ScheduledTask[] $scheduledTasks */
        $scheduledTasks = $this->find('all');

        $results = [];
        foreach ($scheduledTasks as $scheduledTask) {
            //update the next_run_time
            $scheduledTask = $this->updateNextRunTime($scheduledTask);

            //persist to DB
            if ($this->save($scheduledTask)) {
                $results[$scheduledTask->id] = true;
            } else {
                $results[$scheduledTask->id] = false;
            }
        }

        return $results;
    }

    /**
     * Formats data before saving.
     *
     * @param \Cake\Datasource\EntityInterface|ScheduledTask $entity
     * @param array $options
     * @return false|\Cake\Datasource\EntityInterface
     */
    public function save(\Cake\Datasource\EntityInterface|ScheduledTask $entity, array $options = []): false|\Cake\Datasource\EntityInterface
    {
        $entity = $this->updateNextRunTime($entity, false);

        return parent::save($entity, $options);
    }

    /**
     * Updates the next_run_time in the entity and persists to DB by default
     *
     * @param ScheduledTask $entity
     * @param bool $save
     * @return ScheduledTask
     */
    public function updateNextRunTime(ScheduledTask $entity, bool $save = true): ScheduledTask
    {
        $currentNextRunTime = $entity->next_run_time;
        $schedule = $entity->schedule;
        $cron = new CronExpression($schedule, LCL_TZ);
        $isValid = $cron->isValid();
        if ($isValid) {
            $nextRun = $cron->getNext();
            $nextRun = new DateTime($nextRun);
            $nextRun = $nextRun->setTimezone('UTC');
        } else {
            $nextRun = null;
        }
        $entity->next_run_time = $nextRun;

        if ($save && $entity->next_run_time->notEquals($currentNextRunTime)) {
            $this->save($entity);
        }

        return $entity;
    }

    /**
     * Get a list of all the workflow classes (hence workflows)
     *
     * @return array
     */
    public function getWorkflowClasses(): array
    {
        $storagePath = APP . 'ScheduledTaskWorkflows\\';

        $files = [];
        $folders = [];
        if (is_dir($storagePath)) {
            $adapter = new LocalFilesystemAdapter($storagePath);
            $fs = new Filesystem($adapter);
            try {
                $listing = $fs->listContents('', false);
                foreach ($listing as $item) {
                    $path = $item->path();
                    if ($item instanceof FileAttributes) {
                        $files[] = $item->path();
                    } elseif ($item instanceof DirectoryAttributes) {
                        $folders[] = $item->path();
                    }
                }
            } catch (\Throwable $exception) {
            }
        }

        $workflowList = [];
        foreach ($files as $file) {
            $file = pathinfo($file, PATHINFO_FILENAME);
            $className = "\\App\\ScheduledTaskWorkflows\\{$file}";
            $class = new $className();

            try {
                $class = new ReflectionClass($class);
                $methods = $class->getMethods();
                foreach ($methods as $method) {
                    if ($method->getName() === 'execute') {
                        $workflowList[$className] = $file;
                    }
                };
            } catch (\Throwable $exception) {

            }
        }

        asort($workflowList);

        return $workflowList;
    }

    /**
     * @return ScheduledTask[]
     */
    public function getEnabledScheduledTasksKeyedById(): array
    {
        $currentDate = new DateTime();

        /** @var ScheduledTask[] $scheduledTasks */
        $scheduledTasks = $this->find('all')
            ->where(["OR" => ["COALESCE(activation, '1970-01-01 00:00:00') <=" => $currentDate->format("Y-m-d H:i:s"), "activation IS NULL"]])
            ->where(["OR" => ["COALESCE(expiration, '9999-12-31 23:59:59') >=" => $currentDate->format("Y-m-d H:i:s"), "expiration IS NULL"]])
            ->where(['is_enabled' => true]);

        $keyed = [];
        foreach ($scheduledTasks as $scheduledTask) {
            $keyed[$scheduledTask->id] = $scheduledTask;
        }

        return $keyed;
    }
}
