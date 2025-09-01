<?php
/**
 * Default configuration.
 * Most of these values will be overridden in separate config files.
 */

use Cake\Cache\Engine\FileEngine;
use Cake\Database\Connection;
use Cake\Error\Renderer\WebExceptionRenderer;
use Cake\Log\Engine\FileLog;
use Cake\Mailer\Transport\MailTransport;

return [
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'App' => [
        'namespace' => 'App',
        'encoding' => env('APP_ENCODING', 'UTF-8'),
        'defaultLocale' => env('APP_DEFAULT_LOCALE', 'en_US'),
        'defaultTimezone' => env('APP_DEFAULT_TIMEZONE', 'UTC'),
        'base' => false,
        'dir' => 'src',
        'webroot' => 'webroot',
        'wwwRoot' => WWW_ROOT,
        //'baseUrl' => env('SCRIPT_NAME'),
        'fullBaseUrl' => false,
        'imageBaseUrl' => 'img/',
        'cssBaseUrl' => 'css/',
        'jsBaseUrl' => 'js/',
        'paths' => [
            'plugins' => [ROOT . DS . 'plugins' . DS],
            'templates' => [ROOT . DS . 'templates' . DS],
            'locales' => [RESOURCES . 'locales' . DS],
        ],
    ],
    'Security' => [
        'salt' => env('SECURITY_SALT'),
    ],
    'Asset' => [
        //'timestamp' => true,
        // 'cacheTime' => '+1 year'
    ],
    'Cache' => [
        'default' => [
            'className' => FileEngine::class,
            'path' => CACHE,
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'table_list' => [
            'className' => 'File',
            'prefix' => 'table_list_',
            'path' => CACHE . 'connection',
            'duration' => '+1 hour',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'micro' => [
            'className' => 'Cake\Cache\Engine\FileEngine',
            'path' => CACHE,
            'duration' => '+5 sec',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'one_hour' => [
            'className' => 'File',
            'prefix' => 'one_hour_',
            'path' => CACHE,
            'duration' => '+1 hour',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'one_day' => [
            'className' => 'File',
            'prefix' => 'one_day_',
            'path' => CACHE,
            'duration' => '+1 day',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'quick_burn' => [
            'className' => 'Cake\Cache\Engine\FileEngine',
            'path' => CACHE,
            'duration' => '+1 min',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'query_results_app' => [
            //more for caching application properties such as Settings
            'className' => 'File',
            'prefix' => 'app_',
            'path' => CACHE . 'queries/app',
            'duration' => '+300 seconds',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'query_results_general' => [
            //for caching general queries
            'className' => 'File',
            'prefix' => 'general_',
            'path' => CACHE . 'queries/general',
            'duration' => '+1 minute',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        'users_session_tracker' => [
            'className' => 'File',
            'prefix' => 'usl_',
            'path' => CACHE . 'users/session_tracker',
            'duration' => '+1 day',
            'url' => env('CACHE_DEFAULT_URL', null),
        ],
        '_cake_core_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_core_',
            'path' => CACHE . 'persistent' . DS,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKECORE_URL', null),
        ],
        '_cake_model_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_model_',
            'path' => CACHE . 'models' . DS,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKEMODEL_URL', null),
        ],
        '_cake_routes_' => [
            'className' => FileEngine::class,
            'prefix' => 'myapp_cake_routes_',
            'path' => CACHE,
            'serialize' => true,
            'duration' => '+1 years',
            'url' => env('CACHE_CAKEROUTES_URL', null),
        ],
    ],
    'Error' => [
        'errorLevel' => E_ALL & ~E_USER_DEPRECATED,
        'exceptionRenderer' => WebExceptionRenderer::class,
        'skipLog' => [],
        'log' => true,
        'trace' => true,
        'ignoredDeprecationPaths' => [
            'vendor/cakephp/cakephp/src/ORM/Table.php',
            'vendor/composer/ClassLoader.php',
        ],
    ],
    'Debugger' => [
        'editor' => 'phpstorm',
    ],
    //will be overridden by app_email.php then DB values
    'EmailTransport' => [
        'default' => [],
    ],
    //will be overridden by app_email.php then DB values
    'Email' => [
        'default' => [],
    ],
    'Datasources' => [
        'default' => [
            'className' => 'Cake\\Database\\Connection',
            'driver' => 'Dummy'
        ],
        'test' => [
            'className' => 'Cake\\Database\\Connection',
            'driver' => 'DummyTest'
        ],
        'internal' =>
            [
                'className' => 'Cake\\Database\\Connection',
                'driver' => 'Cake\\Database\\Driver\\Sqlite',
                'persistent' => false,
                'database' => CONFIG . 'internal.sqlite',
                'encoding' => 'utf8',
                'timezone' => 'UTC',
                'cacheMetadata' => true,
                'quoteIdentifiers' => false,
                'log' => false,
            ],
    ],
    'Log' => [
        'debug' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'debug',
            'url' => env('LOG_DEBUG_URL', null),
            'scopes' => null,
            'levels' => ['notice', 'info', 'debug'],
        ],
        'error' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'error',
            'url' => env('LOG_ERROR_URL', null),
            'scopes' => null,
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
        ],
        'queries' => [
            'className' => FileLog::class,
            'path' => LOGS,
            'file' => 'queries',
            'url' => env('LOG_QUERIES_URL', null),
            'scopes' => ['queriesLog'],
        ],
        'allToDatabase' => [
            'className' => 'App\Log\Engine\Auditor',
            'levels' => [],
        ],
    ],
    'Session' => [
        'defaults' => 'php',
    ],
];
