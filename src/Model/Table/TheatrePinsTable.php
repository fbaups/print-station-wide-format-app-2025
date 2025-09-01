<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TheatrePins Model
 *
 * @method \App\Model\Entity\TheatrePin newEmptyEntity()
 * @method \App\Model\Entity\TheatrePin newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\TheatrePin> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TheatrePin get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\TheatrePin findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\TheatrePin patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\TheatrePin> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\TheatrePin|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\TheatrePin saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\TheatrePin>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TheatrePin>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TheatrePin>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TheatrePin> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TheatrePin>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TheatrePin>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\TheatrePin>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\TheatrePin> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TheatrePinsTable extends AppTable
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

        $this->setTable('theatre_pins');
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
            ->scalar('name')
            ->maxLength('name', 50)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->scalar('pin_code')
            ->maxLength('pin_code', 10)
            ->allowEmptyString('pin_code');

        $validator
            ->integer('user_link')
            ->allowEmptyString('user_link');

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
     * @param $pinCode
     * @return bool
     */
    public function validatePinCode($pinCode): bool
    {
        $theatrePin = $this->find('all')->where(['pin_code' => $pinCode])->first();
        if ($theatrePin) {
            return true;
        }
        return false;
    }

}
