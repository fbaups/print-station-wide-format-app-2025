<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Artifact;
use App\Model\Entity\Document;
use App\Model\Entity\Errand;
use App\Model\Entity\Order;
use App\Utility\Network\CACert;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\I18n\DateTime;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use GuzzleHttp\Client;

/**
 * Documents Model
 *
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\BelongsTo $Jobs
 * @property \App\Model\Table\DocumentStatusesTable&\Cake\ORM\Association\BelongsTo $DocumentStatuses
 * @property \App\Model\Table\DocumentAlertsTable&\Cake\ORM\Association\HasMany $DocumentAlerts
 * @property \App\Model\Table\DocumentPropertiesTable&\Cake\ORM\Association\HasMany $DocumentProperties
 * @property \App\Model\Table\DocumentStatusMovementsTable&\Cake\ORM\Association\HasMany $DocumentStatusMovements
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method \App\Model\Entity\Document newEmptyEntity()
 * @method \App\Model\Entity\Document newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Document> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Document get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Document findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Document patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Document> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Document|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Document saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Document>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Document>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Document>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Document> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Document>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Document>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Document>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Document> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class DocumentsTable extends AppTable
{
    protected Table|ArtifactsTable $Artifacts;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('documents');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('DocumentStatuses', [
            'foreignKey' => 'document_status_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('DocumentAlerts', [
            'foreignKey' => 'document_id',
        ]);
        $this->hasMany('DocumentProperties', [
            'foreignKey' => 'document_id',
        ]);
        $this->hasMany('DocumentStatusMovements', [
            'foreignKey' => 'document_id',
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'document_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'documents_users',
        ]);

        $this->initializeSchemaJsonFields($this->getJsonFields());

        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
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
            ->scalar('guid')
            ->maxLength('guid', 50)
            ->allowEmptyString('guid');

        $validator
            ->integer('job_id')
            ->notEmptyString('job_id');

        $validator
            ->integer('document_status_id')
            ->notEmptyString('document_status_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 1024)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->integer('quantity')
            ->requirePresence('quantity', 'create')
            ->notEmptyString('quantity');

        $validator
            ->scalar('artifact_token')
            ->maxLength('artifact_token', 50)
            ->allowEmptyString('artifact_token');

        $validator
            ->scalar('external_document_number')
            ->maxLength('external_document_number', 50)
            ->allowEmptyString('external_document_number');

        $validator
            ->dateTime('external_creation_date')
            ->allowEmptyDateTime('external_creation_date');

        $validator
            ->integer('priority')
            ->allowEmptyString('priority');

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
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['job_id'], 'Jobs'), ['errorField' => '0']);
        $rules->add($rules->existsIn(['document_status_id'], 'DocumentStatuses'), ['errorField' => '1']);

        return $rules;
    }

    /**
     * @param array $data
     * @return false|Document
     */
    public function create(array $data): false|Document
    {
        $defaultData = [
            'quantity' => 1,
        ];

        $data = array_merge($defaultData, $data);

        if (empty($data['hash_sum'])) {
            $hashEntries = [
                'name' => true,
                'description' => true,
                'quantity' => true,
                'external_document_number' => true,
                'external_creation_date' => true,
                'external_url' => true,
                'payload' => true,
            ];
            $dataForHashing = array_intersect_key($data, $hashEntries);
            $data['hash_sum'] = sha1(serialize($dataForHashing));
        }

        $isPresent = $this->findByHashSum($data['hash_sum'])->first();
        if ($isPresent) {
            $this->addDangerAlerts(__("Document {0} already exists in the Database.", $data['name']));
            return false;
        }

        $ent = $this->newEntity($data);
        $saveResult = $this->save($ent);

        //create an errand to download the document from the path
        if ($saveResult) {
            $this->addSuccessAlerts(__("Created Document ID:{0}", $saveResult->id));

            if (!empty($saveResult['external_url'])) {
                $this->downloadDocumentErrand($saveResult);
            }
        } else {
            $this->addDangerAlerts(__("Failed to save the Document."));
        }

        return $saveResult;
    }

    /**
     * @param int|Document $idOrEntity
     * @return Errand|bool
     */
    public function downloadDocumentErrand(int|Document $idOrEntity): bool|Errand
    {
        /** @var Document $ent */
        $ent = $this->asEntity($idOrEntity);

        /** @var ErrandsTable $Errands */
        $Errands = TableRegistry::getTableLocator()->get('Errands');

        $activation = new DateTime();
        $expiration = (clone $activation)->addDays(7);
        $options = [
            'activation' => $activation,
            'expiration' => $expiration,
            'name' => "Download Document {$ent->name}",
            'class' => 'DocumentsTable',
            'method' => 'downloadDocument',
            'parameters' => [0 => $ent->id],
        ];

        return $Errands->createErrand($options, false);
    }

    /**
     * @param int|Document $idOrEntity
     * @return Artifact|array|bool
     */
    public function downloadDocument(int|Document $idOrEntity): bool|array|Artifact
    {
        if (is_int($idOrEntity)) {
            $document = $this->find('all')->where(['id' => $idOrEntity])->first();
        } else {
            $document = $idOrEntity;
        }

        if (!$document) {
            $this->setReturnMessage('Invalid Document ID or Entity.');
            $this->setReturnValue(1);
            return false;
        }

        //recurse up to get the Order
        /** @var Order $order */
        $orderIdInJob = $this->Jobs->find('all')->where(['id' => $document->job_id])->select('order_id', true);
        $order = $this->Jobs->Orders->find('all')->where(['id IN' => $orderIdInJob])->first();
        $externalSystemType = $order->external_system_type;

        //group all Artifacts by their order
        $grouping = sha1("{$order->id}.{$order->guid}");

        //check if already downloaded
        if ($document->artifact_token) {
            $artifact = $this->Artifacts->find('all')->where(['token' => $document->artifact_token, 'grouping' => $grouping])->first();
            if ($artifact) {
                $this->addInfoAlerts('Document was previously downloaded, deleting and will download again.');
                $this->Artifacts->delete($artifact);
            }
        }

        $document->document_status_id = $this->DocumentStatuses->findByNameOrAlias('Downloading')->first()->id;
        $this->save($document);

        //choose the best method to download document
        if (strtolower($externalSystemType) === 'ustore') {
            $data = $this->uStoreDocumentDownload($document->external_url);
            if ($data) {
                $artifact = $this->Artifacts->createArtifactFromBlob($data['name'], $data['blob'], $grouping);
            } else {
                $artifact = false;
            }
        } else {
            $artifact = $this->Artifacts->createArtifactFromUrl($document->name, $document->external_url, $grouping);
        }

        if ($artifact) {
            $document->document_status_id = $this->DocumentStatuses->findByNameOrAlias('Ready')->first()->id;
            $document->artifact_token = $artifact->token;

            $this->setReturnMessage('Document was successfully downloaded.');
            $this->setReturnValue(0);
        } else {
            $document->document_status_id = $this->DocumentStatuses->findByNameOrAlias('Error')->first()->id;

            $this->setReturnMessage('Could not download the document.');
            $this->setReturnValue(2);
        }
        $this->save($document);

        return $artifact;
    }

    public function uStoreDocumentDownload(string $url, array $options = []): bool|array
    {
        //determine if cacert.pem file is present
        $caPath = (new CACert())->getCertPath();
        if ($caPath) {
            $verify = $caPath;
        } else {
            $verify = true;
        }

        //relax $verify if localhost address
        $localhostAddresses = ['127.0.0.1', 'localhost'];
        $host = parse_url($url)['host'];
        if (in_array($host, $localhostAddresses)) {
            $verify = false;
        } else {
            $host = explode(".", $host);
            $host = array_pop($host);
            if (in_array($host, $localhostAddresses)) {
                $verify = false;
            }
        }

        $defaultOptions = [
            'timeout' => 600,
            'verify' => $verify,
        ];

        $options = array_merge($defaultOptions, $options);

        $guzzleOptions = [
            'base_uri' => $url,
            'timeout' => $options['timeout'],
            'verify' => $options['verify'],
        ];

        try {
            $Client = new Client($guzzleOptions);
            $response = $Client->head($url);

            $httpCode = $response->getStatusCode();
            $headers = $response->getHeaders();

            if (!isset($headers['Content-Length'][0])) {
                $this->addDangerAlerts('Content-Length not set.');
                return false;
            }

            $contentLength = intval($headers['Content-Length'][0]);

            if (!isset($headers['Content-Type'][0])) {
                $this->addDangerAlerts('Content-Type not set.');
                return false;
            }

            $contentType = $headers['Content-Type'][0];
            $contentType = strtolower($contentType);
            $contentType = explode(";", $contentType);
            $contentType = $contentType[0] ?? false;
            $this->addInfoAlerts(__('Content-Type is [{0}]', $contentType));
            if (!in_array($contentType, $this->Jobs->Orders->getSafeMimeTypes())) {
                $this->addDangerAlerts(__('Content-Type [{0}] not allowed.', $contentType));
                return false;
            }

            if (isset($headers['Content-Disposition'][0])) {
                $this->addDangerAlerts('Content-Disposition not set.');
                $contentDisposition = $headers['Content-Disposition'][0];
                if (!str_starts_with($contentDisposition, 'attachment; ')) {
                    $this->addDangerAlerts('Content-Disposition is not of type "attachment".');
                    return false;
                }

                $filename = str_replace('attachment; ', '', $contentDisposition);
                $filename = str_replace('filename=', '', $filename);
                $filename = trim($filename, "\"");
                $filename = urldecode($filename);
            } else {
                $filename = explode("?", $url)[0];
                $filename = pathinfo($filename, PATHINFO_BASENAME);
            }

            $response = $Client->get($url);
            $fileContents = $response->getBody()->read($contentLength);

            return [
                'name' => $filename,
                'blob' => $fileContents,
            ];

        } catch (\Throwable $exception) {
            $this->addDangerAlerts($exception->getMessage());
            return false;
        }
    }

    /**
     * @param int|Document $idOrEntity
     * @param bool $validated
     * @return array
     */
    public function getArtifacts(int|Document $idOrEntity, bool $validated = true): array
    {
        if (is_int($idOrEntity)) {
            $id = $idOrEntity;
        } else {
            $id = $idOrEntity->id;
        }

        /** @var Document $document */
        $document = $this->find('all')
            ->where(['id' => $id])
            ->first();

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        $artifacts = [];
        $artifactToken = $document->artifact_token;
        if ($artifactToken) {
            $artifacts[$artifactToken] = null; //populated later
        }

        $tokens = array_keys($artifacts);

        /** @var Artifact[] $artifactEntities */
        $artifactEntities = $Artifacts->findByTokens($tokens);

        foreach ($artifactEntities as $artifactEntity) {
            if ($validated && is_file($artifactEntity->full_unc)) {
                $artifacts[$artifactEntity->token] = $artifactEntity;
            } else {
                $artifacts[$artifactEntity->token] = $artifactEntity;
            }
        }

        return $artifacts;
    }
}
