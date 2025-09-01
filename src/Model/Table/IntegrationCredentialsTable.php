<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\IntegrationCredential;
use App\Utility\IntegrationCredentials\Backblaze\B2CommunicationsFlow;
use App\Utility\IntegrationCredentials\MicrosoftOpenAuth2\AuthorizationFlow;
use App\Utility\IntegrationCredentials\sFTP\sftpCommunicationsFlow;
use App\Utility\IntegrationCredentials\XMPie\uProduceCommunicationsFlow;
use App\Utility\Network\CACert;
use App\Utility\Storage\BackblazeB2Inspector;
use App\View\Helper\ExtendedFormHelper;
use arajcany\BackblazeB2Client\BackblazeB2\Client;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Validation\Validator;
use Cake\View\View;
use League\Flysystem\Filesystem;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Zaxbux\Flysystem\BackblazeB2Adapter;

/**
 * IntegrationCredentials Model
 *
 * @method \App\Model\Entity\IntegrationCredential newEmptyEntity()
 * @method \App\Model\Entity\IntegrationCredential newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\IntegrationCredential> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\IntegrationCredential get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\IntegrationCredential findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method array<\App\Model\Entity\IntegrationCredential> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\IntegrationCredential|false save(EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\IntegrationCredential saveOrFail(EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\IntegrationCredential>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IntegrationCredential>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\IntegrationCredential>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IntegrationCredential> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\IntegrationCredential>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IntegrationCredential>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\IntegrationCredential>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\IntegrationCredential> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class IntegrationCredentialsTable extends AppTable
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

        $this->setTable('integration_credentials');
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

        $validator
            ->scalar('last_status_text')
            ->allowEmptyString('last_status_text');

        $validator
            ->scalar('last_status_html')
            ->allowEmptyString('last_status_html');

        $validator
            ->scalar('last_status_datetime')
            ->allowEmptyString('last_status_datetime');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = ['parameters', 'tracking_data'];

        return $jsonFields;
    }

    public function getByTrackingHash(string $trackingHash)
    {
        return $this->find('all')->where(['tracking_hash' => $trackingHash])->first();
    }

    /**
     * Some of the data needs to be encrypted before persisting to the DB
     *
     * @param EntityInterface|IntegrationCredential $entity
     * @param array $data
     * @param array $options
     * @return EntityInterface|IntegrationCredential
     */
    public function patchEntity(EntityInterface|IntegrationCredential $entity, array $data, array $options = []): EntityInterface|IntegrationCredential
    {
        $newParameters = [];
        $newTrackingData = [];

        if ($entity->type === 'MicrosoftOpenAuth2') {
            $defaultParameters = $this->getMicrosoftOpenAuth2DefaultParameters();
            $currentParameters = $entity->parameters ?? $defaultParameters;

            if (isset($data['parameters'])) {
                $newParameters = Hash::merge($defaultParameters, $currentParameters, $data['parameters']);
            } else {
                $newParameters = Hash::merge($defaultParameters, $currentParameters);
            }

            $newParameters['auth_url_options']['scope'] = array_unique($newParameters['auth_url_options']['scope']);

            if (isset($newParameters['provider_options']['tenantId'])) {
                $newParameters['provider_options']['tenantId'] = Security::encrypt64($newParameters['provider_options']['tenantId']);
            }

            if (isset($newParameters['provider_options']['clientId'])) {
                $newParameters['provider_options']['clientId'] = Security::encrypt64($newParameters['provider_options']['clientId']);
            }

            if (isset($newParameters['provider_options']['clientSecret'])) {
                if (strlen($newParameters['provider_options']['clientSecret']) < 60) {
                    $newParameters['provider_options']['clientSecret'] = Security::encrypt64($newParameters['provider_options']['clientSecret']);
                }
            }
        }

        if ($entity->type === 'BackblazeB2') {
            $defaultParameters = $this->getBackblazeB2DefaultParameters();
            $currentParameters = $entity->parameters ?? $defaultParameters;

            if (isset($data['parameters'])) {
                $newParameters = Hash::merge($defaultParameters, $currentParameters, $data['parameters']);
            } else {
                $newParameters = Hash::merge($defaultParameters, $currentParameters);
            }

            $B2I = new BackblazeB2Inspector();
            $accountAuthorisation = $B2I->getAccountAuthorisation($newParameters);

            if (isset($newParameters['b2_key'])) {
                if (strlen($newParameters['b2_key']) < 60) {
                    $newParameters['b2_key'] = Security::encrypt64($newParameters['b2_key']);
                }
            }
            if (isset($newParameters['b2_path'])) {
                $newParameters['b2_path'] = $B2I->cleanupPath($newParameters['b2_path']);
            }

            if ($accountAuthorisation) {
                $aaBucketId = $accountAuthorisation['account_authorisation']['allowed']['bucketId'] ?? false;
                $aaBucketName = $accountAuthorisation['account_authorisation']['allowed']['bucketName'] ?? false;
                $aaNamePrefix = $accountAuthorisation['account_authorisation']['allowed']['namePrefix'] ?? false;
                $aaHttpHost = $accountAuthorisation['account_authorisation']['downloadUrl'] ?? false;

                if ($aaBucketId) {
                    $newParameters['b2_bucket'] = $aaBucketId;
                }

                if ($aaBucketId) {
                    $newParameters['b2_bucket_name'] = $aaBucketName;
                }

                if ($aaNamePrefix) {
                    $newParameters['b2_name_prefix'] = $B2I->cleanupPath($aaNamePrefix);
                } else {
                    $newParameters['b2_name_prefix'] = '';
                }

                if ($aaHttpHost) {
                    $newParameters['http_host'] = "{$aaHttpHost}/file/{$aaBucketName}/{$newParameters['b2_path']}";
                }

                $newTrackingData = $accountAuthorisation;
            }
        }

        if ($entity->type === 'sftp') {
            $defaultParameters = $this->getSftpDefaultParameters();
            $currentParameters = $entity->parameters ?? $defaultParameters;

            if (isset($data['parameters'])) {
                $newParameters = Hash::merge($defaultParameters, $currentParameters, $data['parameters']);
            } else {
                $newParameters = Hash::merge($defaultParameters, $currentParameters);
            }

            if (isset($newParameters['sftp_password'])) {
                if (strlen($newParameters['sftp_password']) > 0) {
                    if ($newParameters['sftp_password'] !== $currentParameters['sftp_password']) {
                        $newParameters['sftp_password'] = Security::encrypt64($newParameters['sftp_password']);
                    }
                }
            }

            if (isset($newParameters['sftp_privateKey'])) {
                if (strlen($newParameters['sftp_privateKey']) > 0) {
                    if ($newParameters['sftp_privateKey'] !== $currentParameters['sftp_privateKey']) {
                        $newParameters['sftp_privateKey'] = Security::encrypt64($newParameters['sftp_privateKey']);
                    }
                }
            }

            if (isset($newParameters['sftp_publicKey'])) {
                if (strlen($newParameters['sftp_publicKey']) > 0) {
                    if ($newParameters['sftp_publicKey'] !== $currentParameters['sftp_publicKey']) {
                        $newParameters['sftp_publicKey'] = Security::encrypt64($newParameters['sftp_publicKey']);
                    }
                }
            }
        }

        if ($entity->type === 'XMPie-uProduce') {
            $defaultParameters = $this->getUProduceDefaultParameters();
            $currentParameters = $entity->parameters ?? $defaultParameters;

            if (isset($data['parameters'])) {
                $newParameters = Hash::merge($defaultParameters, $currentParameters, $data['parameters']);
            } else {
                $newParameters = Hash::merge($defaultParameters, $currentParameters);
            }

            if (isset($newParameters['uproduce_admin_password'])) {
                if (strlen($newParameters['uproduce_admin_password']) > 0) {
                    if ($newParameters['uproduce_admin_password'] !== $currentParameters['uproduce_admin_password']) {
                        $newParameters['uproduce_admin_password'] = Security::encrypt64($newParameters['uproduce_admin_password']);
                    }
                }
            }

            if (isset($newParameters['uproduce_password'])) {
                if (strlen($newParameters['uproduce_password']) > 0) {
                    if ($newParameters['uproduce_password'] !== $currentParameters['uproduce_password']) {
                        $newParameters['uproduce_password'] = Security::encrypt64($newParameters['uproduce_password']);
                    }
                }
            }

            if (isset($newParameters['uproduce_host'])) {
                if (strlen($newParameters['uproduce_host']) > 0) {
                    $newParameters['uproduce_host'] = TextFormatter::makeDirectoryTrailingForwardSlash($newParameters['uproduce_host']);

                    $hostCheck = strtolower($newParameters['uproduce_host']);
                    if (!TextFormatter::startsWith($hostCheck, 'http://') && !TextFormatter::startsWith($hostCheck, 'https://')) {
                        $newParameters['uproduce_host'] = TextFormatter::makeStartsWith($newParameters['uproduce_host'], 'https://');
                    }
                }
            }
        }

        /** @var IntegrationCredential $entity */
        $entity = parent::patchEntity($entity, $data, $options);

        $entity->parameters = $newParameters;
        $entity->tracking_data = $newTrackingData;

        return $entity;
    }

    public function updateLastStatusForAll(): array
    {
        /** @var IntegrationCredential[] $integrationCredentials */
        $integrationCredentials = $this->find('all');

        $results = [];
        foreach ($integrationCredentials as $integrationCredential) {
            $updateStatus = $this->updateLastStatus($integrationCredential);
            $results[$integrationCredential->id] = $updateStatus;
        }

        return $results;
    }


    /**
     * Try and connect to the integration and update the entity with the results
     *
     * @param int|IntegrationCredential|EntityInterface $idOrEntity
     * @return bool
     */
    public function updateLastStatus(int|IntegrationCredential|EntityInterface $idOrEntity): bool
    {
        /** @var IntegrationCredential $integrationCredential */
        $integrationCredential = $this->asEntity($idOrEntity);

        //MicrosoftOpenAuth2
        if ($integrationCredential->type === 'MicrosoftOpenAuth2') {
            $AuthFlow = new AuthorizationFlow($integrationCredential);
            $AuthFlow->updateLastStatus();
            $saveResult = $this->save($integrationCredential);

            return (bool)$saveResult;
        }

        //BackblazeB2
        if ($integrationCredential->type === 'BackblazeB2') {
            $B2Flow = new B2CommunicationsFlow($integrationCredential);
            $B2Flow->updateLastStatus();
            $saveResult = $this->save($integrationCredential);

            return (bool)$saveResult;
        }

        //sFTP
        if ($integrationCredential->type === 'sftp') {
            $SftpFlow = new sftpCommunicationsFlow($integrationCredential);
            $SftpFlow->updateLastStatus();
            $saveResult = $this->save($integrationCredential);

            return (bool)$saveResult;
        }

        //uProduce
        if ($integrationCredential->type === 'XMPie-uProduce') {
            $UProduceFlow = new uProduceCommunicationsFlow($integrationCredential);
            $UProduceFlow->updateLastStatus();
            $saveResult = $this->save($integrationCredential);

            return (bool)$saveResult;
        }

        if ($this->save($integrationCredential)) {
            return true;
        } else {
            return false;
        }

    }

    public function getMicrosoftOpenAuth2DefaultParameters(): array
    {
        $redirectUri = Router::url(
            [
                'prefix' => 'Administrators',
                'controller' => 'IntegrationCredentials',
                'action' => 'authenticate',
                'microsoft-open-auth-2'
            ],
            true
        );

        return [
            "provider_options" => [
                "tenantId" => "",
                "clientId" => "",
                "clientSecret" => "",
                "redirectUri" => $redirectUri,
                "urlAuthorize" => "https://login.microsoftonline.com/common/oauth2/v2.0/authorize",
                "urlAccessToken" => "https://login.microsoftonline.com/common/oauth2/v2.0/token"
            ],
            "auth_url_options" => [
                "scope" => [
                    "email",
                    "openid",
                    "profile",
                    "User.read",
                    "Files.Read.All",
                    "offline_access"
                ]
            ]
        ];
    }

    public function getBackblazeB2DefaultParameters(): array
    {
        return [
            "b2_key_id" => '',
            "b2_key" => '',
            "b2_bucket" => '',
            "b2_path" => '',
        ];
    }

    public function getSftpDefaultParameters(): array
    {
        return [
            'sftp_host' => '',
            'sftp_port' => 22,
            'sftp_username' => '',
            'sftp_password' => '',
            'sftp_timeout' => 2,
            'sftp_path' => '',
            'sftp_privateKey' => '',
            'sftp_publicKey' => '',
        ];

    }

    public function getUProduceDefaultParameters(): array
    {
        return [
            'uproduce_host' => '',
            'uproduce_admin_username' => '',
            'uproduce_admin_password' => '',
            'uproduce_username' => '',
            'uproduce_password' => '',
        ];

    }
}
