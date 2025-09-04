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
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use App\Middleware\ModifyRequestDataMiddleware;
use App\Middleware\TinyAuthAuthorizationMiddleware;
use App\Model\Table\UsersTable;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\AbstractIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\Middleware\HttpsEnforcerMiddleware;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\TableRegistry;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /* START_BLOCKED_CODE */
        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            Configure::write('DebugKit.safeTld', ['name', 'net', 'localhost', 'local']);
            $this->addPlugin('DebugKit');
        }
        /* END_BLOCKED_CODE */

        // Load more plugins here
        $this->addPlugin('Authentication');
        $this->addPlugin('TinyAuth');

        // Brand the Application
        $this->loadBranding();

        //dynamically determine Session Timeouts for the user
        $this->applySessionTracker();
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance. For that when
            // creating the middleware instance specify the cache config name by
            // using it's second constructor argument:
            // `new RoutingMiddleware($this, '_cake_routes_')`
            ->add(new RoutingMiddleware($this))

            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())
            ->add(new AuthenticationMiddleware($this))
            ->add(new TinyAuthAuthorizationMiddleware());

        // Cross Site Request Forgery (CSRF) Protection Middleware
        // https://book.cakephp.org/5/en/security/csrf.html#csrf-protection
        $csrf = new CsrfProtectionMiddleware([
            'httponly' => true,
        ]);
        $csrf->skipCheckCallback(function ($request) {
            //token check will be skipped when callback returns `true`.
            $controller = $request->getParam('controller');
            $action = $request->getParam('action');

            //skip certain Controllers
            if (in_array($controller, ['DataObjects'])) {
                return true;
            }

            //skip the ConnectorWebHotFolders:submit route
            if ($controller === 'ConnectorWebHotFolders' && $action === 'submit') {
                return true;
            }

            //skip the ConnectorOpen route
            if ($controller === 'ConnectorOpen') {
                return true;
            }

            //skip the ConnectorDataBlobs route
            if ($controller === 'ConnectorDataBlobs') {
                return true;
            }

            //skip the Instance:configure route
            if ($controller === 'Instance' && $action === 'configure') {
                return true;
            }

            return false;
        });
        $middlewareQueue->add($csrf);


        // HTTPS Enforcer Middleware - application to only be available via HTTPS
        // https://book.cakephp.org/4/en/security/https-enforcer.html
        $isHttps = true; //todo make this configurable
        if ($isHttps) {
            $https = new HttpsEnforcerMiddleware([
                'redirect' => true,
                'statusCode' => 302,
                'headers' => ['X-Https-Upgrade' => 1],
                'disableOnDebug' => false,
            ]);
            $middlewareQueue->add($https);
        }

        $middlewareQueue->add(new ModifyRequestDataMiddleware());

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Cake/Repl');
        $this->addOptionalPlugin('Bake');

        $this->addPlugin('Migrations');

        // Load more plugins here
    }

    /**
     * Load the branding JSON file and apply Application name and logo
     *
     * @return void
     */
    private function loadBranding(): void
    {
        $brandingConfig = CONFIG . 'branding.json';
        if (is_file($brandingConfig)) {
            $brandingConfig = json_decode(file_get_contents($brandingConfig), true);
        } else {
            $brandingConfig = [
                'app_name' => "Dashboard",
                'app_logo' => "",
                'app_desc' => "",
            ];
        }

        if (!defined('APP_NAME')) {
            $appName = $brandingConfig['app_name'] ?? "Dashboard";
            define('APP_NAME', $appName);

        }

        if (!defined('APP_LOGO')) {
            $appLogo = $brandingConfig['app_logo'] ?? "";
            define('APP_LOGO', $appLogo);
        }

        if (!defined('APP_DESC')) {
            $appDesc = $brandingConfig['app_desc'] ?? "";
            define('APP_DESC', $appDesc);
        }
    }

    /**
     * Returns a service provider instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @return \Authentication\AuthenticationServiceInterface
     */
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService();

        // Define where users should be redirected to when they are not authenticated
        $service->setConfig([
            'unauthenticatedRedirect' => Router::url([
                'prefix' => false,
                'plugin' => false,
                'controller' => 'UserHub',
                'action' => 'login',
            ]),
            'queryParam' => 'redirect',
        ]);

        $fields = [
            AbstractIdentifier::CREDENTIAL_USERNAME => 'username',
            AbstractIdentifier::CREDENTIAL_PASSWORD => 'password',
        ];
        // Load the authenticators. Session should be first.
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => $fields,
            'loginUrl' => Router::url([
                'prefix' => false,
                'plugin' => false,
                'controller' => 'UserHub',
                'action' => 'login',
            ]),
        ]);

        // Load identifiers - first try username, then email
        $service->loadIdentifier('UsernamePassword', [
            'className' => 'Authentication.Password',
            'fields'    => [
                AbstractIdentifier::CREDENTIAL_USERNAME => 'username',
                AbstractIdentifier::CREDENTIAL_PASSWORD => 'password',
            ],
            'resolver'  => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
                'finder'    => 'authWithEmailSupport',
            ],
        ]);

        // Second identifier for email login
        $service->loadIdentifier('EmailPassword', [
            'className' => 'Authentication.Password',
            'fields'    => [
                AbstractIdentifier::CREDENTIAL_USERNAME => 'email',
                AbstractIdentifier::CREDENTIAL_PASSWORD => 'password',
            ],
            'resolver'  => [
                'className' => 'Authentication.Orm',
                'userModel' => 'Users',
                'finder'    => 'authWithEmailSupport',
            ],
        ]);

        return $service;
    }

    /**
     * Dynamically sets the User Session Timeout based on the Users Role
     *
     * This is a multistep process
     * 1) On a page load (usually login), the User is identified and their
     *      Session ID is saved into the Cache. See AppController->configureSessionTracker()
     * 2) Application.php->applySessionTracker() is run before PHP Session is opened.
     *      We look for the Session ID in the request header and retrieve the User Session Timeout
     *      from the Cache in step (1) and apply it to Configure->Session.timeout
     * The User Session Timeout is now based in their Role.
     *
     * @return void
     */
    private function applySessionTracker(): void
    {
        $headers = getallheaders();

        $phpSessionId = false;

        if (isset($headers['Cookie'])) {
            // Match PHPSESSID from the Cookie string
            if (preg_match('/PHPSESSID=([^;]+)/', $headers['Cookie'], $matches)) {
                $phpSessionId = $matches[1];
            }
        }

        if (!$phpSessionId) {
            return;
        }

        $trackerInfo = Cache::read("PhpSession.{$phpSessionId}", 'users_session_tracker');
        if (!$trackerInfo) {
            return;
        }

        $id = $trackerInfo['user_id'];
        if (!$id) {
            return;
        }

        /** @var UsersTable $UsersTable */
        $UsersTable = TableRegistry::getTableLocator()->get('Users');
        $cacheConfigName = $UsersTable->createCacheForUser($id);

        //read data form cache and apply timeout if present
        $userDataFromCache = Cache::read('userSessionData', $cacheConfigName);

        if (!$userDataFromCache) {
            return;
        }

        //finally apply to Configure, which will be applied to the PHP Session
        Configure::write('Session.timeout', $userDataFromCache['session_timeout_minutes']);

        //update the Cache so we know that it has been applied
        $trackerInfo['is_applied'] = true;
        Cache::write("PhpSession.{$phpSessionId}", $trackerInfo, 'users_session_tracker');
    }
}
