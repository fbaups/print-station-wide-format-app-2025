<?php
/**
 * This page generates global JS variables for the environment
 *
 * @var App\View\AppView $this
 */

use App\Model\Table\UsersTable;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;

/** @var UsersTable $Users */
$Users = TableRegistry::getTableLocator()->get('Users');

$homeUrl = TextFormatter::makeDirectoryTrailingForwardSlash(Router::url("/", true));
$loginUrl = $homeUrl . "login";
$logoutUrl = $homeUrl . "logout";
$sessionTimeout = $Users->getUserRolesSessionTimeoutSeconds();
$autoLogoutTimestamp = time() + $sessionTimeout;
$dataObjectsUrl = $homeUrl . "data-objects/";
$controllerUrl = $homeUrl . $this->request->getParam('controller') . "/";
$csrf = $this->request->getAttribute('csrfToken');//cakephp4
$pageUrl = Router::url(null, true);
$mode = strtolower(Configure::read('mode', 'prod'));
$modeBanner = Configure::read('mode-banner') ? 'true' : 'false';

$jsString = "\r\n
/**
 * Global Variables
 */
";
$jsString .= __('var csrfToken = "{0}";', $csrf) . "\r\n";
$jsString .= __('var homeUrl = "{0}";', $homeUrl) . "\r\n";
$jsString .= __('var loginUrl = "{0}";', $loginUrl) . "\r\n";
$jsString .= __('var logoutUrl = "{0}";', $logoutUrl) . "\r\n";
$jsString .= __('var dataObjectsUrl = "{0}";', $dataObjectsUrl) . "\r\n";
$jsString .= __('var controllerUrl = "{0}";', $controllerUrl) . "\r\n";
$jsString .= __('var pageUrl = "{0}";', $pageUrl) . "\r\n";
$jsString .= __('var sessionTimeout = {0};', $sessionTimeout) . "\r\n";
$jsString .= __('var userInactivityCounter = 0;') . "\r\n";
$jsString .= __('var autoLogoutTimestamp = {0};', $autoLogoutTimestamp) . "\r\n";
$jsString .= __('var mode = "{0}";', $mode) . "\r\n";
$jsString .= __('var modeBanner = {0};', $modeBanner) . "\r\n";
$jsString .= "\r\n";

echo "\r\n\r\n<!--Start Environment-->\r\n";
echo $this->Html->scriptBlock($jsString, ["type" => "application/javascript"]);
echo "\r\n<!--End Environment-->\r\n\r\n";

echo $this->Html->script('environment');
