<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Artifact;
use App\Model\Entity\Order;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Orders Model
 *
 * @property \App\Model\Table\OrderStatusesTable&\Cake\ORM\Association\BelongsTo $OrderStatuses
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\HasMany $Jobs
 * @property \App\Model\Table\OrderAlertsTable&\Cake\ORM\Association\HasMany $OrderAlerts
 * @property \App\Model\Table\OrderPropertiesTable&\Cake\ORM\Association\HasMany $OrderProperties
 * @property \App\Model\Table\OrderStatusMovementsTable&\Cake\ORM\Association\HasMany $OrderStatusMovements
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method Order newEmptyEntity()
 * @method Order newEntity(array $data, array $options = [])
 * @method array<Order> newEntities(array $data, array $options = [])
 * @method Order get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method Order findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method Order patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<Order> patchEntities(iterable $entities, array $data, array $options = [])
 * @method Order|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method Order saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<Order>|\Cake\Datasource\ResultSetInterface<Order>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<Order>|\Cake\Datasource\ResultSetInterface<Order> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<Order>|\Cake\Datasource\ResultSetInterface<Order>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<Order>|\Cake\Datasource\ResultSetInterface<Order> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdersTable extends AppTable
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

        $this->setTable('orders');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrderStatuses', [
            'foreignKey' => 'order_status_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Jobs', [
            'foreignKey' => 'order_id',
        ]);
        $this->hasMany('OrderAlerts', [
            'foreignKey' => 'order_id',
        ]);
        $this->hasMany('OrderProperties', [
            'foreignKey' => 'order_id',
        ]);
        $this->hasMany('OrderStatusMovements', [
            'foreignKey' => 'order_id',
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'order_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'orders_users',
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
            ->scalar('guid')
            ->maxLength('guid', 50)
            ->allowEmptyString('guid');

        $validator
            ->integer('order_status_id')
            ->notEmptyString('order_status_id');

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
            ->scalar('external_system_type')
            ->maxLength('external_system_type', 50)
            ->allowEmptyString('external_system_type');

        $validator
            ->scalar('external_order_number')
            ->maxLength('external_order_number', 50)
            ->allowEmptyString('external_order_number');

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
        $rules->add($rules->existsIn(['order_status_id'], 'OrderStatuses'), ['errorField' => '0']);

        return $rules;
    }

    public function create(array $data): false|Order
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
                'external_system_type' => true,
                'external_order_number' => true,
                'external_creation_date' => true,
                'payload' => true,
            ];
            $dataForHashing = array_intersect_key($data, $hashEntries);
            $data['hash_sum'] = sha1(serialize($dataForHashing));
        }

        $isPresent = $this->findByHashSum($data['hash_sum'])->first();
        if ($isPresent) {
            $this->addDangerAlerts(__("Order {0} already exists in the Database.", $data['name']));
            return false;
        }

        $ent = $this->newEntity($data);

        return $this->save($ent);
    }

    /**
     * @param int|Order $idOrEntity
     * @param bool $validated
     * @return Artifact[] array
     */
    public function getArtifacts(int|Order $idOrEntity, bool $validated = true): array
    {
        if (is_int($idOrEntity)) {
            $id = $idOrEntity;
        } else {
            $id = $idOrEntity->id;
        }

        /** @var Order $order */
        $order = $this->find('all')
            ->where(['id' => $id])
            ->contain([
                'Jobs' => [
                    'sort' => ['Jobs.id' => 'ASC'],
                    'Documents' => [
                        'sort' => ['Documents.id' => 'ASC']
                    ]
                ]
            ])
            ->first();

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        $artifacts = [];
        foreach ($order->jobs as $job) {
            foreach ($job->documents as $document) {
                $artifactToken = $document->artifact_token;
                if ($artifactToken) {
                    $artifacts[$artifactToken] = null; //populated later
                }
            }
        }

        if (empty($artifacts)) {
            return $artifacts;
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

    /**
     * @param int|Order $idOrEntity
     * @return false|Order
     */
    public function getCompleteOrder(int|Order $idOrEntity): false|Order
    {
        if (is_int($idOrEntity)) {
            $id = $idOrEntity;
        } else {
            $id = $idOrEntity->id;
        }

        /** @var Order $order */
        $order = $this->find('all')
            ->where(['id' => $id])
            ->contain([
                'Jobs' => [
                    'sort' => ['Jobs.id' => 'ASC'],
                    'Documents' => [
                        'sort' => ['Documents.id' => 'ASC']
                    ]
                ]
            ])
            ->first();

        if (empty($order)) {
            return false;
        }

        return $order;
    }
}
