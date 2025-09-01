<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Log\Engine\Auditor;
use App\Model\Entity\DataBlob;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * DataBlobs Model
 *
 * @method \App\Model\Entity\DataBlob newEmptyEntity()
 * @method \App\Model\Entity\DataBlob newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\DataBlob> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\DataBlob get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\DataBlob findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\DataBlob patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\DataBlob> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\DataBlob|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\DataBlob saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\DataBlob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DataBlob>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DataBlob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DataBlob> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DataBlob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DataBlob>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\DataBlob>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\DataBlob> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DataBlobsTable extends AppTable
{
    use LocatorAwareTrait;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('data_blobs');
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
            ->dateTime('activation')
            ->allowEmptyDateTime('activation');

        $validator
            ->dateTime('expiration')
            ->allowEmptyDateTime('expiration');

        $validator
            ->boolean('auto_delete')
            ->allowEmptyString('auto_delete');

        $validator
            ->scalar('grouping')
            ->maxLength('grouping', 50)
            ->allowEmptyString('grouping');

        $validator
            ->scalar('blob')
            ->allowEmptyString('blob');

        $validator
            ->scalar('hash_sum')
            ->maxLength('hash_sum', 50)
            ->allowEmptyString('hash_sum');

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

    public function saveDataBlob(ServerRequest $request, string $grouping = null): bool
    {
        $Auditor = new Auditor();

        //extract from the request
        $headers = $request->getHeaders();
        $queryParams = $request->getQueryParams();
        unset($queryParams['BearerToken'], $queryParams['bearerToken'], $queryParams['bearertoken']);
        $parsedData = $request->getData();
        $rawData = $request->getBody()->getContents();

        if (!$rawData && !$parsedData) {
            $this->addDangerAlerts(__("No data was supplied that could be saved."));
            return false;
        }

        if ($rawData) {
            $blobToSave = $rawData;
        } else {
            $blobToSave = json_encode($parsedData, JSON_PRETTY_PRINT);
        }

        $isSafe = $this->isBlobDataSafe($blobToSave);
        if (!$isSafe) {
            $this->addDangerAlerts(__("No safe data was supplied that could be saved."));
            return false;
        }

        $mimeTypeFromBlob = $this->getMimeTypeFromBlob($blobToSave);

        $dataBlob = $this->newEmptyEntity();
        $dataBlob = $this->patchEntity($dataBlob, $this->getDefaultData());

        $dataBlob->grouping = $grouping;
        $dataBlob->format = $mimeTypeFromBlob;
        $dataBlob->blob = $blobToSave;
        $dataBlob->hash_sum = sha1(serialize($blobToSave));

        if ($this->save($dataBlob)) {
            $this->addSuccessAlerts(__("Saved {0} data blob.", $mimeTypeFromBlob));
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    private function getDefaultData(): array
    {
        $timeObjCurrent = new DateTime();
        $months = intval(Configure::read("Settings.repo_purge"));

        return [
            'created' => (clone $timeObjCurrent),
            'modified' => (clone $timeObjCurrent),
            'activation' => (clone $timeObjCurrent),
            'expiration' => (clone $timeObjCurrent)->addMonths($months),
            'auto_delete' => true,
            'grouping' => null,
            'format' => null,
            'blob' => null,
            'hash_sum' => null,
        ];
    }
}
