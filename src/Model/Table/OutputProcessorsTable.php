<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\OutputProcessor\BackblazeBucketOutputProcessor;
use App\OutputProcessor\EpsonPrintAutomateOutputProcessor;
use App\OutputProcessor\FolderOutputProcessor;
use App\OutputProcessor\FujifilmXmfPressReadyCsvHotFolderProcessor;
use App\OutputProcessor\FujifilmXmfPressReadyPdfHotFolderProcessor;
use App\OutputProcessor\OutputProcessorBase;
use App\OutputProcessor\sFTPOutputProcessor;
use App\VendorIntegrations\Fujifilm\PressReady;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Xml;
use Cake\Validation\Validator;
use League\Flysystem\Filesystem;

/**
 * OutputProcessors Model
 *
 * @method \App\Model\Entity\OutputProcessor newEmptyEntity()
 * @method \App\Model\Entity\OutputProcessor newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\OutputProcessor> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OutputProcessor get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\OutputProcessor findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\OutputProcessor patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\OutputProcessor> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\OutputProcessor|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\OutputProcessor saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\OutputProcessor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OutputProcessor>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OutputProcessor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OutputProcessor> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OutputProcessor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OutputProcessor>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\OutputProcessor>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\OutputProcessor> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OutputProcessorsTable extends AppTable
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

        $this->setTable('output_processors');
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
            ->scalar('type')
            ->maxLength('type', 50)
            ->allowEmptyString('type');

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
            ->scalar('parameters')
            ->allowEmptyString('parameters');

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
            'parameters'
        ];

        return $jsonFields;
    }

    /**
     * @return Query
     */
    public function getActiveList(): Query
    {
        $outputProcessors = $this->find('list')
            ->orderBy(['id'])
            ->where(['is_enabled' => 1]);

        $OP = new OutputProcessorBase();
        if (!$OP->getEpsonExecutablePath()) {
            $outputProcessors = $outputProcessors->where(['type !=' => 'EpsonPrintAutomate']);
        }

        return $outputProcessors;
    }

    public function formatParameters($requestData, $paramsCurrent): array
    {
        $paramsCurrent = empty($paramsCurrent) ? [] : $paramsCurrent;
        $return = [];

        $filenameBuilder = trim($requestData['filename-builder']);
        $filenameBuilder = !empty($filenameBuilder) ? $filenameBuilder : false;

        $common = [
            "filenameBuilder" => $filenameBuilder,
            "filenameOptions" => $requestData['file-naming-options'],
            "prefixOrderId" => asBool($requestData['prefix-order-id']),
            "prefixJobId" => asBool($requestData['prefix-job-id']),
            "prefixDocumentId" => asBool($requestData['prefix-document-id']),
            "prefixExternalOrderNumber" => asBool($requestData['prefix-external-order-number']),
            "prefixExternalJobNumber" => asBool($requestData['prefix-external-job-number']),
            "prefixExternalDocumentNumber" => asBool($requestData['prefix-external-document-number']),
        ];

        if ($requestData['type'] === 'Folder') {
            $OP = new FolderOutputProcessor();
            $paramsDefault = $OP->getDefaultOutputConfiguration();

            $paramsNewRequest = [
                "fso_path" => TextFormatter::makeDirectoryTrailingSmartSlash($requestData['fso-path']),
            ];

            $return = array_merge($paramsDefault, $paramsCurrent, $common, $paramsNewRequest);

            //remove irrelevant keys
            foreach ($return as $key => $value) {
                if (!in_array($key, array_keys($paramsDefault))) {
                    unset($return[$key]);
                }
            }
        }

        if ($requestData['type'] === 'sFTP') {
            $OP = new sFTPOutputProcessor();
            $paramsDefault = $OP->getDefaultOutputConfiguration();

            if ($paramsCurrent['sftp_password'] === $requestData['sftp-password']) {
                $password = $paramsCurrent['sftp_password'];
            } else {
                $password = Security::encrypt64Url($requestData['sftp-password']);
            }

            $paramsNewRequest = [
                "sftp_host" => $requestData['sftp-host'],
                "sftp_port" => $requestData['sftp-port'],
                "sftp_username" => $requestData['sftp-username'],
                "sftp_password" => $password,
                "sftp_timeout" => $requestData['sftp-timeout'],
                "sftp_path" => $requestData['sftp-path'],
            ];

            $return = array_merge($paramsDefault, $paramsCurrent, $common, $paramsNewRequest);

            //remove irrelevant keys
            foreach ($return as $key => $value) {
                if (!in_array($key, array_keys($paramsDefault))) {
                    unset($return[$key]);
                }
            }
        }

        if ($requestData['type'] === 'EpsonPrintAutomate') {
            $OP = new EpsonPrintAutomateOutputProcessor();
            $paramsDefault = $OP->getDefaultOutputConfiguration();

            if ($paramsCurrent['epa_password'] === $requestData['epa-password']) {
                $password = $paramsCurrent['epa_password'];
            } else {
                $password = Security::encrypt64Url($requestData['epa-password']);
            }

            $paramsNewRequest = [
                "epa_preset" => $requestData['epa-preset'],
                "epa_username" => $requestData['epa-username'],
                "epa_password" => $password,
            ];

            $return = array_merge($paramsDefault, $paramsCurrent, $common, $paramsNewRequest);

            //remove irrelevant keys
            foreach ($return as $key => $value) {
                if (!in_array($key, array_keys($paramsDefault))) {
                    unset($return[$key]);
                }
            }
        }

        if ($requestData['type'] === 'BackblazeBucket') {
            $OP = new BackblazeBucketOutputProcessor();
            $paramsDefault = $OP->getDefaultOutputConfiguration();

            if ($paramsCurrent['b2_key'] === $requestData['b2-key']) {
                $b2Key = $paramsCurrent['b2_key'];
            } else {
                $b2Key = Security::encrypt64Url($requestData['b2-key']);
            }

            $paramsNewRequest = [
                "b2_key_id" => $requestData['b2-key-id'],
                "b2_key" => $b2Key,
                "b2_bucket" => $requestData['b2-bucket'],
                "b2_path" => $requestData['b2-path'],
            ];

            $return = array_merge($paramsDefault, $paramsCurrent, $common, $paramsNewRequest);

            //remove irrelevant keys
            foreach ($return as $key => $value) {
                if (!in_array($key, array_keys($paramsDefault))) {
                    unset($return[$key]);
                }
            }
        }

        if ($requestData['type'] === 'FujifilmXmfPressReadyPdfHotFolder') {
            $OP = new FujifilmXmfPressReadyPdfHotFolderProcessor();
            $paramsDefault = $OP->getDefaultOutputConfiguration();

            $PressReady = new PressReady();
            $pressReadyPdfHotFoldersGroup = $PressReady->getPdfHotFoldersOptionsList();

            $hotFolderList = [];
            foreach ($pressReadyPdfHotFoldersGroup as $groupName => $pressReadyPdfHotFolders) {
                $hotFolderList = array_merge($hotFolderList, array_keys($pressReadyPdfHotFolders));
            }

            $paramsNewRequest = $requestData;
            if (!in_array($requestData['pr-pdf-hf-id-wf-id'], $hotFolderList)) {
                $paramsNewRequest = [
                    "pr-pdf-hf-id-wf-id" => null,
                    "pr-pdf-hf-id" => null,
                    "pr-pdf-wf-id" => null,
                    "pr-pdf-hf-path" => null,
                ];
            } else {
                $split = explode("-", $paramsNewRequest['pr-pdf-hf-id-wf-id']);
                //generate some extra data
                $paramsNewRequest["pr-pdf-hf-id"] = $split[0];
                $paramsNewRequest["pr-pdf-wf-id"] = $split[1];
                $paramsNewRequest["pr-pdf-hf-path"] = null;
            }

            $return = array_merge($paramsDefault, $paramsCurrent, $common, $paramsNewRequest);

            //remove irrelevant keys
            foreach ($return as $key => $value) {
                if (!in_array($key, array_keys($paramsDefault))) {
                    unset($return[$key]);
                }
            }
        }

        if ($requestData['type'] === 'FujifilmXmfPressReadyCsvHotFolder') {
            $OP = new FujifilmXmfPressReadyCsvHotFolderProcessor();
            $paramsDefault = $OP->getDefaultOutputConfiguration();

            $PressReady = new PressReady();
            $pressReadyCsvHotFoldersGroup = $PressReady->getCsvHotFoldersOptionsList();

            $hotFolderList = [];
            foreach ($pressReadyCsvHotFoldersGroup as $groupName => $pressReadyCsvHotFolders) {
                $hotFolderList = array_merge($hotFolderList, array_keys($pressReadyCsvHotFolders));
            }

            $paramsNewRequest = $requestData;
            if (!in_array($requestData['pr-csv-hf-id-wf-id'], $hotFolderList)) {
                $paramsNewRequest = [
                    "pr-csv-hf-id-wf-id" => null,
                    "pr-csv-hf-id" => null,
                    "pr-csv-wf-id" => null,
                    "pr-csv-hf-path" => null,
                ];
            } else {
                $split = explode("-", $paramsNewRequest['pr-csv-hf-id-wf-id']);
                //generate some extra data
                $paramsNewRequest["pr-csv-hf-id"] = $split[0];
                $paramsNewRequest["pr-csv-wf-id"] = $split[1];
                $paramsNewRequest["pr-csv-hf-path"] = null;
            }

            $return = array_merge($paramsDefault, $paramsCurrent, $common, $paramsNewRequest);

            //remove irrelevant keys
            foreach ($return as $key => $value) {
                if (!in_array($key, array_keys($paramsDefault))) {
                    unset($return[$key]);
                }
            }
        }

        return $return;
    }
}
