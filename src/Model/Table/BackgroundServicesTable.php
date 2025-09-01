<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\BackgroundService;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * BackgroundServices Model
 *
 * @method BackgroundService newEmptyEntity()
 * @method BackgroundService newEntity(array $data, array $options = [])
 * @method BackgroundService[] newEntities(array $data, array $options = [])
 * @method BackgroundService get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method BackgroundService findOrCreate($search, ?callable $callback = null, $options = [])
 * @method BackgroundService patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method BackgroundService[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method BackgroundService|false save(EntityInterface $entity, $options = [])
 * @method BackgroundService saveOrFail(EntityInterface $entity, $options = [])
 * @method BackgroundService[]|ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method BackgroundService[]|ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method BackgroundService[]|ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method BackgroundService[]|ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin TimestampBehavior
 */
class BackgroundServicesTable extends AppTable
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

        $this->setTable('background_services');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->initializeSchemaJsonFields($this->getJsonFields());
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
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
            ->scalar('type')
            ->maxLength('type', 128)
            ->allowEmptyString('type');

        $validator
            ->integer('pid')
            ->allowEmptyString('pid');

        $validator
            ->scalar('current_state')
            ->maxLength('current_state', 128)
            ->allowEmptyString('current_state');

        $validator
            ->dateTime('appointment_date')
            ->allowEmptyDateTime('appointment_date');

        $validator
            ->dateTime('retirement_date')
            ->allowEmptyDateTime('retirement_date');

        $validator
            ->dateTime('termination_date')
            ->allowEmptyDateTime('termination_date');

        $validator
            ->boolean('force_recycle')
            ->allowEmptyString('force_recycle');

        $validator
            ->boolean('force_shutdown')
            ->allowEmptyString('force_shutdown');

        $validator
            ->integer('errand_link')
            ->allowEmptyString('errand_link');

        $validator
            ->scalar('errand_name')
            ->maxLength('errand_name', 128)
            ->allowEmptyString('errand_name');

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
     * @param string $type errand||message||hot_folder||database_purger
     * @param array $options
     * @return bool|BackgroundService
     */
    public function createBackgroundServiceEntry(string $type, array $options): BackgroundService|bool
    {
        $allowed = ['errand', 'message', 'hot_folder', 'scheduled_task', 'database_purger'];

        $type = strtolower($type);
        if (!in_array($type, $allowed)) {
            return false;
        }

        //create some defaults based on Settings
        $backgroundServiceLifeExpectancy = intval(Configure::read("Settings.{$type}_background_service_life_expectancy"));
        $backgroundServiceGracePeriod = intval(Configure::read("Settings.{$type}_background_service_grace_period"));

        $timeObjAppointment = new DateTime('now');

        $timeObjRetirement = new DateTime('now');
        $timeObjRetirement = $timeObjRetirement->addMinutes($backgroundServiceLifeExpectancy);

        $timeObjTermination = new DateTime('now');
        $timeObjTermination = $timeObjTermination->addMinutes($backgroundServiceLifeExpectancy + $backgroundServiceGracePeriod);

        $defaultOptions = [
            'server' => gethostname(),
            'domain' => parse_url(Router::url("/", true), PHP_URL_HOST),
            'name' => '',
            'type' => '',
            'pid' => getmypid(),
            'current_state' => null,
            'appointment_date' => $timeObjAppointment,
            'retirement_date' => $timeObjRetirement,
            'termination_date' => $timeObjTermination,
            'force_recycle' => false,
            'force_shutdown' => false,
            'errand_link' => null,
            'errand_name' => null,
        ];
        $options = array_merge($defaultOptions, $options);

        $backgroundService = $this->newEntity($options);

        $backgroundService = $this->save($backgroundService);

        return $backgroundService;
    }

    /**
     * @param $name
     * @return EntityInterface|BackgroundService|null
     */
    public function getBackgroundServiceByName($name): EntityInterface|BackgroundService|null
    {
        return $this->findByNameOrAlias($name)->first();
    }


    /**
     * Get a list of the Background Services indexed by name
     *
     * @return array
     */
    public function getBackgroundServices(): array
    {
        $return = [];

        /** @var BackgroundService[] $backgroundServices */
        $backgroundServices = $this->find('all')->limit(200)->orderByDesc('id');
        foreach ($backgroundServices as $backgroundService) {
            $return[$backgroundService->name] = $backgroundService;
        }

        return $return;
    }


    /**
     * Get a list of the possible thread numbers for the given $type
     *
     * @param $type
     * @return array
     */
    public function getThreadNumbers($type): array
    {
        $return[0] = [0]; //zero is always a valid thread number

        /** @var BackgroundService[] $backgroundServices */
        $backgroundServices = $this->find('all')
            ->where(['type' => $type]);
        foreach ($backgroundServices as $backgroundService) {
            $threadNumber = intval(preg_replace('/[^0-9]/', '', $backgroundService->name));
            $return[$threadNumber] = $threadNumber;
        }

        return $return;
    }


    /**
     * Check if a Background Service is flag to recycle or shutdown
     *
     * @param string $heartbeatContext
     * @return bool
     */
    public function isRecycleOrShutdown(string $heartbeatContext): bool
    {
        $backgroundService = $this->getBackgroundServiceByName($heartbeatContext);

        if ($backgroundService->force_recycle) {
            return true;
        }

        if ($backgroundService->force_shutdown) {
            return true;
        }

        return false;
    }

    /**
     * Flag to recycle
     *
     * @param string $heartbeatContext
     * @return bool|BackgroundService
     */
    public function flagRecycle(string $heartbeatContext): bool|BackgroundService
    {
        $backgroundService = $this->getBackgroundServiceByName($heartbeatContext);

        if (!$backgroundService) {
            return false;
        }

        $backgroundService->force_recycle = true;

        return $this->save($backgroundService);
    }

    /**
     * Flag to recycle
     *
     * @param string $heartbeatContext
     * @return bool|BackgroundService
     */
    public function flagShutdown(string $heartbeatContext): bool|BackgroundService
    {
        $backgroundService = $this->getBackgroundServiceByName($heartbeatContext);

        if (!$backgroundService) {
            return false;
        }

        $backgroundService->force_shutdown = true;

        return $this->save($backgroundService);
    }


    /**
     * Resets the Background Service entry to null
     *
     * @param BackgroundService $backgroundService
     * @return bool|BackgroundService|EntityInterface
     */
    public function cleanup(BackgroundService $backgroundService): bool|BackgroundService|EntityInterface
    {
        $backgroundService->appointment_date = null;
        $backgroundService->retirement_date = null;
        $backgroundService->termination_date = null;
        $backgroundService->pid = null;
        $backgroundService->current_state = 'stopped';
        $backgroundService->force_recycle = null;
        $backgroundService->force_shutdown = null;

        return $this->save($backgroundService);
    }
}
