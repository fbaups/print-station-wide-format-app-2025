<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\CodeWatcherFile;
use App\Model\Entity\CodeWatcherProject;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CodeWatcherProjects Model
 *
 * @property \App\Model\Table\CodeWatcherFoldersTable&\Cake\ORM\Association\HasMany $CodeWatcherFolders
 *
 * @method \App\Model\Entity\CodeWatcherProject newEmptyEntity()
 * @method \App\Model\Entity\CodeWatcherProject newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CodeWatcherProject> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CodeWatcherProject get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CodeWatcherProject findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CodeWatcherProject patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CodeWatcherProject> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CodeWatcherProject|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CodeWatcherProject saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherProject>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherProject>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherProject>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherProject> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherProject>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherProject>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CodeWatcherProject>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CodeWatcherProject> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CodeWatcherProjectsTable extends AppTable
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

        $this->setTable('code_watcher_projects');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('CodeWatcherFolders', [
            'foreignKey' => 'code_watcher_project_id',
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 850)
            ->allowEmptyString('description');

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
            ->boolean('enable_tracking')
            ->allowEmptyString('enable_tracking');

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
     * @param int|CodeWatcherProject $idOrEntity
     * @return Query\SelectQuery|CodeWatcherFile[]
     */
    public function findFilesInProject(int|CodeWatcherProject $idOrEntity): Query\SelectQuery
    {
        $projectId = $this->asId($idOrEntity);

        $folders = $this->CodeWatcherFolders->find('all')
            ->select(['id'], true)
            ->where(['code_watcher_project_id' => $projectId]);

        $files = $this->CodeWatcherFolders->CodeWatcherFiles->find('all')
            ->where(['code_watcher_folder_id IN' => $folders]);

        return $files;
    }

    /**
     * @param int|CodeWatcherProject $idOrEntity
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return int
     */
    public function sumActivityInTimeRange(int|CodeWatcherProject $idOrEntity, DateTime $start = null, DateTime $end = null): int
    {
        $activity = $this->getDailyActivityInTimeRange($idOrEntity, $start, $end);

        $sum = 0;
        foreach ($activity as $data) {
            $sum += $data['time'];
        }

        return $sum;
    }

    /**
     * @param int|CodeWatcherProject $idOrEntity
     * @param DateTime|null $start
     * @param DateTime|null $end
     * @return array
     */
    public function getDailyActivityInTimeRange(int|CodeWatcherProject $idOrEntity, DateTime $start = null, DateTime $end = null, $asRawData = false): array
    {
        $id = $this->asId($idOrEntity);

        if (!$start) {
            $start = (new DateTime())->setTimezone(LCL_TZ)->startOfMonth();
        }
        if (!$end) {
            $end = (new DateTime())->setTimezone(LCL_TZ)->endOfMonth();
        }

        $query = $this->findFilesInProject($id);
        $query->select([
            'time_grouping_key',
            'local_year' => 'max(local_year)',
            'local_month' => 'max(local_month)',
            'local_day' => 'max(local_day)',
            'local_hour' => 'max(local_hour)',
            'local_minute' => 'max(local_minute)',
            'local_second' => 'max(local_second)',
        ])
            ->groupBy(['time_grouping_key'])
            ->orderBy(['time_grouping_key'])
            ->disableHydration();

        if ($start) {
            $query->where([
                'local_year >=' => $start->year,
                'local_month >=' => $start->month,
                'local_day >=' => $start->day,
            ]);
        }

        if ($end) {
            $query->where([
                'local_year <=' => $end->year,
                'local_month <=' => $end->month,
                'local_day <=' => $end->day,
            ]);
        }

        $rawData = [];
        $dailyActivity = [];

        $rollingDate = (clone $start);
        $counter = 0;
        while ($rollingDate->lessThanOrEquals($end) && $counter <= 35) {
            $dayTmp = $rollingDate->format("Y") . "-" . $rollingDate->format("m") . "-" . $rollingDate->format("d");
            $dailyActivity[$dayTmp] = ['hits' => 0, 'time' => 0];

            //dump($rollingDate->day);
            $rollingDate = $rollingDate->addDays(1);
            $counter++;
        }

        $timeBracket = 5; //we look at brackets of 5 mins
        foreach ($query->toArray() as $record) {
            $dayTmp = "{$record['local_year']}-{$record['local_month']}-{$record['local_day']}";

            //condensed data
            if (!isset($dailyActivity[$dayTmp])) {
                $dailyActivity[$dayTmp] = ['hits' => 1, 'time' => 1 * $timeBracket];
            } else {
                $dailyActivity[$dayTmp]['hits']++;
                $dailyActivity[$dayTmp]['time'] = $dailyActivity[$dayTmp]['hits'] * $timeBracket;
            }

            //raw data
            $rawData[] = $record['time_grouping_key'];
        }

        if ($asRawData) {
            return $rawData;
        }

        return $dailyActivity;
    }

}
