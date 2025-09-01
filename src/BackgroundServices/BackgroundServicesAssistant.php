<?php

namespace App\BackgroundServices;

use App\Model\Table\BackgroundServicesTable;
use App\Model\Table\SettingsTable;
use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Instances\InstanceTasks;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Throwable;

/**
 * BackgroundServicesAssistant
 *
 * The main states of a Service are:
 *  STOPPED:                The service is not running.
 *  START_PENDING:          The service is about to start.
 *  STOP_PENDING:           The service is about to stop.
 *  RUNNING:                The service is running.
 *  PAUSED:                 The service is paused. (Some services support pausing.)
 *  CONTINUE_PENDING:       The service is about to continue from a paused state.
 *  PAUSE_PENDING:          The service is about to pause.
 *  UNKNOWN:                The service state cannot be determined.
 *
 * @property BackgroundServicesTable $BackgroundServices
 */
class BackgroundServicesAssistant
{
    use ReturnAlerts;

    private array $servicesThatWereRunning; //hold a list of running services so that we can restart only them.
    private array|null $servicesCache = null; //simple cache of Windows Services
    private Table|BackgroundServicesTable $BackgroundServices;
    private array|null $statsCache = null; //simple cache

    public string $batchLocation;
    public string $serviceInstallFile;
    public string $serviceRemoveFile;
    public string $serviceStartFile;
    public string $serviceStopFile;
    public string $serviceTestFile;


    public string $nssm;
    public bool $isNssm;

    public function __construct()
    {
        $this->BackgroundServices = TableRegistry::getTableLocator()->get('BackgroundServices');

        $this->batchLocation = ROOT . DS . 'bin' . DS . 'BackgroundServices' . DS;
        $this->nssm = $this->batchLocation . 'nssm.exe';
        if (is_file($this->nssm)) {
            $this->isNssm = true;
        } else {
            $this->isNssm = false;
        }

        $this->serviceInstallFile = $this->batchLocation . 'services_install.bat';
        $this->serviceRemoveFile = $this->batchLocation . 'services_uninstall.bat';
        $this->serviceStartFile = $this->batchLocation . 'services_start.bat';
        $this->serviceStopFile = $this->batchLocation . 'services_stop.bat';
        $this->serviceTestFile = $this->batchLocation . 'services_test_commands.bat';
    }

    public function getAppNameCamelized(): string
    {
        $camelName = Inflector::camelize(APP_NAME);
        $tagId = $this->getTagId();
        return "{$camelName}_{$tagId}";
    }

    private function getTagId(): string
    {
        return strtolower(substr(sha1(Security::getSalt()), 0, 6));
    }

    /**
     * @param array $options
     * @return bool
     */
    public function installBackgroundServices(array $options = []): bool
    {
        $commands = $this->generateCommands($options);

        if ($this->areProtectionServicesRunning()) {

            $this->BackgroundServices->deleteAll(['id <> 0']);
            $this->addInfoAlerts(__("This computer has protection services running. Please run the Installer Script located in the .\bin directory."));

            $this->writeCommands($commands['install'], $this->serviceInstallFile);

            return true;
        } elseif ($this->isWindowsAdmin()) {

            $this->executeCommands($commands['uninstall']);
            $this->executeCommands($commands['install']);
            $this->checkServicesAreInstalled($commands['servicesNames']);

            $this->writeCommands($commands['start'], $this->serviceStartFile);
            $this->writeCommands($commands['stop'], $this->serviceStopFile);
            $this->writeCommands($commands['test'], $this->serviceTestFile);


            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function uninstallBackgroundServices(): bool
    {
        $commands = $this->generateCommands();

        if ($this->areProtectionServicesRunning()) {

            $this->BackgroundServices->deleteAll(['id <> 0']);
            $this->addInfoAlerts(__("This computer has protection services running. Please run the Uninstaller Script located in the .\bin directory."));

            $this->writeCommands($commands['uninstall'], $this->serviceRemoveFile);

            return true;
        } elseif ($this->isWindowsAdmin()) {

            $this->BackgroundServices->deleteAll(['id <> 0']);
            $this->executeCommands($commands['uninstall']);

            return true;
        } else {
            return false;
        }
    }


    /**
     * @param $commands
     * @return void
     */
    private function executeCommands($commands): void
    {
        foreach ($commands as $cmd) {
            $out = null;
            $ret = null;
            exec($cmd, $out, $ret);
            $this->addSmartAlerts($out);
        }
    }


    /**
     * @param $commands
     * @param $location
     * @return false|int
     */
    private function writeCommands($commands, $location): false|int
    {
        $commandsContent = implode("\r\n", $commands);
        return file_put_contents($location, $commandsContent);
    }


    /**
     * @param $expectedServicesNames
     * @return bool
     */
    public function checkServicesAreInstalled($expectedServicesNames): bool
    {
        $newlyInstalledServices = $this->_getServices(true);
        foreach ($newlyInstalledServices as $k => $service) {
            $newlyInstalledServices[$k] = $service['name'];
        }
        asort($newlyInstalledServices);
        $newlyInstalledServices = array_values($newlyInstalledServices);

        asort($expectedServicesNames);
        $expectedServicesNames = array_values($expectedServicesNames);

        if (json_encode($newlyInstalledServices) == json_encode($expectedServicesNames)) {
            foreach ($newlyInstalledServices as $k => $newlyInstalledService) {
                $counter = $k + 1;
                $count = count($newlyInstalledServices);
                $countText = "#$counter of $count: ";
                $this->addSuccessAlerts(__("{1} Service \"{0}\" installed successfully!", $newlyInstalledService, $countText));
            }
            return true;
        } else {
            $this->addInfoAlerts(__("Please run the Installer Script located in the .\bin directory."));
            return false;
        }
    }


    /**
     * Create the commands that installs/uninstalls/stops/starts the services
     *
     * @param array $options
     * @return array
     */
    public function generateCommands(array $options = []): array
    {
        $defaultOptions = [
            'php_version' => '',
            'username' => '',
            'password' => '',
            'service_start' => 'SERVICE_DEMAND_START',
            'errand_background_service_limit' => 4,
            'message_background_service_limit' => 4,
            'database_purger_background_service_limit' => 2,
            'hot_folder_background_service_limit' => 2,
            'scheduled_task_background_service_limit' => 2,
        ];

        $options = array_merge($defaultOptions, $options);

        $appName = $this->getAppNameCamelized();

        $nssm = $this->nssm;

        $phpVersion = $options['php_version'];
        $username = $options['username'];
        $password = $options['password'];
        $serviceStartMode = $options['service_start'];

        if (empty($phpVersion) || !is_file($phpVersion)) {
            $phpExe = (new InstanceTasks)->getPhpBinary();
        } else {
            $phpExe = $phpVersion;
        }
        $phpLocation = pathinfo($phpExe, PATHINFO_DIRNAME);
        $binDirectory = ROOT . DS . 'bin' . DS;

        $commandsInstall = [];
        $commandsRemove = [];
        $commandsStart = [];
        $commandsStop = [];
        $commandsTest = [];

        $expectedServicesNames = [];

        $dbEntries = [];
        $defaultOptionsDbEntries = [
            'server' => null,
            'domain' => null,
            'name' => null,
            'type' => null,
            'pid' => null,
            'current_state' => 'initialised',
            'appointment_date' => null,
            'retirement_date' => null,
            'termination_date' => null,
            'force_recycle' => null,
            'force_shutdown' => null,
            'errand_link' => null,
            'errand_name' => null,
        ];


        //------START Errands-------------------------------------------------------------
        $backgroundServiceTypeLowerCased = 'errand';
        $backgroundServiceLimit = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_limit");
        if ($backgroundServiceLimit > 0) {
            $serviceCountInterval = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_life_expectancy");
            $offset = ($serviceCountInterval / $backgroundServiceLimit) * 2;
            foreach (range(1, $backgroundServiceLimit) as $counter) {
                $counterPadded = str_pad($counter, 2, '0', STR_PAD_LEFT);
                $delay = intval($offset * ($counter - 1));
                $serviceName = $appName . "_Errands_" . $counterPadded;
                $serviceDescription = "Errands for " . APP_NAME;
                $parameters = __("-f \"\"\"{0}cake.php\"\"\" ServiceErrands -d {1} -h {2}", $binDirectory, $delay, $serviceName);
                $commandsInstall[] = __("\"{0}\" install \"{1}\" \"{2}\"", $nssm, $serviceName, $phpExe);
                if (strlen($username) > 0 && strlen($password) > 0) {
                    $commandsInstall[] = __("\"{0}\" set \"{1}\" ObjectName \"{2}\" \"{3}\"", $nssm, $serviceName, $username, $password);
                }
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Application \"{2}\"", $nssm, $serviceName, $phpExe);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppDirectory \"{2}\"", $nssm, $serviceName, $phpLocation);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppParameters {2}", $nssm, $serviceName, $parameters);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" DisplayName \"{2}\"", $nssm, $serviceName, $serviceName);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Description \"{2}\"", $nssm, $serviceName, $serviceDescription);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Start {2}", $nssm, $serviceName, $serviceStartMode);
                if ($serviceStartMode == "SERVICE_AUTO_START" || $serviceStartMode == "SERVICE_DELAYED_START") {
                    $commandsInstall[] = __("net start \"{0}\"", $serviceName);
                }
                $commandsRemove[] = __("net stop \"{0}\"", $serviceName);
                $commandsRemove[] = __("\"{0}\" remove \"{1}\" confirm", $nssm, $serviceName);
                $commandsStart[] = __("net start \"{0}\"", $serviceName);
                $commandsStop[] = __("net stop \"{0}\"", $serviceName);
                $commandsTest[] = __("rem \"{0}\" {1}", $phpExe, $parameters);

                $expectedServicesNames[] = $serviceName;

                $dbEntryOptions = $defaultOptionsDbEntries;
                $dbEntryOptions['type'] = $backgroundServiceTypeLowerCased;
                $dbEntryOptions['name'] = $serviceName;
                $dbEntries[$serviceName] = $dbEntryOptions;
            }
        }
        //------END Errands-------------------------------------------------------------


        //------START Messages-------------------------------------------------------------
        $backgroundServiceTypeLowerCased = 'message';
        $backgroundServiceLimit = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_limit");
        if ($backgroundServiceLimit > 0) {
            $serviceCountInterval = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_life_expectancy");
            $offset = ($serviceCountInterval / $backgroundServiceLimit) * 2;
            foreach (range(1, $backgroundServiceLimit) as $counter) {
                $counterPadded = str_pad($counter, 2, '0', STR_PAD_LEFT);
                $delay = intval($offset * ($counter - 1));
                $serviceName = $appName . "_Messages_" . $counterPadded;
                $serviceDescription = "Messages for " . APP_NAME;
                $parameters = __("-f \"\"\"{0}cake.php\"\"\" ServiceMessages -d {1} -h {2}", $binDirectory, $delay, $serviceName);
                $commandsInstall[] = __("\"{0}\" install \"{1}\" \"{2}\"", $nssm, $serviceName, $phpExe);
                if (strlen($username) > 0 && strlen($password) > 0) {
                    $commandsInstall[] = __("\"{0}\" set \"{1}\" ObjectName \"{2}\" \"{3}\"", $nssm, $serviceName, $username, $password);
                }
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Application \"{2}\"", $nssm, $serviceName, $phpExe);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppDirectory \"{2}\"", $nssm, $serviceName, $phpLocation);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppParameters {2}", $nssm, $serviceName, $parameters);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" DisplayName \"{2}\"", $nssm, $serviceName, $serviceName);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Description \"{2}\"", $nssm, $serviceName, $serviceDescription);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Start {2}", $nssm, $serviceName, $serviceStartMode);
                if ($serviceStartMode == "SERVICE_AUTO_START" || $serviceStartMode == "SERVICE_DELAYED_START") {
                    $commandsInstall[] = __("net start \"{0}\"", $serviceName);
                }
                $commandsRemove[] = __("net stop \"{0}\"", $serviceName);
                $commandsRemove[] = __("\"{0}\" remove \"{1}\" confirm", $nssm, $serviceName);
                $commandsStart[] = __("net start \"{0}\"", $serviceName);
                $commandsStop[] = __("net stop \"{0}\"", $serviceName);
                $commandsTest[] = __("rem \"{0}\" {1}", $phpExe, $parameters);

                $expectedServicesNames[] = $serviceName;

                $dbEntryOptions = $defaultOptionsDbEntries;
                $dbEntryOptions['type'] = $backgroundServiceTypeLowerCased;
                $dbEntryOptions['name'] = $serviceName;
                $dbEntries[$serviceName] = $dbEntryOptions;
            }
        }
        //------END Messages-------------------------------------------------------------


        //------START Database Purging-------------------------------------------------------------
        $backgroundServiceTypeLowerCased = 'database_purger';
        $backgroundServiceLimit = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_limit");
        if ($backgroundServiceLimit > 0) {
            $serviceCountInterval = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_life_expectancy");
            $offset = ($serviceCountInterval / $backgroundServiceLimit) * 2;
            foreach (range(1, $backgroundServiceLimit) as $counter) {
                $counterPadded = str_pad($counter, 2, '0', STR_PAD_LEFT);
                $delay = intval($offset * ($counter - 1));
                $serviceName = $appName . "_DatabasePurger_" . $counterPadded;
                $serviceDescription = "Database Purging for " . APP_NAME;
                $parameters = __("-f \"\"\"{0}cake.php\"\"\" ServiceDatabasePurger -d {1} -h {2}", $binDirectory, $delay, $serviceName);
                $commandsInstall[] = __("\"{0}\" install \"{1}\" \"{2}\"", $nssm, $serviceName, $phpExe);
                if (strlen($username) > 0 && strlen($password) > 0) {
                    $commandsInstall[] = __("\"{0}\" set \"{1}\" ObjectName \"{2}\" \"{3}\"", $nssm, $serviceName, $username, $password);
                }
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Application \"{2}\"", $nssm, $serviceName, $phpExe);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppDirectory \"{2}\"", $nssm, $serviceName, $phpLocation);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppParameters {2}", $nssm, $serviceName, $parameters);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" DisplayName \"{2}\"", $nssm, $serviceName, $serviceName);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Description \"{2}\"", $nssm, $serviceName, $serviceDescription);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Start {2}", $nssm, $serviceName, $serviceStartMode);
                if ($serviceStartMode == "SERVICE_AUTO_START" || $serviceStartMode == "SERVICE_DELAYED_START") {
                    $commandsInstall[] = __("net start \"{0}\"", $serviceName);
                }
                $commandsRemove[] = __("net stop \"{0}\"", $serviceName);
                $commandsRemove[] = __("\"{0}\" remove \"{1}\" confirm", $nssm, $serviceName);
                $commandsStart[] = __("net start \"{0}\"", $serviceName);
                $commandsStop[] = __("net stop \"{0}\"", $serviceName);
                $commandsTest[] = __("rem \"{0}\" {1}", $phpExe, $parameters);

                $expectedServicesNames[] = $serviceName;

                $dbEntryOptions = $defaultOptionsDbEntries;
                $dbEntryOptions['type'] = $backgroundServiceTypeLowerCased;
                $dbEntryOptions['name'] = $serviceName;
                $dbEntries[$serviceName] = $dbEntryOptions;
            }
        }
        //------END Database Purging-------------------------------------------------------------


        //------START HotFolders-------------------------------------------------------------
        $backgroundServiceTypeLowerCased = 'hot_folder';
        $backgroundServiceLimit = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_limit");
        if ($backgroundServiceLimit > 0) {
            $serviceCountInterval = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_life_expectancy");
            $offset = ($serviceCountInterval / $backgroundServiceLimit) * 2;
            foreach (range(1, $backgroundServiceLimit) as $counter) {
                $counterPadded = str_pad($counter, 2, '0', STR_PAD_LEFT);
                $delay = intval($offset * ($counter - 1));
                $serviceName = $appName . "_HotFolders_" . $counterPadded;
                $serviceDescription = "HotFolders for " . APP_NAME;
                $parameters = __("-f \"\"\"{0}cake.php\"\"\" ServiceHotFolders -d {1} -h {2}", $binDirectory, $delay, $serviceName);
                $commandsInstall[] = __("\"{0}\" install \"{1}\" \"{2}\"", $nssm, $serviceName, $phpExe);
                if (strlen($username) > 0 && strlen($password) > 0) {
                    $commandsInstall[] = __("\"{0}\" set \"{1}\" ObjectName \"{2}\" \"{3}\"", $nssm, $serviceName, $username, $password);
                }
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Application \"{2}\"", $nssm, $serviceName, $phpExe);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppDirectory \"{2}\"", $nssm, $serviceName, $phpLocation);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppParameters {2}", $nssm, $serviceName, $parameters);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" DisplayName \"{2}\"", $nssm, $serviceName, $serviceName);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Description \"{2}\"", $nssm, $serviceName, $serviceDescription);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Start {2}", $nssm, $serviceName, $serviceStartMode);
                if ($serviceStartMode == "SERVICE_AUTO_START" || $serviceStartMode == "SERVICE_DELAYED_START") {
                    $commandsInstall[] = __("net start \"{0}\"", $serviceName);
                }
                $commandsRemove[] = __("net stop \"{0}\"", $serviceName);
                $commandsRemove[] = __("\"{0}\" remove \"{1}\" confirm", $nssm, $serviceName);
                $commandsStart[] = __("net start \"{0}\"", $serviceName);
                $commandsStop[] = __("net stop \"{0}\"", $serviceName);
                $commandsTest[] = __("rem \"{0}\" {1}", $phpExe, $parameters);

                $expectedServicesNames[] = $serviceName;

                $dbEntryOptions = $defaultOptionsDbEntries;
                $dbEntryOptions['type'] = $backgroundServiceTypeLowerCased;
                $dbEntryOptions['name'] = $serviceName;
                $dbEntries[$serviceName] = $dbEntryOptions;
            }
        }
        //------END HotFolders-------------------------------------------------------------


        //------START ScheduledTasks-------------------------------------------------------------
        $backgroundServiceTypeLowerCased = 'scheduled_task';
        $backgroundServiceLimit = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_limit");
        if ($backgroundServiceLimit > 0) {
            $serviceCountInterval = Configure::read("Settings.{$backgroundServiceTypeLowerCased}_background_service_life_expectancy");
            $offset = ($serviceCountInterval / $backgroundServiceLimit) * 2;
            foreach (range(1, $backgroundServiceLimit) as $counter) {
                $counterPadded = str_pad($counter, 2, '0', STR_PAD_LEFT);
                $delay = intval($offset * ($counter - 1));
                $serviceName = $appName . "_ScheduledTasks_" . $counterPadded;
                $serviceDescription = "ScheduledTasks for " . APP_NAME;
                $parameters = __("-f \"\"\"{0}cake.php\"\"\" ServiceScheduledTasks -d {1} -h {2}", $binDirectory, $delay, $serviceName);
                $commandsInstall[] = __("\"{0}\" install \"{1}\" \"{2}\"", $nssm, $serviceName, $phpExe);
                if (strlen($username) > 0 && strlen($password) > 0) {
                    $commandsInstall[] = __("\"{0}\" set \"{1}\" ObjectName \"{2}\" \"{3}\"", $nssm, $serviceName, $username, $password);
                }
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Application \"{2}\"", $nssm, $serviceName, $phpExe);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppDirectory \"{2}\"", $nssm, $serviceName, $phpLocation);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" AppParameters {2}", $nssm, $serviceName, $parameters);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" DisplayName \"{2}\"", $nssm, $serviceName, $serviceName);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Description \"{2}\"", $nssm, $serviceName, $serviceDescription);
                $commandsInstall[] = __("\"{0}\" set \"{1}\" Start {2}", $nssm, $serviceName, $serviceStartMode);
                if ($serviceStartMode == "SERVICE_AUTO_START" || $serviceStartMode == "SERVICE_DELAYED_START") {
                    $commandsInstall[] = __("net start \"{0}\"", $serviceName);
                }
                $commandsRemove[] = __("net stop \"{0}\"", $serviceName);
                $commandsRemove[] = __("\"{0}\" remove \"{1}\" confirm", $nssm, $serviceName);
                $commandsStart[] = __("net start \"{0}\"", $serviceName);
                $commandsStop[] = __("net stop \"{0}\"", $serviceName);
                $commandsTest[] = __("rem \"{0}\" {1}", $phpExe, $parameters);

                $expectedServicesNames[] = $serviceName;

                $dbEntryOptions = $defaultOptionsDbEntries;
                $dbEntryOptions['type'] = $backgroundServiceTypeLowerCased;
                $dbEntryOptions['name'] = $serviceName;
                $dbEntries[$serviceName] = $dbEntryOptions;
            }
        }
        //------END ScheduledTasks-------------------------------------------------------------


        //------START commands to uninstall what is actually installed-------------------------------------------------------------
        $installedServices = $this->_getServices();
        $commandsUninstall = [];
        foreach ($installedServices as $installedService) {
            $commandsUninstall[] = __("net stop \"{0}\"", $installedService['name']);
            $commandsUninstall[] = __("\"{0}\" remove \"{1}\" confirm", $nssm, $installedService['name']);
        }
        //------END commands to uninstall what is actually installed-------------------------------------------------------------

        //create the DB entries
        $this->BackgroundServices->deleteAll(['id <> 0']);
        foreach ($dbEntries as $dbEntry) {
            $result = $this->BackgroundServices->createBackgroundServiceEntry($dbEntry['type'], $dbEntry);
        }

        //fixup to change NSSM requirement of """ to standard CLI of "
        array_walk($commandsTest, function (&$text) {
            $text = str_replace(' """', ' "', $text);
            $text = str_replace('""" ', '" ', $text);
        });

        //fixup to remove duplicates
        $commandsUninstallCompiled = array_merge($commandsRemove, $commandsUninstall);
        $commandsUninstallCompiled = array_unique($commandsUninstallCompiled);

        return [
            'servicesNames' => $expectedServicesNames,
            'install' => $commandsInstall,
            'uninstall' => $commandsUninstallCompiled,
            'start' => $commandsStart,
            'stop' => $commandsStop,
            'test' => $commandsTest,
        ];

    }


    /**
     * Convenience function to get a list of service names based on given filter
     *
     * @param string|null $filter
     * @param false $forceRefresh
     * @return array
     */
    public function _getServiceNames(string|null $filter, bool $forceRefresh = false): array
    {
        if (is_string($filter)) {
            $filter = strtolower($filter);
        }

        $services = $this->_getServices($forceRefresh);

        $serviceNamesCompiled = [];
        $servicesRunning = [];
        $servicesStopped = [];
        foreach ($services as $service) {
            $serviceNamesCompiled[] = $service['name'];

            if ($service['state'] === 'RUNNING' || $service['state'] === 'PAUSED') {
                $servicesRunning[] = $service['name'];
            } elseif ($service['state'] == 'STOPPED' && $service['start_type'] != 'DISABLED') {
                $servicesStopped[] = $service['name'];
            }
        }

        if ($filter === 'stopped') {
            return $servicesStopped;
        }

        if ($filter === 'running') {
            return $servicesRunning;
        }

        if ($filter === 'all-errand-backgroundServices') {
            $servicesToReturn = [];
            foreach ($serviceNamesCompiled as $serviceName) {
                if (str_contains(strtolower($serviceName), 'errand')) {
                    $servicesToReturn[] = $serviceName;
                }
            }
            return $servicesToReturn;
        }

        if ($filter === 'all-message-backgroundServices') {
            $servicesToReturn = [];
            foreach ($serviceNamesCompiled as $serviceName) {
                if (str_contains(strtolower($serviceName), 'message')) {
                    $servicesToReturn[] = $serviceName;
                }
            }
            return $servicesToReturn;
        }

        if ($filter === 'all-database-purger-backgroundServices') {
            $servicesToReturn = [];
            foreach ($serviceNamesCompiled as $serviceName) {
                if (str_contains(strtolower($serviceName), 'databasepurger')) {
                    $servicesToReturn[] = $serviceName;
                }
            }
            return $servicesToReturn;
        }

        if ($filter === 'all-hot-folder-backgroundServices') {
            $servicesToReturn = [];
            foreach ($serviceNamesCompiled as $serviceName) {
                if (str_contains(strtolower($serviceName), 'hotfolders')) {
                    $servicesToReturn[] = $serviceName;
                }
            }
            return $servicesToReturn;
        }

        if ($filter === 'all-scheduled-task-backgroundServices') {
            $servicesToReturn = [];
            foreach ($serviceNamesCompiled as $serviceName) {
                if (str_contains(strtolower($serviceName), 'scheduledtasks')) {
                    $servicesToReturn[] = $serviceName;
                }
            }
            return $servicesToReturn;
        }

        return $serviceNamesCompiled;
    }

    /**
     * Get a list of Windows Services for this Application.
     * Uses a simple caching mechanism.
     *
     * @param bool $forceRefresh
     * @return array
     */
    public function _getServices(bool $forceRefresh = false): array
    {
        //return from cache...
        if (!$forceRefresh) {
            if ($this->servicesCache) {
                return $this->servicesCache;
            }
        }

        $appName = $this->getAppNameCamelized();

        $cmd = __("sc.exe query state= all | find \"SERVICE_NAME: {0}_\"", $appName);
        exec($cmd, $foundServices, $ret);

        $services = [];
        foreach ($foundServices as $service) {
            $service = str_replace("SERVICE_NAME: ", "", $service);

            $cmd2 = __("sc.exe query {0} | find \"STATE\"", $service);
            exec($cmd2, $outServiceState, $ret2);
            $outServiceState = explode(" ", $outServiceState[0]);
            $outServiceState = array_pop($outServiceState);

            $cmd3 = __("sc.exe qc {0} | find \"START_TYPE\"", $service);
            exec($cmd3, $outServiceStartType, $ret3);
            $outServiceStartType = explode(" ", $outServiceStartType[0]);
            $outServiceStartType = array_pop($outServiceStartType);

            $services[] =
                [
                    'name' => $service,
                    'state' => $outServiceState,
                    'start_type' => $outServiceStartType,
                ];

            unset($outServiceState);
            unset($ret2);
            unset($outServiceStartType);
            unset($ret3);
        }

        $this->servicesCache = $services;

        return $services;
    }

    /**
     * Get services grouped by Type
     *
     * @param bool $forceRefresh
     * @return array
     */
    public function _getServicesGrouped(bool $forceRefresh = false): array
    {
        $grouped = [
            'Errand' => [],
            'Message' => [],
            'DatabasePurger' => [],
            'HotFolder' => [],
            'ScheduledTask' => [],
        ];
        $searchKeys = array_keys($grouped);

        $services = $this->_getServices($forceRefresh);
        foreach ($services as $service) {
            foreach ($searchKeys as $searchKey) {
                if (str_contains($service['name'], $searchKey)) {
                    $grouped[$searchKey][] = $service;
                }
            }
        }

        return $grouped;
    }


    /**
     * Get statistics for services (e.g. how many running or stopped)
     * Uses the quick_burn cache to speed up as does not frequently change.
     *
     * @param bool $forceRefresh
     * @return array
     */
    public function _getServicesStats(bool $forceRefresh = false): array
    {
        $stats = Cache::read('Services.stats', 'quick_burn');
        if ($stats) {
            return $stats;
        }

        $grouped = $this->_getServicesGrouped($forceRefresh);
        $stats = [];

        foreach ($grouped as $groupName => $group) {
            $total = 0;
            $running = 0;
            $stopped = 0;
            $paused = 0;
            $disabled = 0;
            foreach ($group as $service) {
                $total++;
                if ($service['state'] === 'RUNNING') {
                    $running++;
                } elseif ($service['state'] == 'STOPPED') {
                    $stopped++;
                } elseif ($service['state'] == 'PAUSED') {
                    $paused++;
                } elseif ($service['state'] == 'DISABLED') {
                    $disabled++;
                }
            }
            $stats[$groupName]['total'] = $total;
            $stats[$groupName]['running'] = $running;
            $stats[$groupName]['stopped'] = $stopped;
            $stats[$groupName]['paused'] = $paused;
            $stats[$groupName]['disabled'] = $disabled;
        }

        Cache::write('Services.stats', $stats, 'quick_burn');

        return $stats;
    }

    /**
     * Count the number of running Background Services
     *
     * @return int
     */
    public function countRunningServices(): int
    {
        $counter = 0;
        $services = $this->_getServices();
        foreach ($services as $service) {
            if ($service['state'] !== 'STOPPED') {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Count the number of running Background Services
     *
     * @return int
     */
    public function countAllServices(): int
    {
        $services = $this->_getServices();
        return count($services);
    }

    /**
     * Checks if the given $name is a valid service name.
     * Case Sensitive.
     *
     * @param string $name
     * @return bool
     */
    public function _isValidServiceName(string $name): bool
    {
        $services = $this->_getServices();
        foreach ($services as $service) {
            if ($service['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the given $action is a valid to perform.
     * Case Sensitive.
     *
     * @param string $action
     * @return bool
     */
    public function _isValidServiceAction(string $action): bool
    {
        $actions = ['kill', 'recycle', 'shutdown', 'start'];

        return in_array($action, $actions);
    }

    /**
     * @param array|string $servicesToStop
     * @param bool $verbose
     * @return mixed
     */
    public function kill(array|string $servicesToStop, bool $verbose = true): mixed
    {
        if (is_string($servicesToStop)) {
            $servicesToStop = [$servicesToStop];
        }

        $serviceNamesCompiled = $this->_getServiceNames(null, true);
        $servicesRunning = $this->_getServiceNames('running');
        $servicesStopped = $this->_getServiceNames('stopped');
        $servicesErrand = $this->_getServiceNames('all-errand-backgroundServices');
        $servicesMessage = $this->_getServiceNames('all-message-backgroundServices');

        $this->servicesThatWereRunning = $servicesRunning;

        $servicesToActOn = [];
        foreach ($servicesToStop as $serviceToStop) {
            if (strtolower($serviceToStop) == 'all') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesRunning);
            } elseif (strtolower($serviceToStop) === 'all-errand-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesErrand);
            } elseif (strtolower($serviceToStop) === 'all-message-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesMessage);
            } elseif (in_array($serviceToStop, $serviceNamesCompiled)) {
                $servicesToActOn[] = $serviceToStop;
            } else {
                if ($verbose) {
                    $this->addWarningAlerts(__('Sorry, could not find service {0}', $serviceToStop));
                }
            }
        }

        $counter = 0;
        foreach ($servicesToActOn as $service) {
            /**
             * @var array $out
             */
            $cmd = __("net stop \"{0}\" 2>&1", $service);
            exec($cmd, $out, $ret);

            $out = implode(" ", $out);
            if ($verbose) {
                $this->addSmartAlerts(__('{0}', $out));
            }

            if (str_contains(strtolower($out), 'success')) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * @param array|string $servicesToStart
     * @param bool $verbose
     * @return mixed
     */
    public function start(array|string $servicesToStart, bool $verbose = true): mixed
    {
        if (is_string($servicesToStart)) {
            $servicesToStart = [$servicesToStart];
        }

        $serviceNamesCompiled = $this->_getServiceNames(null, true);
        $servicesRunning = $this->_getServiceNames('running');
        $servicesStopped = $this->_getServiceNames('stopped');
        $servicesErrand = $this->_getServiceNames('all-errand-backgroundServices');
        $servicesMessage = $this->_getServiceNames('all-message-backgroundServices');

        $servicesToActOn = [];
        foreach ($servicesToStart as $serviceToStart) {
            if (strtolower($serviceToStart) == 'all') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesStopped);
            } elseif (strtolower($serviceToStart) == 'were-running') {
                $servicesToActOn = array_merge($servicesToActOn, $this->servicesThatWereRunning);
            } elseif (strtolower($serviceToStart) === 'all-errand-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesErrand);
            } elseif (strtolower($serviceToStart) === 'all-message-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesMessage);
            } elseif (in_array($serviceToStart, $serviceNamesCompiled)) {
                $servicesToActOn[] = $serviceToStart;
            } else {
                if ($verbose) {
                    $this->addWarningAlerts(__('Sorry, could not find service {0}', $serviceToStart));
                }
            }
        }

        $counter = 0;
        foreach ($servicesToActOn as $service) {
            /**
             * @var array $out
             */
            $cmd = __("net start \"{0}\" 2>&1", $service);
            exec($cmd, $out, $ret);

            $out = implode(" ", $out);
            if ($verbose) {
                $this->addSmartAlerts(__('{0}', $out));
            }

            if (str_contains(strtolower($out), 'success')) {
                $counter++;
            }
        }

        $this->servicesThatWereRunning = [];

        return $counter;
    }

    /**
     * @param array|string $servicesToShutdown
     * @param bool $verbose
     * @return mixed
     */
    public function shutdown(array|string $servicesToShutdown, bool $verbose = true): mixed
    {
        if (is_string($servicesToShutdown)) {
            $servicesToShutdown = [$servicesToShutdown];
        }

        $serviceNamesCompiled = $this->_getServiceNames(null, true);
        $servicesRunning = $this->_getServiceNames('running');
        $servicesStopped = $this->_getServiceNames('stopped');
        $servicesErrand = $this->_getServiceNames('all-errand-backgroundServices');
        $servicesMessage = $this->_getServiceNames('all-message-backgroundServices');

        $this->servicesThatWereRunning = $servicesRunning;

        $servicesToActOn = [];
        foreach ($servicesToShutdown as $serviceToStop) {
            if (strtolower($serviceToStop) == 'all') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesRunning);
            } elseif (strtolower($serviceToStop) === 'all-errand-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesErrand);
            } elseif (strtolower($serviceToStop) === 'all-message-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesMessage);
            } elseif (in_array($serviceToStop, $serviceNamesCompiled)) {
                $servicesToActOn[] = $serviceToStop;
            } else {
                if ($verbose) {
                    $this->addWarningAlerts(__('Sorry, could not find service {0}', $serviceToStop));
                }
            }
        }

        $counter = 0;
        foreach ($servicesToActOn as $service) {
            $result = $this->BackgroundServices->flagShutdown($service);
            if ($result) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * @param array|string $servicesToShutdown
     * @param bool $verbose
     * @return mixed
     */
    public function recycle(array|string $servicesToShutdown, bool $verbose = true): mixed
    {
        if (is_string($servicesToShutdown)) {
            $servicesToShutdown = [$servicesToShutdown];
        }

        $serviceNamesCompiled = $this->_getServiceNames(null, true);
        $servicesRunning = $this->_getServiceNames('running');
        $servicesStopped = $this->_getServiceNames('stopped');
        $servicesErrand = $this->_getServiceNames('all-errand-backgroundServices');
        $servicesMessage = $this->_getServiceNames('all-message-backgroundServices');

        $this->servicesThatWereRunning = $servicesRunning;

        $servicesToActOn = [];
        foreach ($servicesToShutdown as $serviceToStop) {
            if (strtolower($serviceToStop) == 'all') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesRunning);
            } elseif (strtolower($serviceToStop) === 'all-errand-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesErrand);
            } elseif (strtolower($serviceToStop) === 'all-message-backgroundServices') {
                $servicesToActOn = array_merge($servicesToActOn, $servicesMessage);
            } elseif (in_array($serviceToStop, $serviceNamesCompiled)) {
                $servicesToActOn[] = $serviceToStop;
            } else {
                if ($verbose) {
                    $this->addWarningAlerts(__('Sorry, could not find service {0}', $serviceToStop));
                }
            }
        }

        $counter = 0;
        foreach ($servicesToActOn as $service) {
            $result = $this->BackgroundServices->flagRecycle($service);
            if ($result) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Check if the current user is a Windows Admin and therefore able to install Services.
     *
     * @return bool
     */
    private function isWindowsAdmin()
    {
        $cmd = "NET SESSION 2>&1";
        $out = null;
        $ret = null;
        exec($cmd, $out, $ret);

        $out = implode(" ", $out);

        if ($ret == 0 && !str_contains($out, "error")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Test if the given Windows username and password are valid.
     * Done by creating a Scheduled Task with the given user credentials.
     *
     * @param $winUsername
     * @param $winPassword
     * @return bool
     */
    private function isWindowsCredentialsValid($winUsername, $winPassword): bool
    {
        if (strlen($winUsername) === 0 || strlen($winPassword) === 0) {
            return false;
        }

        $rn = mt_rand(111, 999);
        $outPath = TMP . "out{$rn}.txt";

        try {
            $cmd = 'schtasks /create /tr "ping.exe" /sc minute /mo 20 /tn "\\MyTasks\\TestAdminTask" /ru "' . $winUsername . '" /rp "' . $winPassword . '" /f > ' . $outPath . ' 2>&1  ';
            exec($cmd, $output, $retval);
            if (intval($retval) !== 0) {
                return false;
            }
            unset($output, $retval);
            $cmd = 'schtasks /delete /tn "\\MyTasks\\TestAdminTask" /f ';
            $cmd = escapeshellcmd($cmd);
            exec($cmd, $output, $retval);
            unset($output, $retval);

            if (is_file($outPath)) {
                unlink($outPath);
            }
        } catch (Throwable $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param string $serviceName
     * @param string $serviceAction
     * @return array
     */
    public function handleServiceRequest(string $serviceName, string $serviceAction): array
    {
        if ($serviceAction === 'start') {
            $count = $this->start($serviceName);
            $return = ['response' => true, 'count' => $count];
        } elseif ($serviceAction === 'kill') {
            $count = $this->kill($serviceName);
            $return = ['response' => true, 'count' => $count];
        } elseif ($serviceAction === 'recycle') {
            $count = $this->recycle($serviceName);
            $return = ['response' => true, 'count' => $count];
        } elseif ($serviceAction === 'shutdown') {
            $count = $this->shutdown($serviceName);
            $return = ['response' => true, 'count' => $count];
        } else {
            $return = ['response' => false, 'count' => 0];
        }

        return $return;
    }

    /**
     * Find if anti-malware services are running.
     * If so, may not be able to do some Background Services operations this may send security warnings to Domain Administrators
     *
     * @return bool
     */
    public function areProtectionServicesRunning(): bool
    {
        //CrowdStrike Falcon
        $cmd = __("sc.exe query state= all | find \"SERVICE_NAME: CSFalconService\"");
        exec($cmd, $foundServices, $ret);
        if (!empty($foundServices)) {
            return true;
        }

        return false;
    }

}
