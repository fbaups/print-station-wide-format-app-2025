<?php

namespace App\Model\Table;

use App\Model\Entity\InternalOption;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * InternalOptions Model
 *
 * @method InternalOption get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method InternalOption newEntity($data = null, array $options = [])
 * @method InternalOption[] newEntities(array $data, array $options = [])
 * @method InternalOption|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method InternalOption patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method InternalOption[] patchEntities($entities, array $data, array $options = [])
 * @method InternalOption findOrCreate($search, callable $callback = null, $options = [])
 * @method \Cake\ORM\Query findById(int $id)
 * @method \Cake\ORM\Query findByName(string $name)
 * @method \Cake\ORM\Query findByOptionKey(string $optionKey)
 * @method \Cake\ORM\Query findByApplyMask(bool $applyMask)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class InternalOptionsTable extends AppTable
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

        $this->setTable('internal_options');
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
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('option_key')
            ->requirePresence('option_key', 'create')
            ->notBlank('option_key')
            ->add('option_key', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('option_value')
            ->requirePresence('option_value', 'create')
            ->notBlank('option_value');

        $validator
            ->boolean('is_masked')
            ->allowEmptyString('is_masked');

        $validator
            ->boolean('apply_mask')
            ->allowEmptyString('apply_mask');

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
        $rules->add($rules->isUnique(['option_key']));

        return $rules;
    }

    /**
     * Returns the database connection name to use by default.
     *
     * @return string
     */
    public static function defaultConnectionName(): string
    {
        return 'internal';
    }

    /**
     * Create the internal_options table in the internal.db
     * Commonly called when this is a brand new installation.
     */
    public function buildInternalOptionsTable()
    {
        $hk = sha1(random_bytes(2048)) . sha1(random_bytes(2048)) . sha1(random_bytes(2048));
        $hs = sha1(random_bytes(2048)) . sha1(random_bytes(2048)) . sha1(random_bytes(2048));

        $sqlStatements[] = "CREATE TABLE IF NOT EXISTS internal_options
(
	id INTEGER not null primary key autoincrement,
	created DATETIME,
	modified DATETIME,
	option_key TEXT not null,
	option_value TEXT not null,
	is_masked INT,
	apply_mask INT
)";

        $sqlStatements[] = "CREATE UNIQUE INDEX IF NOT EXISTS options_key_uindex on internal_options (option_key)";

        $connection = $this->getConnection();
        foreach ($sqlStatements as $sqlStatement) {
            $results = $connection->execute($sqlStatement)->fetchAll('assoc');
        }

        $vals = [
            ['option_key' => 'hk', 'option_value' => $hk, 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'hs', 'option_value' => $hs, 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'company_name', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'street', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'suburb', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'state', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'postcode', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'phone', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'web', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
            ['option_key' => 'email', 'option_value' => '-', 'is_masked' => 0, 'apply_mask' => 0,],
        ];

        foreach ($vals as $val) {
            $ent = $this->newEntity($val);
            $this->save($ent);
        }

        Cache::write('first_run', true, 'quick_burn');

        $this->loadInternalOptions();
        $this->encryptOptions();
    }

    /**
     * Load InternalOptions into Configure for fast easy access.
     *
     * Looks at the Cache first and if available, pushes to Configure
     * Failing a Cache read, it looks at the DB and pushes to Cache and Configure
     *
     * If you need the freshest values from the DB,
     * call $this->dumpInternalOptions() before calling this method.
     *
     * @return null|false|InternalOption[]
     */
    public function loadInternalOptions(): bool|array|null
    {
        /**
         * @var null|false|InternalOption[] $options
         */

        //read from Cache to speed up expensive DB read
        $options = Cache::read('InternalOptions', 'query_results_app');

        //if Cache expired/empty read from DB and push back to Cache for next time
        if (empty($options)) {
            //read from DB
            $options = $this->find('all')
                ->select(['id', 'option_key', 'option_value', 'is_masked'])
                ->orderByAsc('id');

            //reformat
            $tmpOptions = [];
            foreach ($options as $option) {
                $tmpOptions[$option->option_key] = $option;
            }
            $options = $tmpOptions;

            //save for later
            try {
                Cache::write('InternalOptions', $options, 'query_results_app');
            } catch (\Throwable $e) {
                // Do not halt - not critical
            }
        }

        $optionsList = [];

        /**
         * @var \App\Model\Entity\InternalOption $option
         */
        foreach ($options as $option) {
            $value = $option->option_value;

            if ($value === 'false') {
                $value = false;
            }

            if ($value === 'true') {
                $value = true;
            }

            if ($value === 'null') {
                $value = null;
            }

            $optionsList[$option->option_key] = $value;
        }

        $optionsList['key'] = $this->getSecurityKey();
        $optionsList['salt'] = $this->getSecuritySalt();

        Configure::write('InternalOptions', $optionsList);;

        return $options;
    }

    /**
     * Clear the Cache and Configure of values [opposite of $this->loadInternalOptions()].
     * Will automatically rebuild on next request.
     */
    public function dumpInternalOptions()
    {
        Configure::delete("InternalOptions");
        Cache::delete('InternalOptions', 'query_results_app');
    }

    /**
     * Convenience method
     *
     * @return string
     */
    public function getSecurityKey(): string
    {
        $hk = Configure::read("InternalOptions.hk.option_value");
        if (empty($hk)) {
            $hk = ($this->findByOptionKey('hk')->first())->option_value;
        }

        return $hk;
    }


    /**
     * Convenience method
     *
     * @return string
     */
    public function getSecuritySalt(): string
    {
        $hs = Configure::read("InternalOptions.hs.option_value");
        if (empty($hs)) {
            $hs = ($this->findByOptionKey('hs')->first())->option_value;
        }

        return $hs;
    }

    /**
     * @param string $string
     * @return bool
     */
    public function updateSecuritySalt(string $string): bool
    {
        $count = 0;
        if (strlen($string) >= 40) {
            Configure::write("InternalOptions.hs.option_value", $string);
            $count = $this->updateAll(['option_value' => $string], ['option_key' => 'hs']);
        }

        return asBool($count);
    }

    /**
     * @param string $string
     * @return bool
     */
    public function updateSecurityKey(string $string): bool
    {
        $count = 0;
        if (strlen($string) >= 40) {
            Configure::write("InternalOptions.hk.option_value", $string);
            $count = $this->updateAll(['option_value' => $string], ['option_key' => 'hk']);
        }

        return asBool($count);
    }


    /**
     * Convenience Method to get a value based on the passed in key.
     *
     * @param string $optionKey
     * @param bool $autoDecrypt
     * @return bool|string|null
     */
    public function getOption(string $optionKey, bool $autoDecrypt = true): bool|string|null
    {
        /**
         * @var InternalOption[] $storedOptions
         */

        //try to get the value from Configure first
        $storedOptions = Configure::read("InternalOptions");

        //re-populate Configure
        if (empty($storedOptions)) {
            $storedOptions = $this->loadInternalOptions();
        }

        $return = null;

        if (isset($storedOptions[$optionKey])) {
            $option = $storedOptions[$optionKey];
            if ($autoDecrypt == true && $option->is_masked == 1) {
                $return = Security::decrypt64($option->option_value);
            } else {
                $return = $option->option_value;
            }
        }

        return $return;
    }

    /**
     * Convenience Method to set a value based on the passed in key
     * Can only be used to update.
     *
     * @param $optionKey
     * @param $optionValue
     * @return InternalOption|bool
     */
    public function setOption($optionKey, $optionValue): InternalOption|bool
    {
        /**
         * @var InternalOption $ent
         */
        $ent = $this->find('all')->where(['option_key' => $optionKey])->first();

        //update
        if ($ent) {
            if ($ent->is_masked == 1) {
                $ent->option_value = Security::encrypt64($ent->option_value);
            } else {
                $ent->option_value = $optionValue;
            }
            $ent->apply_mask = 0;

            $this->dumpInternalOptions();

            return $this->save($ent);
        } else {
            return false;
        }
    }

    /**
     * Encrypt Options where 'apply_mask' == true
     *
     * @return InternalOption|bool
     */
    public function encryptOptions()
    {
        /**
         * @var InternalOption $ent
         */

        $rows = $this->findByApplyMask(1)->toArray();

        $result = true;
        foreach ($rows as $ent) {
            $ent->option_value = Security::encrypt64($ent->option_value);
            $ent->is_masked = 1;
            $ent->apply_mask = 0;
            $resultOfSave = $this->save($ent);

            if (!$resultOfSave) {
                $result = false;
            }
        }

        $this->dumpInternalOptions();

        return $result;
    }


    /**
     * Convenience Method to get all the AuthorText details
     *
     * @return array
     */
    public function getAuthorText()
    {
        $fields = [
            'company_name',
            'street',
            'suburb',
            'state',
            'postcode',
            'phone',
            'web',
            'email',
        ];

        $fieldsPopulated = [];
        foreach ($fields as $field) {
            $fieldsPopulated[$field] = Configure::read("InternalOptions.{$field}.option_value");
        }

        return $fieldsPopulated;
    }

}
