<?php
declare(strict_types=1);

namespace App\Controller\Component;

use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use SQLite3;
use Throwable;

/**
 * CheckDatabaseDrivers component
 */
class CheckDatabaseDriversComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected array $_defaultConfig = [];


    public function configExists(): bool
    {
        return is_file($this->getConfigFilepath());
    }

    public function checkMysql($data = [], $dump = false)
    {
        $connectionConfig = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => $data['host'],
            'port' => $data['port'],
            'username' => $data['username'],
            'password' => $data['password'],
            'database' => '', //don't connect to database, just test connection to engine
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ];

        if ($dump) {
            $databaseName = Inflector::camelize($data['database']);
            $makeDatabaseStatement = "CREATE DATABASE IF NOT EXISTS {$databaseName};";

            try {
                ConnectionManager::setConfig('make_connection', $connectionConfig);
                $connection = ConnectionManager::get('make_connection');
                $result = $connection->execute($makeDatabaseStatement);
            } catch (Throwable $connectionError) {
            }

            //add in the DB name
            $connectionConfig['database'] = $databaseName;
            $this->dump($connectionConfig);
        }

        return $this->testConnectionMysqlAndSqlserver($connectionConfig);
    }

    public function checkSqlserver($data = [], $dump = false)
    {
        $connectionConfig = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Sqlserver',
            'persistent' => false,
            'host' => $data['host'],
            'port' => $data['port'],
            'username' => $data['username'],
            'password' => $data['password'],
            'database' => '', //don't connect to database, just test connection to engine
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            //'log' => true,
        ];

        if ($dump) {
            $databaseName = Inflector::camelize($data['database']);
            $makeDatabaseStatement = "IF NOT EXISTS(SELECT * FROM sys.databases WHERE name = '{$databaseName}') BEGIN CREATE DATABASE [{$databaseName}] END;";

            try {
                ConnectionManager::setConfig('make_connection', $connectionConfig);
                $connection = ConnectionManager::get('make_connection');
                $result = $connection->execute($makeDatabaseStatement);
            } catch (Throwable $connectionError) {
            }

            //add in the DB name
            $connectionConfig['database'] = $databaseName;
            $this->dump($connectionConfig);
        }

        return $this->testConnectionMysqlAndSqlserver($connectionConfig);
    }

    public function checkSqlite($data = [], $dump = false)
    {
        $sqliteDbFilepath = $data['name'];
        $sqliteDbFilepath = preg_replace('@[^0-9a-z.]+@i', '|', $sqliteDbFilepath);
        $sqliteDbFilepath = array_reverse(explode("|", $sqliteDbFilepath))[0];
        $sqliteDbFilepath = CONFIG . $sqliteDbFilepath;
        $sqliteDbFilepath = TextFormatter::makeEndsWith($sqliteDbFilepath, '.sqlite');

        $connectionConfig = [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Sqlite',
            'persistent' => false,
            'database' => $sqliteDbFilepath,
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ];

        if (is_file($sqliteDbFilepath)) {
            $sqliteFileExistedBefore = true;
        } else {
            $sqliteFileExistedBefore = false;
        }

        if (extension_loaded('sqlite3')) {
            $version = SQLite3::version();
        } else {
            $version = false;
        }

        if (isset($version['versionString'])) {
            $return['connected'] = true;
            $return['error'] = '';
            $return['dbExists'] = $sqliteFileExistedBefore;
        } else {
            $return['connected'] = false;
            $return['error'] = "Class SQLite3 not found";
            $return['db_exists'] = $sqliteFileExistedBefore;
        }

        if ($dump) {
            try {
                ConnectionManager::setConfig('make_connection', $connectionConfig);
                $connection = ConnectionManager::get('make_connection');
                $connected = $connection->getDriver()->connect();
            } catch (Throwable $connectionError) {
            }

            $this->dump($connectionConfig);
        }

        return $return;
    }

    /**
     * @param array $connectionConfig
     * @return array
     */
    private function testConnectionMysqlAndSqlserver(array $connectionConfig)
    {
        if ($connectionConfig['driver'] === 'Cake\Database\Driver\Sqlserver') {
            $canCreateDatabaseStatement = "SELECT has_perms_by_name(null, null, 'CREATE ANY DATABASE') as 'createDatabase';";
        } else if ($connectionConfig['driver'] === 'Cake\Database\Driver\Mysql') {
            $canCreateDatabaseStatement = " SHOW GRANTS FOR CURRENT_USER;";
        } else {
            $canCreateDatabaseStatement = "SELECT 0 as 'createDatabase';";
        }

        try {
            $isTestConnection = ConnectionManager::get('test_connection');
        } catch (Throwable $exception) {
            ConnectionManager::setConfig('test_connection', $connectionConfig);
        }

        try {
            $connection = ConnectionManager::get('test_connection');
            $connection->getDriver()->connect();
            $connected = true;
            $return['connected'] = $connected;
            $return['error'] = null;
        } catch (Throwable $connectionError) {
            $connection = false;
            $connected = false;
            $error = $connectionError->getMessage();
            if (method_exists($connectionError, 'getAttributes')) {
                $attributes = $connectionError->getAttributes();
                if (isset($attributes['message'])) {
                    $error .= "\r\n" . $attributes['message'];
                }
            }
            $return['connected'] = $connected;
            $return['error'] = $error;
        }

        if ($connection && $connected) {
            $results = $connection->execute($canCreateDatabaseStatement)->fetchAll('assoc');

            $permissionCreateDatabase = false;

            //SQL Server
            if (isset($results[0]['createDatabase'])) {
                if (intval($results[0]['createDatabase']) === 1) {
                    $permissionCreateDatabase = true;
                }
            }

            //MySQL
            if (isset($results[0])) {
                $keyName = array_keys($results[0])[0];
                $resultsText = json_encode($results);
                if (TextFormatter::startsWith($keyName, "Grants for")) {
                    if (strpos($resultsText, "CREATE") !== false && strpos($resultsText, "SHOW DATABASES") !== false) {
                        $permissionCreateDatabase = true;
                    }
                }
            }

            $return['permissionCreateDatabase'] = $permissionCreateDatabase;
        }

        return $return;
    }

    private function dump($connectionConfig, $connectionName = 'default')
    {
        $configFilename = $this->getConfigFilename();
        $configFilepath = $this->getConfigFilepath();

        //load existing
        $dataSources = [];
        try {
            Configure::load($configFilename);
            $dataSources = array_merge($dataSources, Configure::read('Datasources'));
        } catch (Throwable $exception) {

        }

        //merge in new config
        $dataSources[$connectionName] = $connectionConfig;

        Configure::write("Datasources", $dataSources);
        Configure::dump($configFilename, 'default', ['Datasources']);
        $contents = file_get_contents($configFilepath);
        $contents = str_replace("array (", "[", $contents);
        $contents = str_replace("),", "],", $contents);
        $contents = str_replace(");", "];", $contents);
        $contents = str_replace("'internal.sqlite'", "CONFIG . 'internal.sqlite'", $contents);
        $contents = str_replace("'developer.sqlite'", "CONFIG . 'internal.sqlite'", $contents);

        $configPathReplacement = str_replace(DS, DS . DS, CONFIG);
        $contents = str_replace("'" . $configPathReplacement, "CONFIG . '", $contents);

        file_put_contents($configFilepath, $contents);
    }

    public function getConfigFilename(): string
    {
        return 'app_datasources';
    }

    public function getConfigFilepath(): string
    {
        return CONFIG . $this->getConfigFilename() . ".php";
    }

}


/*
 * Configurations should look like the following:
 *
 'Datasources' => [
        'sqlserver' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Sqlserver',
            'persistent' => false,
            'host' => 'localhost',
            'port' => null,
            'username' => '',
            'password' => '',
            'database' => 'SeascapeDashboard',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ],
        'mysql' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            'port' => '3306',
            'username' => '',
            'password' => '',
            'database' => 'SeascapeDashboard',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ],
        'sqlite' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Sqlite',
            'persistent' => false,
            'database' => CONFIG . 'Stub_DB.sqlite',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
            'log' => false,
        ],
    ],
 */
