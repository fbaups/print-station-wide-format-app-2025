<?php
declare(strict_types=1);

namespace App\Controller;

use Exception;

/**
 * This is a dummy Controller that simply redirects to the Dashboard of the corresponding Prefix.
 * Do not add or change anything.
 *
 * The routes.php file has the following to redirect the base path of "/" to this controller.
 * $builder->connect('/', ['controller' => 'PrefixRedirections', 'action' => 'index']);
 *
 */
class PrefixRedirectionsController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        //silently redirect to the correct Dashboard based on the User's Role
        $identity = $this->Authentication->getIdentity();
        if ($identity) {
            $userRoles = $this->getUserRoles($identity);

            if (array_intersect($userRoles, ['superadmin', 'admin'])) {
                $this->redirect(['prefix' => 'Administrators', 'controller' => 'Dashboards', 'action' => 'index']);
            } elseif (array_intersect($userRoles, ['manager', 'supervisor', 'operator'])) {
                $this->redirect(['prefix' => 'Producers', 'controller' => 'Dashboards', 'action' => 'index']);
            } elseif (array_intersect($userRoles, ['superuser', 'user'])) {
                $this->redirect(['prefix' => 'Consumers', 'controller' => 'Dashboards', 'action' => 'index']);
            } else {
                //no valid Role so force a logout
                $this->redirect("/logout");
            }
        } else {
            // No authenticated user - redirect to login
            $this->redirect(['controller' => 'UserHub', 'action' => 'login']);
        }
    }

    function index()
    {

    }

    /**
     * Get user roles from identity
     */
    private function getUserRoles($identity): array
    {
        if (!$identity) {
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
}
