<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.8
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/*
 * Configure paths required to find CakePHP + general filepath constants
 */
require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';

/*
 * Global functions
 */
require CONFIG . 'global_functions.php';
require CAKE . 'functions.php';

/*
 * Bootstrap CakePHP.
 *
 * Does the various bits of setup that CakePHP needs to do.
 * This includes:
 *
 * - Registering the CakePHP autoloader.
 * - Setting the default application paths.
 */
require CORE_PATH . 'config' . DS . 'bootstrap.php';

use App\Console\Installer;
use App\Model\Table\InternalOptionsTable;
use App\Model\Table\SettingsTable;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Database\Type\StringType;
use Cake\Database\TypeFactory;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorTrap;
use Cake\Error\ExceptionTrap;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Router;
use Cake\Utility\Security;

/*
 * Make sure the web.config file exists
 */
if (!is_file(ROOT . DS . "web.config")) {
    if (is_file(ROOT . DS . "web.xml")) {
        copy(ROOT . DS . "web.xml", ROOT . DS . "web.config");
    } else {
        die("Invalid web.config file, exiting!");
    }
}

/*
 * See https://github.com/josegonzalez/php-dotenv for API details.
 *
 * Uncomment block of code below if you want to use `.env` file during development.
 * You should copy `config/.env.example` to `config/.env` and set/modify the
 * variables as required.
 *
 * The purpose of the .env file is to emulate the presence of the environment
 * variables like they would be present in production.
 *
 * If you use .env files, be careful to not commit them to source control to avoid
 * security risks. See https://github.com/josegonzalez/php-dotenv#general-security-information
 * for more information for recommended practices.
*/
// if (!env('APP_NAME') && file_exists(CONFIG . '.env')) {
//     $dotenv = new \josegonzalez\Dotenv\Loader([CONFIG . '.env']);
//     $dotenv->parse()
//         ->putenv()
//         ->toEnv()
//         ->toServer();
// }

/*
 * Read configuration file and inject configuration into various
 * CakePHP classes.
 *
 * By default there is only one configuration file. It is often a good
 * idea to create multiple configuration files, and separate the configuration
 * that changes from configuration that does not. This makes deployment simpler.
 */
try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

/*
 * Load other configurations.
 */
if (file_exists(CONFIG . 'app_datasources.php')) {
    Configure::load('app_datasources', 'default');
}

/*
 * Load the local environment configuration last. Do not commit to GIT!
 */
if (file_exists(CONFIG . 'app_local.php')) {
    Configure::load('app_local', 'default');
} else {
    $contents = Installer::generateAppLocalFileContents();
    file_put_contents(CONFIG . 'app_local.php', $contents);
    Configure::load('app_local', 'default');
}

/*
 * When debug = true the metadata cache should only last
 * for a short time.
 */
if (Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+2 minutes');
    Configure::write('Cache._cake_core_.duration', '+2 minutes');
    // disable router cache during development
    Configure::write('Cache._cake_routes_.duration', '+2 seconds');
}

/*
 * Set the default server timezone. Using UTC makes time calculations / conversions easier.
 * Check http://php.net/manual/en/timezones.php for list of valid timezone strings.
 */
date_default_timezone_set(Configure::read('App.defaultTimezone'));

/*
 * Configure the mbstring extension to use the correct encoding.
 */
mb_internal_encoding(Configure::read('App.encoding'));

/*
 * Set the default locale. This controls how dates, number and currency is
 * formatted and sets the default language to use for translations.
 */
ini_set('intl.default_locale', Configure::read('App.defaultLocale'));

/*
 * Register application error and exception handlers.
 */
(new ErrorTrap(Configure::read('Error')))->register();
(new ExceptionTrap(Configure::read('Error')))->register();

/*
 * Include the CLI bootstrap overrides.
 */
if (PHP_SAPI === 'cli') {
    require CONFIG . 'bootstrap_cli.php';
}

/*
 * Set the full base URL.
 * This URL is used as the base of all absolute links.
 */
$fullBaseUrl = Configure::read('App.fullBaseUrl');
if (!$fullBaseUrl) {
    /*
     * When using proxies or load balancers, SSL/TLS connections might
     * get terminated before reaching the server. If you trust the proxy,
     * you can enable `$trustProxy` to rely on the `X-Forwarded-Proto`
     * header to determine whether to generate URLs using `https`.
     *
     * See also https://book.cakephp.org/4/en/controllers/request-response.html#trusting-proxy-headers
     */
    $trustProxy = false;

    $s = null;
    if (env('HTTPS') || ($trustProxy && env('HTTP_X_FORWARDED_PROTO') === 'https')) {
        $s = 's';
    }

    $httpHost = env('HTTP_HOST');
    if (isset($httpHost)) {
        $fullBaseUrl = 'http' . $s . '://' . $httpHost;
    }
    unset($httpHost, $s);
}
if ($fullBaseUrl) {
    Router::fullBaseUrl($fullBaseUrl);
}
unset($fullBaseUrl);

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));
TransportFactory::setConfig(Configure::consume('EmailTransport'));
Mailer::setConfig(Configure::consume('Email'));
Log::setConfig(Configure::consume('Log'));
Security::setSalt(Configure::consume('Security.salt'));

/*
 * Setup detectors for mobile and tablet.
 * If you don't use these checks you can safely remove this code
 * and the mobiledetect package from composer.json.
 */
ServerRequest::addDetector('mobile', function ($request) {
    $detector = new \Detection\MobileDetect();

    return $detector->isMobile();
});
ServerRequest::addDetector('tablet', function ($request) {
    $detector = new \Detection\MobileDetect();

    return $detector->isTablet();
});

/**
 * @var SettingsTable $SettingsTable
 * @var InternalOptionsTable $InternalOptionsTable
 */

$tableLocator = new TableLocator();

//load Settings from DB
try {
    $tablesDefault = getConnectionTableList('default');
    if (isset($tablesDefault['settings'])) {
        $SettingsTable = $tableLocator->get('Settings');
        $SettingsTable->saveSettingsToConfigure();
    } else {
        $SettingsTable = false;
    }
} catch (\Exception $e) {
    $SettingsTable = false;
}

//load InternalOptions from DB
$connectionInternal = getConnectionTableList('internal', false);
if (isset($connectionInternal['internal_options'])) {
    $InternalOptionsTable = $tableLocator->get('InternalOptions');
    $InternalOptionsTable->loadInternalOptions();
} else {
    $InternalOptionsTable = $tableLocator->get('InternalOptions');
    $InternalOptionsTable->buildInternalOptionsTable();
}

//build email config from DB
try {
    //config from DB
    if ($SettingsTable) {
        $emailConfigFromDb = $SettingsTable->getEmailDetails();
    } else {
        $emailConfigFromDb = false;
    }

    //config from file (defaults)
    $loader = new PhpConfig();
    $emailConfigFromFile = $loader->read('app_email');
    $emailConfigMerged = $emailConfigFromFile;

    if ($emailConfigFromDb) {
        //merge Transport
        if (!empty($emailConfigFromDb['email_username'])) {
            $email_username = $emailConfigFromDb['email_username'];
        } else {
            $email_username = null;
        }
        if (!empty($emailConfigFromDb['email_password'])) {
            $email_password = $emailConfigFromDb['email_password'];
        } else {
            $email_password = null;
        }

        $emailConfigMerged['EmailTransport']['default']['host'] = $emailConfigFromDb['email_smtp_host'];
        $emailConfigMerged['EmailTransport']['default']['port'] = $emailConfigFromDb['email_smtp_port'];
        $emailConfigMerged['EmailTransport']['default']['username'] = $email_username;
        $emailConfigMerged['EmailTransport']['default']['password'] = $email_password;
        $emailConfigMerged['EmailTransport']['default']['tls'] =
            filter_var($emailConfigFromDb['email_tls'], FILTER_VALIDATE_BOOLEAN);

        //merge Profile
        $emailConfigMerged['Email']['default']['transport'] = 'default';
        $emailConfigMerged['Email']['default']['from'] =
            [$emailConfigFromDb['email_from_address'] => $emailConfigFromDb['email_from_name']];
        $emailConfigMerged['Email']['default']['charset'] = 'utf-8';
        $emailConfigMerged['Email']['default']['headerCharset'] = 'utf-8';
    }

    //save into Email configuration
    TransportFactory::drop('default');
    Mailer::drop('default');
    TransportFactory::setConfig($emailConfigMerged['EmailTransport']);
    Mailer::setConfig($emailConfigMerged['Email']);
} catch (\Exception $e) {
    exit($e->getMessage() . "\n");
}

/*
 * You can enable default locale format parsing by adding calls
 * to `useLocaleParser()`. This enables the automatic conversion of
 * locale specific date formats. For details see
 * @link https://book.cakephp.org/4/en/core-libraries/internationalization-and-localization.html#parsing-localized-datetime-data
 */
// \Cake\Database\TypeFactory::build('time')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('date')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('datetime')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('timestamp')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('datetimefractional')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('timestampfractional')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('datetimetimezone')
//    ->useLocaleParser();
// \Cake\Database\TypeFactory::build('timestamptimezone')
//    ->useLocaleParser();

// There is no time-specific type in Cake
TypeFactory::map('time', StringType::class);

/*
 * Custom Inflector rules, can be set to correctly pluralize or singularize
 * table, model, controller names or whatever other string is passed to the
 * inflection functions.
 */
//Inflector::rules('plural', ['/^(inflect)or$/i' => '\1ables']);
//Inflector::rules('irregular', ['red' => 'redlings']);
//Inflector::rules('uninflected', ['dontinflectme']);

//----------------------------------------------------------------------------------------------------------------------
/**
 * This function is purely to use Cache when getting a list of tables
 *
 * @param string $connectionName
 * @param bool $readFromCache
 * @return mixed
 */
function getConnectionTableList(string $connectionName = '', bool $readFromCache = true): mixed
{
    if ($readFromCache) {
        $list = Cache::read($connectionName, 'table_list');
        if ($list) {
            return $list;
        }
    }

    $driver = ConnectionManager::get($connectionName)->config()['driver'];
    if ($driver === 'Dummy') {
        return [];
    }

    try {
        $list = ConnectionManager::get($connectionName)->getSchemaCollection()->listTables();
        $list = array_flip($list);
        Cache::write($connectionName, $list, 'table_list');
        return $list;
    } catch (Throwable $exception) {
        return [];
    }
}
