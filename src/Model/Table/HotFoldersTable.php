<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Log\Engine\Auditor;
use App\Model\Entity\HotFolder;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\Security\Security;
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
     * 1) Raw body. MIME type is checked and the blob saved as defined by the MIME type
     * 2) Form Data.
     *      a) Data only. Saved as data.json
     *      b) Data + File/s. Folder is created and data.json saved as JSON and files saved
     *      c) If query params are present, they are saved into the data.json file vai array_merge(queryData, formData).
     * 3) Query Data. If the only data present, will be saved as data.json
     *
     * @param mixed $hotFolderName
     * @param ServerRequest $request
     * @return bool
     */
    public function saveSubmission(mixed $hotFolderName, ServerRequest $request): bool
    {
        $Auditor = new Auditor();

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
        $formData = $request->getData();
        $rawBody = $request->getBody()->getContents();

        //extract the bearer token
        $authBearerToken = $this->extractBearerTokenFromRequest($request);
        if (!$authBearerToken) {
            $Auditor->auditWarning(__('Hot Folder Web Submission - Bearer Token not supplied.'));
            $this->addDangerAlerts(__('Bearer Token not supplied.'));
            return false;
        }

        //check the bearer token
        /** @var SeedsTable $Seeds */
        $Seeds = $this->getTableLocator()->get('Seeds');
        $isTokenValid = $Seeds->validateSeed($authBearerToken, true);
        if (!$isTokenValid) {
            $Auditor->auditWarning(__('Hot Folder Web Submission - Bearer Token not valid.'));
            $this->addDangerAlerts(__("Bearer Token is not valid."));
            return false;
        }
        $Seeds->increaseBid($authBearerToken);

        //set up the path to save to
        $rndFilename = date("Ymd-His-") . Security::purl(16);
        $path = $hotFolder->path . $rndFilename . "/";
        @mkdir($path, 0777, true);

        //raw data submission
        if (strlen($rawBody) > 0) {
            $isSafe = $this->isBlobDataSafe($rawBody);
            $mimeType = $this->getMimeTypeFromBlob($rawBody);
            if (!$isSafe) {
                $message = __("Detected unsafe data [{0}]", $mimeType);
                $Auditor->auditWarning(__('Hot Folder Web Submission - {0}.', 1));
                $this->addDangerAlerts($message);
                return false;
            }

            $ext = $this->getExtensionFromBlob($rawBody);

            $jsonData = json_encode($queryParams);
            if (strlen($jsonData) > 2) {
                file_put_contents($path . "body.$ext", $rawBody);
                file_put_contents($path . "meta.json", $jsonData);
                $Auditor->auditInfo(__('Hot Folder Web Submission - Raw data & query data submission.'));
            } else {
                file_put_contents($path . "body.$ext", $rawBody);
                $Auditor->auditInfo(__('Hot Folder Web Submission - Raw data submission.'));
            }

            return true;
        }

        //query string data
        if (strlen(json_encode($queryParams)) > 2 && strlen(json_encode($formData)) <= 2) {
            $jsonData = json_encode($queryParams);
            file_put_contents($path . "meta.json", $jsonData);
            $Auditor->auditInfo(__('Hot Folder Web Submission - Query data submission.'));

            return true;
        }

        //html form-data extraction
        if (strlen(json_encode($queryParams)) > 2 && strlen(json_encode($formData)) > 2) {
            //separate out file uploads
            /** @var UploadedFile[] $uploadedFiles */
            $uploadedFiles = [];
            $this->extractUploadedFiles($formData, $uploadedFiles);

            //save any file uploads
            if (!empty($uploadedFiles)) {
                foreach ($uploadedFiles as $uploadedFile) {
                    try {
                        $fileContents = $uploadedFile->getStream()->getContents();
                        $fileName = $uploadedFile->getClientFilename();
                        $mimeType = $uploadedFile->getClientMediaType();
                        $isSafe = $this->isBlobDataSafe($fileContents);
                        if (!$isSafe) {
                            $message = __("Detected unsafe data [{0}]", $mimeType);
                            $this->addDangerAlerts($message);
                            continue;
                        }
                        $result = file_put_contents($path . $fileName, $fileContents);
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
            }

            $jsonData = array_merge($queryParams, $formData);
            file_put_contents($path . "meta.json", json_encode($jsonData));
            $Auditor->auditInfo(__('Hot Folder Web Submission - Form data submission.'));

            return true;
        }

        unlink($path);
        return false;
    }

    /**
     * @param $data
     * @param $stdClassObjects
     * @return void
     */
    public function extractUploadedFiles(&$data, &$stdClassObjects): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->extractUploadedFiles($value, $stdClassObjects);
            } elseif ($value instanceof UploadedFile) {
                $stdClassObjects[$key] = $value;
                unset($data[$key]);
            }
        }
    }

    /**
     * Extract from the header or the query-string
     *
     * @param ServerRequest $request
     * @return string|false
     */
    protected function extractBearerTokenFromRequest(ServerRequest $request): string|false
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
