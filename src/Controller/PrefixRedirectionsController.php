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
        if ($this->AuthUser->hasRoles(['superadmin', 'admin'])) {
            $this->redirect(['prefix' => 'Administrators', 'controller' => 'Dashboards', 'action' => 'index']);
        } elseif ($this->AuthUser->hasRoles(['manager', 'supervisor', 'operator'])) {
            $this->redirect(['prefix' => 'Producers', 'controller' => 'Dashboards', 'action' => 'index']);
        } elseif ($this->AuthUser->hasRoles(['superuser', 'user'])) {
            $this->redirect(['prefix' => 'Consumers', 'controller' => 'Dashboards', 'action' => 'index']);
        }

        //no Role so force a logout
        if ($this->AuthUser->user()) {
            $this->redirect("/logout");
        }
    }

    function index()
    {

    }
}
