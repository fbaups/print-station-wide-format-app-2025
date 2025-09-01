<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Utility\Releases\BuildTasks;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

$baseDir = __DIR__;
require $baseDir . '/../config/paths.php';
require $baseDir . '/../vendor/autoload.php';
require $baseDir . '/../config/global_functions.php';

$brandingConfig = CONFIG . 'branding.json';
if (is_file($brandingConfig)) {
    $brandingConfig = json_decode(file_get_contents($brandingConfig), true);

    if (!defined('APP_NAME')) {
        $appName = $brandingConfig['app_name'] ?? "Dashboard";
        define('APP_NAME', $appName);
    }
} else {
    print_r("No App Name Defined");
    die();
}

$BuildTasks = new BuildTasks();

$result = $BuildTasks->pushDownloadLinksFile();
