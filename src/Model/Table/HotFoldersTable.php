<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Log\Engine\Auditor;
use App\Model\Entity\HotFolder;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\Http\ServerRequest;
use Cake\I18n\DateTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;
use Laminas\Diactoros\UploadedFile;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use ReflectionClass;

/**
 * HotFolders Model
 * @property \App\Model\Table\HotFolderEntriesTable&\Cake\ORM\Association\HasMany $HotFolderEntries
 *
 * @method \App\Model\Entity\HotFolder newEmptyEntity()
 * @method \App\Model\Entity\HotFolder newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\HotFolder[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\HotFolder get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\HotFolder findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\HotFolder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\HotFolder[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\HotFolder|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\HotFolder saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\HotFolder[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\HotFolder[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\HotFolder[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\HotFolder[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class HotFoldersTable extends AppTable
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

        $this->setTable('hot_folders');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasOne('HotFolderEntries', [
            'foreignKey' => 'hot_folder_id',
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
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('submit_url')
            ->maxLength('submit_url', 50)
            ->allowEmptyString('submit_url');

        $validator
            ->boolean('submit_url_enabled')
            ->allowEmptyString('submit_url_enabled');

        $validator
            ->scalar('description')
            ->maxLength('description', 512)
            ->allowEmptyString('description');

        $validator
            ->scalar('path')
            ->maxLength('path', 512)
            ->allowEmptyString('path');

        $validator
            ->boolean('is_enabled')
            ->allowEmptyString('is_enabled');

        $validator
            ->scalar('workflow')
            ->maxLength('workflow', 50)
            ->allowEmptyString('workflow');

        $validator
            ->scalar('parameters')
            ->allowEmptyString('parameters');

        $validator
            ->dateTime('next_polling_time')
            ->allowEmptyDateTime('next_polling_time');

        $validator
            ->integer('polling_interval')
            ->allowEmptyString('polling_interval');

        $validator
            ->integer('stable_interval')
            ->allowEmptyString('stable_interval');

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
            ->boolean('auto_clear_entries')
            ->allowEmptyString('auto_clear_entries');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name']), ['errorField' => 'name']);

        return $rules;
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
     * @return HotFolder[]
     */
    public function getAllHotFoldersKeyedById(): array
    {
        /** @var HotFolder[] $hotFolders */
        $hotFolders = $this->find('all');

        $keyed = [];
        foreach ($hotFolders as $hotFolder) {
            $keyed[$hotFolder->id] = $hotFolder;
        }

        return $keyed;
    }

    /**
     * @param $submitUrl
     * @return array|EntityInterface|HotFolder|null
     */
    public function getHotFolderBySubmitUrl($submitUrl): array|\Cake\Datasource\EntityInterface|null|HotFolder
    {
        return $this->find('all')->where(['submit_url' => $submitUrl, 'submit_url_enabled' => true])->first();
    }

    /**
     * @return HotFolder[]
     */
    public function getEnabledHotFoldersKeyedById(): array
    {
        $currentDate = new DateTime();

        /** @var HotFolder[] $hotFolders */
        $hotFolders = $this->find('all')
            ->where(["OR" => ["COALESCE(activation, '1970-01-01 00:00:00') <=" => $currentDate->format("Y-m-d H:i:s"), "activation IS NULL"]])
            ->where(["OR" => ["COALESCE(expiration, '9999-12-31 23:59:59') >=" => $currentDate->format("Y-m-d H:i:s"), "expiration IS NULL"]])
            ->where(['is_enabled' => true]);

        $keyed = [];
        foreach ($hotFolders as $hotFolder) {
            $keyed[$hotFolder->id] = $hotFolder;
        }

        return $keyed;
    }

    /**
     * @return Query
     */
    public function getHotFoldersDropdownList(): Query
    {
        return $this->find('list', keyField: 'id', valueField: 'name')
            ->where(['is_enabled' => true]);
    }

    /**
     * Get a list of all the workflow classes (hence workflows)
     *
     * @return array
     */
    public function getWorkflowClasses(): array
    {
        $storagePath = APP . 'HotFolderWorkflows\\';

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
            $className = "\\App\\HotFolderWorkflows\\{$file}";
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

    public function fixPollingAndStableIntervals()
    {
        $this->updateAll(['polling_interval' => 5], ['polling_interval IS NULL']);
        $this->updateAll(['stable_interval' => 5], ['stable_interval IS NULL']);
    }

    /**
     * Save data/file/s submitted via web hook
     *
     * Order of precedence
     * 1) $request->getQueryParams() If query params are present, will be saved as query.json
     *    The function continues after this.
     *
     * 2) $request->getData(). i.e.the CakePHP Parsed data
     *      a) Data only. Saved as data.json
     *      b) Data + File/s. Folder is created and data.json saved as JSON and files saved
     *    The function returns after this.
     *
     * 3) Raw body. MIME type is checked and the blob saved as defined by the MIME type
     *    The function returns after this.
     *
     * @param mixed $hotFolderName
     * @param ServerRequest $request
     * @return bool
     */
    public function saveSubmission(mixed $hotFolderName, ServerRequest $request): bool
    {
        $Auditor = new Auditor();

        $this->addInfoAlerts(__('Started Processing.'));

        /** @var HotFolder $hotFolder */
        $hotFolder = $this->getHotFolderBySubmitUrl($hotFolderName);
        if (!$hotFolder) {
            $Auditor->auditWarning(__('Hot Folder Web Submission - Invalid Hot Folder Name.'));
            $this->addDangerAlerts(__('Invalid Hot Folder Name.'));
            return false;
        }
        if (!is_dir($hotFolder->path)) {
            $Auditor->auditWarning(__('Hot Folder Web Submission - Invalid Hot Folder Path.'));
            $this->addDangerAlerts(__('Invalid Hot Folder Path.'));
            return false;
        }

        //extract from the request
        $headers = $request->getHeaders();
        $queryParams = $request->getQueryParams();
        unset($queryParams['BearerToken'], $queryParams['bearerToken'], $queryParams['bearertoken']);
        $parsedData = $request->getData();
        $rawData = $request->getBody()->getContents();

        //set up the path to save to
        $rndFilename = date("Ymd-His-") . Security::purl(16);
        $path = TextFormatter::makeDirectoryTrailingSmartSlash($hotFolder->path . $rndFilename);
        @mkdir($path, 0777, true);

        $contentType = null;
        if (isset($headers['Content-Type'])) {
            if (is_string($headers['Content-Type'])) {
                $contentType = $headers['Content-Type'];
            } elseif (isset($headers['Content-Type'][0]) && is_string($headers['Content-Type'][0])) {
                $contentType = $headers['Content-Type'][0];
            }
            $contentType = strtolower($contentType);
            $contentType = explode(";", $contentType)[0];
        }

        //dump($contentType);
        //dump($parsedData);
        //dump($rawData);

        /*
         * Now that everything is OK, start processing and saving the request data
         */

        /*
         * Step 1 - Save the QUERY Params if provided
         */
        $savedQs = false;
        if (strlen(json_encode($queryParams)) > 2) {
            $jsonData = json_encode($queryParams);
            file_put_contents($path . "query.json", $jsonData);
            $Auditor->auditInfo(__('Hot Folder Web Submission - Query data submission.'));
            $this->addSuccessAlerts("Saved QUERY string parameters.");
            $savedQs = true;
        } else {
            $this->addInfoAlerts("No QUERY string parameters to save.");
        }


        /*
         * Step 2 - Save CakePHP Parsed data (return if present)
         */
        if (!empty($parsedData)) {
            /** @var UploadedFile[] $uploadedFiles */
            $uploadedFiles = [];
            $this->extractUploadedFiles($parsedData, $uploadedFiles, $path);

            $jsonData = array_merge($queryParams, $parsedData);
            $result = file_put_contents($path . "data.json", json_encode($jsonData));
            if ($result) {
                $this->addSuccessAlerts("Saved POST data $result bytes.");
            } else {
                $this->addSuccessAlerts("Unable to save POST data.");
            }
            $Auditor->auditInfo(__('Hot Folder Web Submission - DATA submission.'));

            $this->addInfoAlerts(__('Completed DATA Processing.'));
            return true;
        }


        /*
         * Step 3 - Save RAW data (return if present)
         */
        if (!empty($rawData)) {
            $isSafe = $this->isBlobDataSafe($rawData);
            $mimeType = $this->getMimeTypeFromBlob($rawData);
            if (!$isSafe) {
                $message = __("Detected unsafe data [{0}]", $mimeType);
                $Auditor->auditWarning(__('Hot Folder Web Submission - {0}.', 1));
                $this->addDangerAlerts($message);
                return false;
            }

            $ext = $this->getExtensionFromBlob($rawData);
            $fullPath = $path . "body.$ext";

            $bodyResult = file_put_contents($fullPath, $rawData);
            if ($bodyResult) {
                $this->addSuccessAlerts("Saved RAW $mimeType body $bodyResult bytes.");
            } else {
                $this->addSuccessAlerts("Unable to save RAW body.");
            }

            $Auditor->auditInfo(__('Hot Folder Web Submission - RAW data submission.'));

            $this->addInfoAlerts(__('Completed RAW Processing.'));
            return true;
        }

        if ($savedQs) {
            return true;
        }

        rmdir($path);
        $this->addDangerAlerts(__('Unable to process request.'));
        return false;
    }

    /**
     * This function uses &pass-by-reference to modify the original inputs
     *
     * @param array $requestData the CakePHP data $request->getData()
     * @param array $uploadedFiles an array to hold the extracted uploaded files
     * @param string|bool $savePath if provided will save the UploadedFile to the given directory (must exist)
     * @return void
     */
    public function extractUploadedFiles(array &$requestData, array &$uploadedFiles, string|bool $savePath = false): void
    {
        foreach ($requestData as $key => &$value) {
            if (is_array($value)) {
                $this->extractUploadedFiles($value, $uploadedFiles, $savePath);
            } elseif ($value instanceof UploadedFile) {
                $uploadedFiles[] = $value;
                $lastKeyAsCounter = array_key_last($uploadedFiles);

                $fileContents = $value->getStream()->getContents();
                $fileName = $lastKeyAsCounter . "_" . $value->getClientFilename();
                $fileSize = $value->getSize();
                $mimeType = $value->getClientMediaType();
                $isSafe = $this->isBlobDataSafe($fileContents);

                $fullPath = '';
                if ($savePath && is_dir($savePath)) {
                    try {
                        $fullPath = $savePath . $fileName;
                        if (!$isSafe) {
                            $message = __("Detected unsafe data [{0}]", $mimeType);
                            $this->addDangerAlerts($message);
                            continue;
                        }
                        $result = file_put_contents($fullPath, $fileContents);
                        if ($result) {
                            $message = __("Saved file: {0}", $fileName);
                            $this->addSuccessAlerts($message);
                        } else {
                            $message = __("unable to save file: {0}", $fileName);
                            $this->addDangerAlerts($message);
                        }
                    } catch (\Throwable $exception) {
                        $message = __("File input error: {0}", $exception->getMessage());
                        $this->addDangerAlerts($message);
                        continue;
                    }
                }

                $requestData[$key] = [
                    'clientFilename' => $fileName,
                    'clientMediaType' => $mimeType,
                    'size' => $fileSize,
                    'path' => $fullPath,
                ];
            }
        }
    }

}
