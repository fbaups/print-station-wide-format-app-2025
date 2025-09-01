<?php

namespace App\Model\Table;

use App\Model\Entity\Setting;
use App\Utility\Instances\Checker;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 * @method \App\Model\Entity\Setting get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Setting newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Setting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Setting|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Setting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Setting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Setting[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Setting findOrCreate($search, callable $callback = null, $options = [])
 *
 * @method \Cake\ORM\Query findByPropertyKey(string $propertyKey) Find based on property_key
 * @method \Cake\ORM\Query findByPropertyGroup(string $propertyGroup) Find based on property_group
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SettingsTable extends AppTable
{
    public bool $isDbReady = true;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('settings');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        if ($this->getConnection()->config()['driver'] === 'Dummy') {
            $this->isDbReady = false;
        }

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
            ->scalar('name')
            ->maxLength('name', 50)
            ->requirePresence('name', 'create')
            ->allowEmptyString('name', null, false);

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->scalar('property_group')
            ->maxLength('property_group', 50)
            ->requirePresence('property_group', 'create')
            ->allowEmptyString('property_group', null, false);

        $validator
            ->scalar('property_key')
            ->maxLength('property_key', 50)
            ->requirePresence('property_key', 'create')
            ->allowEmptyString('property_key', null, false);

        $validator
            ->scalar('property_value')
            ->maxLength('property_value', 4000)
            ->allowEmptyString('property_value');

        $validator
            ->scalar('selections')
            ->maxLength('property_value', 2048)
            ->allowEmptyString('selections');

        $validator
            ->scalar('html_select_type')
            ->maxLength('html_select_type', 50)
            ->allowEmptyString('html_select_type');

        $validator
            ->scalar('match_pattern')
            ->maxLength('match_pattern', 50)
            ->allowEmptyString('match_pattern');

        $validator
            ->boolean('is_masked')
            ->allowEmptyString('is_masked');

        return $validator;
    }

    /**
     * Push all the Settings into Configure.
     * Uses Cache to speed up DB read.
     *
     * @param bool $readFromCache
     * @return void
     */
    public function saveSettingsToConfigure(bool $readFromCache = true)
    {
        if (!$this->isDbReady) {
            return;
        }

        $settings = false;

        //read from Cache to speed up expensive DB read
        if ($readFromCache) {
            $settings = Cache::read('settings', 'query_results_app');
        }

        //if Cache has expired read from DB and push back to Cache for next time
        if (!$settings) {
            $settings = $this->find('all')
                ->select(['id', 'property_key', 'property_value', 'property_group', 'is_masked'])
                ->orderByAsc('property_group')
                ->orderByAsc('id')
                ->toArray();
            try {
                Cache::write('settings', $settings, 'query_results_app');
            } catch (\Throwable $e) {
                // Do not halt - not critical
            }
        }

        $settingsList = [];
        $settingsGrouped = [];
        $settingsEncrypted = [];

        /**
         * @var \App\Model\Entity\Setting $setting
         */
        foreach ($settings as $setting) {
            $value = $setting->property_value;

            if ($value === 'false') {
                $value = false;
            }

            if ($value === 'true') {
                $value = true;
            }

            if ($value === 'null') {
                $value = null;
            }

            $settingsList[$setting->property_key] = $value;
            $settingsGrouped[$setting->property_group][$setting->property_key] = $value;
            if ($setting->is_masked == true) {
                $settingsEncrypted[$setting->property_key] = true;
            } else {
                $settingsEncrypted[$setting->property_key] = false;
            }
        }

        Configure::write('Settings', $settingsList);
        Configure::write('SettingsGrouped', $settingsGrouped);
        Configure::write('SettingsMasked', $settingsEncrypted);
    }

    /**
     * Clear the Cache of the Settings query_results_app
     */
    public function clearCache()
    {
        Cache::delete('settings', 'query_results_app');
    }


    /**
     * Convenience Method to get a value based on the passed in key
     *
     * @param string $propertyKey
     * @param bool $autoDecrypt
     * @return bool|null|string
     */
    public function getSetting(string $propertyKey, bool $autoDecrypt = true): bool|string|null
    {
        if (!$this->isDbReady) {
            return null;
        }

        /**
         * @var Setting $setting
         */

        //try to get the value from Configure first
        $configValue = Configure::read("Settings.{$propertyKey}");
        $configIsMasked = Configure::read("SettingsMasked.{$propertyKey}");
        if ($configValue !== null) {
            if ($autoDecrypt == true) {
                if ($configIsMasked == true) {
                    if (strlen($configValue) > 0) {
                        $configValue = Security::decrypt64($configValue);
                    }
                }
            }
            return $configValue;
        }

        //update the Configure values
        $this->saveSettingsToConfigure(false);

        //fallback to reading from DB
        $setting = $this->findByPropertyKey($propertyKey)->first();

        if (!$setting) {
            return false;
        }

        if (isset($setting->property_value)) {
            $configValue = $setting->property_value;
            if ($autoDecrypt == true) {
                if ($setting->is_masked == true) {
                    if (strlen($configValue) > 0) {
                        $configValue = Security::decrypt64($configValue);
                    }
                }
            }
        } else {
            $configValue = false;
        }

        if ($configValue === 'false') {
            $configValue = false;
        }

        if ($configValue === 'true') {
            $configValue = true;
        }

        if ($configValue === 'null') {
            $configValue = null;
        }

        return $configValue;
    }

    /**
     * Convenience Method to set a value based on the passed in key
     * Can only be used to update.
     *
     * @param string|Setting $propertyKeyOrEntity
     * @param string $propertyValue
     * @param bool $autoEncrypt
     * @return bool|Setting
     */
    public function setSetting(string|Setting $propertyKeyOrEntity, string $propertyValue, bool $autoEncrypt = true): bool|Setting
    {
        //make sure the cache is clear of previous settings
        $this->clearCache();

        /**
         * @var Setting $setting
         */
        if ($propertyKeyOrEntity instanceof Setting) {
            $setting = $propertyKeyOrEntity;
        } else {
            $setting = $this->findByPropertyKey($propertyKeyOrEntity)->first();
        }

        if ($setting) {
            //if no change save hitting DB and exit (regular values)
            if ($propertyValue === $setting->property_value) {
                return true;
            }

            //if no change save hitting DB and exit (masked values)
            if ($setting->is_masked == true) {
                $oldMaskedValue = $setting->property_value;
                $oldMaskedValueSha1 = sha1($oldMaskedValue); //Form Views will SHA1 to prevent exposure of value
                $oldUnmaskedValue = Security::decrypt64($oldMaskedValue);
                if ($propertyValue === $oldMaskedValue || $propertyValue === $oldMaskedValueSha1 || $propertyValue === $oldUnmaskedValue) {
                    return true;
                }
            }

            if ($setting->html_select_type == 'multiple') {
                if (is_array($propertyValue)) {
                    $propertyValue = implode(',', $propertyValue);
                }
            }

            if ($autoEncrypt == true) {
                if ($setting->is_masked == true) {
                    if (strlen($propertyValue) > 0) {
                        $propertyValue = Security::encrypt64($propertyValue);
                    }
                }
            }

            $setting->property_value = $propertyValue;
            $result = $this->save($setting);

            //update Configure values
            $this->saveSettingsToConfigure(false);

            return $result;
        } else {
            return false;
        }
    }

    /**
     * Convenience Method to set lots of values.
     * Pass in a simple array of key/value pairs.
     * Can only be used to update.
     *
     * @param array $dataToSave
     * @return bool
     */
    public function setSettings(array $dataToSave): bool
    {
        $saveResult = true;
        $propertyKeys = $this->listPropertyKeys();
        foreach ($dataToSave as $settingName => $settingValue) {
            if (in_array($settingName, $propertyKeys)) {
                $result = $this->setSetting($settingName, $settingValue);
                if (!$result) {
                    $saveResult = false;
                }
            }
        }

        return $saveResult;
    }

    /**
     * Convenience Method to get a simple list of masked properties
     *
     * @return array
     */
    public function listMaskedKeys(): array
    {
        $results = $this->find('list', valueField: 'property_key')
            ->where(['is_masked' => true])
            ->toArray();
        return array_values($results);
    }

    /**
     * Convenience Method to get a simple list of property_key
     *
     * @return array
     */
    public function listPropertyKeys(): array
    {
        $results = $this->find('list', valueField: 'property_key')->toArray();
        return array_values($results);
    }

    /**
     * Convenience Method to get a simple list of property_group
     *
     * @return array
     */
    public function listPropertyGroups(): array
    {
        $results = $this->find('list', valueField: 'property_group')->toArray();
        return array_values(array_unique($results));
    }

    /**
     * Convenience Method to get all the Email details
     *
     * @return array
     */
    public function getEmailDetails(): array
    {
        //try to read from Configure First
        $results = Configure::read("SettingsGrouped.email_server");
        if ($results) {
            $results['email_password'] = Security::decrypt64($results['email_password']);
            return $results;
        }

        //fall back to read from DB
        $results = $this->find('list', keyField: 'property_key', valueField: 'property_value')
            ->where(['property_group' => 'email_server'])
            ->toArray();
        $results['email_password'] = Security::decrypt64($results['email_password']);
        return $results;
    }

    /**
     * Convenience Method to get base Email domain
     *
     * @param string|null $fallback
     * @return string
     */
    public function getEmailDomain(string $fallback = null): string
    {
        if (empty($fallback)) {
            $fallback = 'localhost.com';
        }

        $results = $this->getEmailDetails();

        if (!isset($results['email_from_address'])) {
            return $fallback;
        }

        $emailParts = explode('@', $results['email_from_address']);
        if (!isset($emailParts[1])) {
            return $fallback;
        }

        if (strlen($emailParts[1]) === 0) {
            return $fallback;
        }

        return $emailParts[1];
    }

    /**
     * Convenience Method to get the email address formatted for Mailer class
     * ['<email-address>' => '<full-name>']
     *
     * @return array|false
     */
    public function getEmailForCakePhpMailer(): bool|array
    {
        $results = $this->getEmailDetails();

        if (isset($results['email_from_name']) && isset($results['email_from_address'])) {
            return [$results['email_from_address'] => $results['email_from_name']];
        }

        return false;
    }

    /**
     * Return a DateTime object for a password expiry date.
     * Date is based on the password_expiry setting.
     *
     * @return DateTime
     */
    public function getPasswordExpiryDate(): DateTime
    {
        $days = $this->getSetting('password_reset_days');

        //fallback
        if ($days <= 0) {
            $days = 365;
        }

        $frozenTimeObj = (new DateTime('+' . $days . ' days'))->endOfDay();

        return $frozenTimeObj;
    }

    /**
     * Return a DateTime object for a password expiry date that has passed.
     * Useful for when a password needs to have a force reset.
     *
     * @return DateTime
     */
    public function getExpiredPasswordExpiryDate(): DateTime
    {
        $frozenTimeObj = (new DateTime('-10 days'))->startOfDay();

        return $frozenTimeObj;
    }

    /**
     * Return a DateTime object for a user account activation date.
     * Date is start of today.
     *
     * @return DateTime
     */
    public function getAccountActivationDate(): DateTime
    {
        $frozenTimeObj = (new DateTime())->startOfDay();

        return $frozenTimeObj;
    }

    /**
     * Return a DateTime object for a user account expiration date.
     * Date is based on the account_expiry setting.
     *
     * @return DateTime
     */
    public function getAccountExpirationDate(): DateTime
    {
        $days = $this->getSetting('account_expiry');

        //fallback
        if ($days <= 0) {
            $days = 365;
        }

        $frozenTimeObj = (new DateTime('+' . $days . ' days'))->endOfDay();

        return $frozenTimeObj;
    }

    /**
     * Return a DateTime object for the default expiration date.
     * Date is based on the data_purge setting.
     *
     * @return DateTime
     */
    public function getDefaultActivationDate(): DateTime
    {
        $frozenTimeObj = (new DateTime())->startOfDay();

        return $frozenTimeObj;
    }

    /**
     * Return a DateTime object for the default expiration date.
     * Date is based on the data_purge setting.
     *
     * @return DateTime
     */
    public function getDefaultExpirationDate(): DateTime
    {
        $days = $this->getSetting('data_purge');

        //fallback
        if ($days <= 0) {
            $days = 365 * 10;
        }

        $frozenTimeObj = (new DateTime('+' . $days . ' days'))->endOfDay();

        return $frozenTimeObj;
    }

    /**
     * Check if the passed in domain is in the whitelist.
     * Whitelist filtering will not be employed if whitelist is empty.
     *
     * @param $domain
     * @return bool
     */
    public function isDomainWhitelisted($domain): bool
    {
        try {
            //catch if this is first-run or installation
            $loginDomainWhitelist = $this->getSetting('login_domain_whitelist');
        } catch (\Throwable) {
            return true;
        }

        if (empty($loginDomainWhitelist)) {
            return true;
        }

        $loginDomainWhitelist = trim($loginDomainWhitelist);
        if (empty($loginDomainWhitelist)) {
            return true;
        }

        $loginDomainWhitelist = str_replace(["\r\n", "\r", "\n"], ",", $loginDomainWhitelist);
        $loginDomainWhitelist = explode(",", $loginDomainWhitelist);

        if (in_array($domain, $loginDomainWhitelist)) {
            return true;
        }

        return false;
    }


    /**
     * Convenience function to set the Repository settings.
     * Included some basic validation.
     *
     * @param $repoSettings
     * @return bool
     */
    public function setRepositoryDetails($repoSettings): bool
    {
        if (isset($repoSettings['repo_unc'])) {
            $s = TextFormatter::makeEndsWith($repoSettings['repo_unc'], "\\");
            $this->setSetting('repo_unc', $s);
        }

        if (isset($repoSettings['repo_url'])) {
            $s = TextFormatter::makeEndsWith($repoSettings['repo_url'], "/");
            $this->setSetting('repo_url', $s);
        }

        if (isset($repoSettings['repo_mode'])) {
            $s = strtolower($repoSettings['repo_mode']);
            if ($s !== 'dynamic' && $s !== 'static') {
                $s = 'static';
            }
            $this->setSetting('repo_mode', $s);
        }

        if (isset($repoSettings['repo_purge'])) {
            $s = intval($repoSettings['repo_purge']);
            if ($s < 3 || $s > 600) {
                $s = 12;
            }
            $this->setSetting('repo_purge', $s);
        }

        if (isset($repoSettings['database_purger_background_service_life_expectancy'])) {
            $s = intval($repoSettings['database_purger_background_service_life_expectancy']);
            if ($s < 5 || $s > 30) {
                $s = 10;
            }
            $this->setSetting('database_purger_background_service_life_expectancy', $s);
        }

        if (isset($repoSettings['database_purger_background_service_limit'])) {
            $s = intval($repoSettings['database_purger_background_service_limit']);
            if ($s < 1 || $s > 5) {
                $s = 1;
            }
            $this->setSetting('database_purger_background_service_limit', $s);
        }

        if (isset($repoSettings['repo_sftp_host'])) {
            $s = $repoSettings['repo_sftp_host'];
            $this->setSetting('repo_sftp_host', $s);
        }

        if (isset($repoSettings['repo_sftp_port'])) {
            $s = intval($repoSettings['repo_sftp_port']);
            if ($s < 1 || $s > 64000) {
                $s = 22;
            }
            $this->setSetting('repo_sftp_port', $s);
        }

        if (isset($repoSettings['repo_sftp_username'])) {
            $s = $repoSettings['repo_sftp_username'];
            $this->setSetting('repo_sftp_username', $s);
        }

        if (isset($repoSettings['repo_sftp_password'])) {
            $s = $repoSettings['repo_sftp_password'];
            $this->setSetting('repo_sftp_password', $s);
        }

        if (isset($repoSettings['repo_sftp_timeout'])) {
            $s = intval($repoSettings['repo_sftp_timeout']);
            if ($s < 1 || $s > 10) {
                $s = 2;
            }
            $this->setSetting('repo_sftp_timeout', $s);
        }

        if (isset($repoSettings['repo_sftp_path'])) {
            $s = $repoSettings['repo_sftp_path'];
            $this->setSetting('repo_sftp_path', $s);
        }

        if (isset($repoSettings['repo_size_icon'])) {
            $s = intval($repoSettings['repo_size_icon']);
            if ($s < 32 || $s > 256) {
                $s = 128;
            }
            $this->setSetting('repo_size_icon', $s);
        }

        if (isset($repoSettings['repo_size_thumbnail'])) {
            $s = intval($repoSettings['repo_size_thumbnail']);
            if ($s < 256 || $s > 512) {
                $s = 256;
            }
            $this->setSetting('repo_size_thumbnail', $s);
        }

        if (isset($repoSettings['repo_size_preview'])) {
            $s = intval($repoSettings['repo_size_preview']);
            if ($s < 512 || $s > 1024) {
                $s = 512;
            }
            $this->setSetting('repo_size_preview', $s);
        }

        if (isset($repoSettings['repo_size_lr'])) {
            $s = intval($repoSettings['repo_size_lr']);
            if ($s < 1024 || $s > 1600) {
                $s = 1024;
            }
            $this->setSetting('repo_size_lr', $s);
        }

        if (isset($repoSettings['repo_size_mr'])) {
            $s = intval($repoSettings['repo_size_mr']);
            if ($s < 1600 || $s > 2400) {
                $s = 1600;
            }
            $this->setSetting('repo_size_mr', $s);
        }

        if (isset($repoSettings['repo_size_hr'])) {
            $s = intval($repoSettings['repo_size_hr']);
            if ($s < 2400 || $s > 4800) {
                $s = 2400;
            }
            $this->setSetting('repo_size_hr', $s);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getRepoSizes(): array
    {
        $sizes = [];
        $query = $this->find('all')->where(["property_key LIKE 'repo_size_%'", "property_group" => "repository",]);
        /** @var Setting $result */
        foreach ($query as $result) {
            $sizes[$result->property_key] = $result->property_value;
        }

        return $sizes;
    }

    /**
     * @param bool $forceRefresh
     * @return array
     */
    public function checkRepositoryDetails(bool $forceRefresh = false): array
    {
        $cachedDetails = Cache::read('checkRepositoryDetails', 'quick_burn');
        if (!$forceRefresh && $cachedDetails) {
            return $cachedDetails;
        }

        $repo_url = TextFormatter::makeEndsWith($this->getSetting('repo_url'), "/");

        $repo_unc = $this->getSetting('repo_unc');
        $repo_sftp_host = $this->getSetting('repo_sftp_host');
        $repo_sftp_port = $this->getSetting('repo_sftp_port');
        $repo_sftp_username = $this->getSetting('repo_sftp_username');
        $repo_sftp_password = $this->getSetting('repo_sftp_password');
        $repo_sftp_timeout = $this->getSetting('repo_sftp_timeout');
        $repo_sftp_path = $this->getSetting('repo_sftp_path');

        $sftpRoundTripSettings = [
            'url' => $repo_url,
            'host' => $repo_sftp_host,
            'port' => $repo_sftp_port,
            'username' => $repo_sftp_username,
            'password' => $repo_sftp_password,
            'timeout' => $repo_sftp_timeout,
            'path' => $repo_sftp_path,
        ];

        $uncRoundTripSettings = [
            'url' => $repo_url,
            'unc' => $repo_unc,
        ];

        $urlSettings = [
            'url' => $repo_url,
        ];

        $Checker = new Checker();
        $isURL = $Checker->checkUrlSettings($urlSettings);
        $isUNC = $Checker->checkUncSettings($uncRoundTripSettings);
        if (!$isUNC) {
            $isSFTP = $Checker->checkSftpSettings($sftpRoundTripSettings);
        } else {
            $isSFTP = null;
        }

        $cachedDetails = [
            'repo_url' => $repo_url,
            'repo_unc' => $repo_unc,
            'repo_sftp_host' => $repo_sftp_host,
            'repo_sftp_port' => $repo_sftp_port,
            'repo_sftp_username' => $repo_sftp_username,
            'repo_sftp_password' => $repo_sftp_password,
            'repo_sftp_timeout' => $repo_sftp_timeout,
            'repo_sftp_path' => $repo_sftp_path,
            'isURL' => $isURL,
            'isSFTP' => $isSFTP,
            'isUNC' => $isUNC,
            'remoteUpdateDebug' => $Checker->getMessages(),
        ];

        Cache::write('checkRepositoryDetails', $cachedDetails, 'quick_burn');

        return $cachedDetails;
    }

    /**
     * Convenience function to get the remote update url
     *
     * @return string
     */
    public function getRemoteUpdateUrl(): string
    {
        return TextFormatter::makeDirectoryTrailingForwardSlash($this->getSetting('remote_update_url'));
    }

}
