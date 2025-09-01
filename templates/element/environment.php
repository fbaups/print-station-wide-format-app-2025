<?php
/**
 * This page generates global JS variables for the environment
 *
 * @var App\View\AppView $this
 * @var array $usersSessionData
 */

use App\Model\Table\UsersTable;
use App\Utility\Instances\LoadBalancerProxyDetector;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/** @var UsersTable $Users */
//$Users = TableRegistry::getTableLocator()->get('Users');
//$usersSessionData = $Users->getUserSessionDataForCache($this->AuthUser->id());

$prefix = $this->request->getParam('prefix');
$controller = $this->request->getParam('controller');
$action = $this->request->getParam('action');
if ($prefix === 'Administrators' && $controller === 'Instance' && $action === 'configure') {
    $instanceUrl = Router::url(['prefix' === 'Administrators' && 'controller' === 'Instance' && 'action' === 'configure'], true);
} else {
    $instanceUrl = false;
}

$homeUrl = TextFormatter::makeDirectoryTrailingForwardSlash(Router::url("/", true));
$loginUrl = $homeUrl . "login";
$logoutUrl = $homeUrl . "logout";
$sessionTimeout = $usersSessionData['session_timeout'] ?? 0;
$inactivityTimeout = $usersSessionData['inactivity_timeout'] ?? 0;
$sessionTimeoutTimestamp = time() + $sessionTimeout;
$dataObjectsClosedUrl = $homeUrl . "connector-closed/";
$dataObjectsOpenUrl = $homeUrl . "connector-open/";

$prefix = $this->request->getParam('prefix');
$controller = $this->request->getParam('controller');
$action = $this->request->getParam('action');

$controllerUrl = ['prefix' => $prefix, 'controller' => $controller];
$controllerUrl = TextFormatter::makeDirectoryTrailingSmartSlash(Router::url($controllerUrl, true));

$csrf = $this->request->getAttribute('csrfToken');
$pageUrl = TextFormatter::makeDirectoryTrailingSmartSlash(Router::url(null, true));
$mode = strtolower(Configure::read('mode', 'prod'));
$modeBanner = Configure::read('mode-banner') ? 'true' : 'false';

//specifically exclude some pages from the SessionTimeout
$excludeSessionTimeoutUrls = [
    ['prefix' => false, 'controller' => 'ConnectorArtifacts', 'action' => 'mobile-upload'],
    ['prefix' => false, 'controller' => 'login', 'action' => ''],
];
foreach ($excludeSessionTimeoutUrls as $excludeSessionTimeoutUrl) {
    $tmpPrefix = empty($prefix) ? false : $prefix;
    $thisUrl = Router::url(['prefix' => $tmpPrefix, 'controller' => $controller, 'action' => $action]);
    $excludeUrl = Router::url($excludeSessionTimeoutUrl);
    if ($thisUrl === $excludeUrl) {
        $sessionTimeout = 0;
    }
}

//specifically exclude some pages from the InactivityTimeout
$excludeInactivityTimeoutUrls = [
    ['prefix' => false, 'controller' => 'ConnectorArtifacts', 'action' => 'mobile-upload'],
    ['prefix' => false, 'controller' => 'login', 'action' => ''],
];
foreach ($excludeInactivityTimeoutUrls as $excludeInactivityTimeoutUrl) {
    $tmpPrefix = empty($prefix) ? false : $prefix;
    $thisUrl = Router::url(['prefix' => $tmpPrefix, 'controller' => $controller, 'action' => $action]);
    $excludeUrl = Router::url($excludeInactivityTimeoutUrl);
    if ($thisUrl === $excludeUrl) {
        $inactivityTimeout = 0;
    }
}

$jsString = "\r\n
/**
 * Global Variables
 */
";
$jsString .= __('var csrfToken = "{0}";', $csrf) . "\r\n";
$jsString .= __('var homeUrl = "{0}";', $homeUrl) . "\r\n";
$jsString .= __('var loginUrl = "{0}";', $loginUrl) . "\r\n";
$jsString .= __('var logoutUrl = "{0}";', $logoutUrl) . "\r\n";
$jsString .= __('var dataObjectsClosedUrl = "{0}";', $dataObjectsClosedUrl) . "\r\n";
$jsString .= __('var dataObjectsOpenUrl = "{0}";', $dataObjectsOpenUrl) . "\r\n";
$jsString .= __('var controllerUrl = "{0}";', $controllerUrl) . "\r\n";
$jsString .= __('var pageUrl = "{0}";', $pageUrl) . "\r\n";
$jsString .= __('var sessionTimeout = {0};', $sessionTimeout) . "\r\n";
$jsString .= __('var sessionTimeoutTimestamp = {0};', $sessionTimeoutTimestamp) . "\r\n";
$jsString .= __('var inactivityTimeout = {0};', $inactivityTimeout) . "\r\n";
$jsString .= __('var userInactivityCounter = 0;') . "\r\n";
$jsString .= __('var mode = "{0}";', $mode) . "\r\n";
$jsString .= __('var modeBanner = {0};', $modeBanner) . "\r\n";
if ($instanceUrl) {
    $jsString .= __('var instanceUrl = "{0}";', $instanceUrl) . "\r\n";
}
$jsString .= "\r\n";

//dd($jsString);

echo "\r\n\r\n<!--Start Environment-->\r\n";
echo $this->Html->scriptBlock($jsString, ["type" => "application/javascript"]);
echo "\r\n<!--End Environment-->\r\n\r\n";

echo $this->Html->script('environment');
