<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Cache\Cache;
use Authentication\Controller\Component\AuthenticationComponent;

/**
 * Authentication Bridge Component
 *
 * Provides authentication-related methods and bridges between old AuthUser patterns
 * and the new CakePHP 5.x Authentication plugin
 */
class AuthenticationBridgeComponent extends Component
{
    /**
     * Get current user ID (replaces AuthUser->id())
     */
    public function getCurrentUserId(): ?int
    {
        $controller = $this->getController();
        if (!$controller->components()->has('Authentication')) {
            return null;
        }

        /** @var AuthenticationComponent $auth */
        $auth = $controller->components()->get('Authentication');
        $identity = $auth->getIdentity();
        return $identity ? $identity->id : null;
    }

    /**
     * Get current user roles (replaces AuthUser->roles())
     */
    public function getCurrentUserRoles(): array
    {
        $controller = $this->getController();
        if (!$controller->components()->has('Authentication')) {
            return [];
        }

        /** @var AuthenticationComponent $auth */
        $auth = $controller->components()->get('Authentication');
        $identity = $auth->getIdentity();

        if (!$identity || !isset($identity->roles)) {
            return [];
        }

        $roles = [];
        foreach ($identity->roles as $role) {
            if (is_array($role)) {
                // If role is already an array, get the name
                $roleName = $role['name'] ?? $role['alias'] ?? '';
            } else {
                // If role is an object, get the name property
                $roleName = $role->name ?? $role->alias ?? '';
            }

            if ($roleName) {
                $roles[] = strtolower($roleName); // getPeerRoles expects lowercase strings
            }
        }
        return $roles;
    }

    /**
     * Check if current user has specific roles (replaces AuthUser->hasRoles())
     */
    public function currentUserHasRoles(array $roleNames): bool
    {
        $userRoles = $this->getCurrentUserRoles(); // This now returns an array of lowercase role names
        $roleNamesLower = array_map('strtolower', $roleNames);

        return !empty(array_intersect($roleNamesLower, $userRoles));
    }

    /**
     * Get current user data (replaces AuthUser->user())
     */
    public function getCurrentUser(): ?array
    {
        $controller = $this->getController();
        if (!$controller->components()->has('Authentication')) {
            return null;
        }

        /** @var AuthenticationComponent $auth */
        $auth = $controller->components()->get('Authentication');
        $identity = $auth->getIdentity();

        if (!$identity) {
            return null;
        }

        // Convert identity to array format expected by legacy code
        if (is_array($identity)) {
            return $identity;
        } else {
            // Handle entity-like identity
            $data = [];
            foreach (get_object_vars($identity) as $key => $value) {
                $data[$key] = $value;
            }
            return $data;
        }
    }

    /**
     * Check if current user has access to specific action (replaces AuthUser->hasAccess())
     * This is a simplified version - for full ACL checking, use TinyAuth middleware
     */
    public function currentUserHasAccess(array $url): bool
    {
        $controller = $this->getController();
        if (!$controller->components()->has('Authentication')) {
            return false;
        }

        /** @var AuthenticationComponent $auth */
        $auth = $controller->components()->get('Authentication');
        $identity = $auth->getIdentity();

        if (!$identity) {
            return false;
        }

        // Basic access check - if user is logged in and has roles, they have basic access
        // For detailed ACL, the TinyAuthAuthorizationMiddleware handles this
        $userRoles = $this->getCurrentUserRoles();
        return !empty($userRoles);
    }

    /**
     * Create an AuthUser bridge object for templates
     * This maintains backward compatibility with existing templates
     */
    public function createAuthUserBridge(array $usersSessionData): object
    {
        $authUserHelper = new \stdClass();
        $authUserHelper->data = $usersSessionData;

        $authUserHelper->user = function($field = null) use ($usersSessionData) {
            return $field ? ($usersSessionData[$field] ?? null) : $usersSessionData;
        };

        $authUserHelper->hasRoles = function($roles) use ($usersSessionData) {
            if (!isset($usersSessionData['roles'])) return false;
            $userRoles = array_column($usersSessionData['roles'], 'name');
            return !empty(array_intersect((array)$roles, $userRoles));
        };

        $authUserHelper->getFulName = function() use ($usersSessionData) {
            return trim(($usersSessionData['first_name'] ?? '') . ' ' . ($usersSessionData['last_name'] ?? ''));
        };

        $authUserHelper->hasAccess = function($url) use ($usersSessionData) {
            // Basic access check - if user has roles, they have access
            // Full ACL is handled by TinyAuthAuthorizationMiddleware
            return !empty($usersSessionData['roles']);
        };

        $authUserHelper->link = function($title, $url, $options = []) {
            // Simple link helper - for full TinyAuth link functionality,
            // you may need to implement more complex logic
            $html = new \Cake\View\Helper\HtmlHelper(new \Cake\View\View());
            return $html->link($title, $url, $options);
        };

        $authUserHelper->postLink = function($title, $url, $options = []) {
            // Simple post link helper
            $form = new \Cake\View\Helper\FormHelper(new \Cake\View\View());
            return $form->postLink($title, $url, $options);
        };

        return $authUserHelper;
    }

    /**
     * Configure session tracker for the current user
     */
    public function configureSessionTracker(): void
    {
        $controller = $this->getController();
        if (!$controller->components()->has('Authentication')) {
            return;
        }

        /** @var AuthenticationComponent $auth */
        $auth = $controller->components()->get('Authentication');
        $identity = $auth->getIdentity();

        if (!$identity) {
            //we don't know who the user is so we can't configure the tracker
            return;
        }

        $phpSessionId = $controller->getRequest()->getSession()->id();
        if (!$phpSessionId) {
            return;
        }

        $trackerInfo = Cache::read("PhpSession.{$phpSessionId}", 'users_session_tracker');
        if (!$trackerInfo) {
            $trackerInfo = [
                'user_id' => $identity->id,
                'is_applied' => false,
            ];
            Cache::write("PhpSession.{$phpSessionId}", $trackerInfo, 'users_session_tracker');
        }
    }
}
