<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Role;
use Cake\Cache\Cache;
use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Roles Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsToMany $Users
 *
 * @method \App\Model\Entity\Role newEmptyEntity()
 * @method \App\Model\Entity\Role newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Role[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Role get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Role findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Role[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Role[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends AppTable
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

        $this->setTable('roles');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Subscriptions', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'subscription_id',
            'joinTable' => 'subscriptions_users',
        ]);
        $this->belongsToMany('Users', [
            'foreignKey' => 'role_id',
            'targetForeignKey' => 'user_id',
            'joinTable' => 'roles_users',
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
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->requirePresence('description', 'create')
            ->notEmptyString('description');

        $validator
            ->scalar('alias')
            ->maxLength('alias', 50)
            ->requirePresence('alias', 'create')
            ->notEmptyString('alias');

        $validator
            ->integer('session_timeout')
            ->requirePresence('session_timeout', 'create')
            ->notEmptyString('session_timeout');

        $validator
            ->integer('inactivity_timeout')
            ->requirePresence('inactivity_timeout', 'create')
            ->notEmptyString('inactivity_timeout');

        $validator
            ->scalar('grouping')
            ->maxLength('grouping', 16)
            ->allowEmptyString('grouping');

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
     * @return \Cake\Database\Query\SelectQuery|Query\SelectQuery
     */
    public function findListWithGrouping(): \Cake\Database\Query\SelectQuery|Query\SelectQuery
    {
        return $this->find('list', [
            'keyField' => 'id',
            'valueField' => 'name',
            'groupField' => 'grouping'
        ]);
    }

    /**
     * Get the roles that are considered Peers to the given role
     * e.g. SuperAdmin is a peer to every role while manager is peer to some roles.
     *
     * $asGrouped = false --> format is key/value paired roles
     * e.g. [1=>'SuperAdmin',2=>'Admin', ...]
     *
     * $asGrouped = true --> format is key/value paired roles under a Group level
     *  e.g. ['Administrators'=>[1=>'SuperAdmin',2=>'Admin'], ...]
     *
     * @param array|string|int $roles
     * @param bool $asGrouped
     * @return array
     */
    public function getPeerRoles(array|string|int $roles, bool $asGrouped = true): array
    {
        if (is_numeric($roles)) {
            try {
                /** @var Role $role */
                $role = $this->find('all')->where(['id' => $roles])->firstOrFail();
                $rolesCleaned = [$role->alias];
            } catch (\Throwable $exception) {
                return [];
            }
        } elseif (is_string($roles)) {
            $rolesCleaned = [strtolower($roles)];
        } elseif (is_array($roles)) {
            $rolesCleaned = [];
            foreach ($roles as $k => $v) {
                if (!is_array($k)) {
                    if (!is_numeric($k)) {
                        $rolesCleaned [] = strtolower($k);
                    }
                }
                if (!is_array($v)) {
                    if (!is_numeric($v)) {
                        $rolesCleaned [] = strtolower($v);
                    }
                }
            }
        } else {
            return [];
        }

        $combinedRoles = [];
        foreach ($rolesCleaned as $role) {
            if ($role === 'superadmin') {
                $allowedRoles = [
                    'superadmin',
                    'admin',
                    'superuser',
                    'user',
                    'manager',
                    'supervisor',
                    'operator',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            } elseif ($role === 'admin') {
                $allowedRoles = [
                    'admin',
                    'superuser',
                    'user',
                    'manager',
                    'supervisor',
                    'operator',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            } elseif ($role === 'superuser') {
                $allowedRoles = [
                    'superuser',
                    'user',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            } elseif ($role === 'user') {
                $allowedRoles = [
                    'user',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            } elseif ($role === 'manager') {
                $allowedRoles = [
                    'manager',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            } elseif ($role === 'supervisor') {
                $allowedRoles = [
                    'supervisor',
                    'operator',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            } elseif ($role === 'operator') {
                $allowedRoles = [
                    'operator',
                ];
                $combinedRoles = array_merge($combinedRoles, $allowedRoles);
            }
        }

        $combinedRoles = array_unique($combinedRoles);

        $peerRoles = [];
        foreach ($combinedRoles as $combinedRole) {
            /** @var Role $role */
            foreach ($this->findByNameOrAlias($combinedRoles) as $role) {
                if ($asGrouped) {
                    $peerRoles[$role->grouping][$role->id] = $role->name;
                } else {
                    $peerRoles[$role->id] = $role->name;
                }
            }
        }
        return $peerRoles;
    }

    /**
     * Clean up a given list of roles.
     *
     * $peerRoles should be in the format of the return value of $this->getPeerRoles(..., $asGrouped = false )
     *
     *
     * Return format is key/value pair of roles
     * e.g. [1=>'SuperAdmin',2=>'Admin', ...]
     *
     * @param array|string $peerRoles
     * @param array|string $dirtyRoles
     * @return array
     */
    public function validatePeerRoles(array|string $peerRoles, array|string $dirtyRoles): array
    {

        if (is_array($dirtyRoles) && isset($dirtyRoles['_ids'])) {
            $dirtyRoles = $dirtyRoles['_ids'];

        }

        if (is_string($dirtyRoles) || is_numeric($dirtyRoles)) {
            $dirtyRoles = [$dirtyRoles];
        }

        $rolesCleaned = [];
        foreach ($dirtyRoles as $k => $v) {

            if (!is_numeric($v)) {
                if (in_array($v, $peerRoles)) {
                    $tmpKey = array_search($v, $peerRoles);
                    $tmpVal = $peerRoles[$tmpKey];
                    $rolesCleaned[$tmpKey] = $tmpVal;
                }
            } else {
                if (isset($peerRoles[$v])) {
                    $tmpKey = $v;
                    $tmpVal = $peerRoles[$v];
                    $rolesCleaned[$tmpKey] = $tmpVal;
                }
            }

        }

        return $rolesCleaned;
    }

    /**
     * @return array
     */
    public function getSessionTimeoutList(): array
    {
        $list = Cache::read('RolesTable.session_timeout');
        if ($list) {
            return $list;
        }

        $roles = $this->find('all');
        $list = [];
        foreach ($roles as $role) {
            $list[$role['alias']] = $role['session_timeout'];
        }

        Cache::write('RolesTable.session_timeout', $list);

        return $list;
    }

    /**
     * @return array
     */
    public function getInactivityTimeoutList(): array
    {
        $list = Cache::read('RolesTable.inactivity_timeout');
        if ($list) {
            return $list;
        }

        $roles = $this->find('all');
        $list = [];
        foreach ($roles as $role) {
            $list[$role['alias']] = $role['inactivity_timeout'];
        }

        Cache::write('RolesTable.inactivity_timeout', $list);

        return $list;
    }

}
