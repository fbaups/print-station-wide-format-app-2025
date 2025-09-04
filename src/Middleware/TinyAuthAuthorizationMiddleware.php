<?php
declare (strict_types = 1);

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TinyAuth\Auth\AclTrait;
use TinyAuth\Auth\AllowTrait;

/**
 * TinyAuth Authorization Middleware
 *
 * This middleware handles authorization using TinyAuth ACL configuration
 * while working with the new CakePHP Authentication plugin.
 */
class TinyAuthAuthorizationMiddleware implements MiddlewareInterface
{
    use AclTrait;
    use AllowTrait;

    /**
     * Process the middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler Handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the authenticated identity from the Authentication plugin
        $identity = $request->getAttribute('identity');

        $params     = $request->getAttribute('params', []);
        $controller = $params['controller'] ?? '';
        $action     = $params['action'] ?? '';
        $prefix     = $params['prefix'] ?? null;

        // Build the controller name with prefix if present
        $controllerName = $prefix ? $prefix . '/' . $controller : $controller;

        // Check if the action is in the allow list (public access)
        if ($this->isActionAllowed($controllerName, $action)) {
            return $handler->handle($request);
        }

        // If no user is authenticated, redirect to login
        if (! $identity) {
            return $this->redirectToLogin($request);
        }

        // Get user roles from identity
        $userRoles = $this->getUserRoles($identity);

        // Check ACL authorization
        if (! $this->isActionAuthorized($controllerName, $action, $userRoles)) {
            // User is authenticated but not authorized - redirect to login or show error
            return $this->redirectToLogin($request);
        }

        return $handler->handle($request);
    }

    /**
     * Check if an action is in the allow list (public access)
     */
    protected function isActionAllowed(string $controller, string $action): bool
    {
        $allowConfig = Configure::read('TinyAuth.allowFilePath') ?: CONFIG . 'auth_allow.ini';

        if (! file_exists($allowConfig)) {
            return false;
        }

        $allowed = $this->getAllowed($allowConfig);

        return isset($allowed[$controller]) &&
            (in_array('*', $allowed[$controller]) || in_array($action, $allowed[$controller]));
    }

    /**
     * Check if an action is authorized for the user's roles
     */
    protected function isActionAuthorized(string $controller, string $action, array $userRoles): bool
    {
        $aclConfig = Configure::read('TinyAuth.aclFilePath') ?: CONFIG . 'auth_acl.ini';

        if (! file_exists($aclConfig)) {
            return false;
        }

        $acl = $this->getAcl($aclConfig);

        if (! isset($acl[$controller])) {
            return false;
        }

        $allowedRoles = [];

        // Check for specific action permissions
        if (isset($acl[$controller][$action])) {
            $allowedRoles = array_merge($allowedRoles, $acl[$controller][$action]);
        }

        // Check for wildcard permissions
        if (isset($acl[$controller]['*'])) {
            $allowedRoles = array_merge($allowedRoles, $acl[$controller]['*']);
        }

        // Check if user has any of the required roles
        return ! empty(array_intersect($userRoles, $allowedRoles));
    }

    /**
     * Get user roles from identity
     */
    protected function getUserRoles($identity): array
    {
        if (! $identity) {
            return [];
        }

        $roles = [];

        // Handle array identity
        if (is_array($identity)) {
            if (isset($identity['roles'])) {
                foreach ($identity['roles'] as $role) {
                    $roles[] = is_array($role) ? $role['name'] : $role;
                }
            }
        } else {
            // Handle entity identity
            if (method_exists($identity, 'get') && $identity->get('roles')) {
                foreach ($identity->get('roles') as $role) {
                    $roles[] = is_array($role) ? $role['name'] : (isset($role->name) ? $role->name : $role);
                }
            }
        }

        return array_map('strtolower', $roles);
    }

    /**
     * Redirect to login page
     */
    protected function redirectToLogin(ServerRequestInterface $request): ResponseInterface
    {
        $loginUrl = Router::url([
            'prefix'     => false,
            'plugin'     => false,
            'controller' => 'UserHub',
            'action'     => 'login',
        ]);

        $currentUrl = $request->getUri()->getPath();
        if (! in_array($currentUrl, ['/login', '/user-hub/login'])) {
            $loginUrl .= '?redirect=' . urlencode($currentUrl);
        }

        $response = new Response();
        return $response->withStatus(302)->withHeader('Location', $loginUrl);
    }

    /**
     * Handle unauthorized access
     */
    protected function handleUnauthorized(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response();
        return $response->withStatus(403)->withStringBody('Access denied');
    }

    /**
     * Get allowed actions from config file
     */
    protected function getAllowed(string $configFile): array
    {
        if (! file_exists($configFile)) {
            return [];
        }

        $allowed = [];
        $config  = parse_ini_file($configFile, false);

        foreach ($config as $controller => $actions) {
            $allowed[$controller] = explode(',', str_replace(' ', '', $actions));
        }

        return $allowed;
    }

    /**
     * Get ACL configuration from config file
     */
    protected function getAcl(string $configFile): array
    {
        if (! file_exists($configFile)) {
            return [];
        }

        $acl    = [];
        $config = parse_ini_file($configFile, true);

        foreach ($config as $controller => $actions) {
            foreach ($actions as $action => $roles) {
                $acl[$controller][$action] = explode(',', str_replace(' ', '', $roles));
            }
        }

        return $acl;
    }
}
