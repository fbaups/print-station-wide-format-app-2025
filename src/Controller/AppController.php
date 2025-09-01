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
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Controller\Component\FlashComponent;
use App\Log\Engine\Auditor;
use App\Model\Entity\User;
use App\Model\Table\InternalOptionsTable;
use App\Model\Table\SeedsTable;
use App\Model\Table\SettingsTable;
use App\Utility\Instances\InstanceTasks;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Exception;
use TinyAuth\Controller\Component\AuthComponent;
use TinyAuth\Controller\Component\AuthUserComponent;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 *
 * @property AuthenticationComponent $Authentication
 * @property AuthComponent $Auth
 * @property AuthUserComponent $AuthUser
 * @property FlashComponent $Flash
 * @property Auditor $Auditor
 */
class AppController extends Controller
{
    protected Table|InternalOptionsTable $InternalOptions;
    protected Table|SettingsTable $Settings;
    protected Table|SeedsTable $Seeds;

    protected Auditor $Auditor;

    //protected AuthenticationComponent $Authentication;
    //protected AuthUserComponent $AuthUser;

    protected ConnectionInterface $Connection;

    public BackgroundServicesAssistant $BackgroundServicesAssistant;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Connection = ConnectionManager::get('default');

        $this->InternalOptions = $this->getTableLocator()->get('InternalOptions');
        $this->Settings = $this->getTableLocator()->get('Settings');
        $this->Seeds = $this->getTableLocator()->get('Seeds');

        $this->Auditor = new Auditor();

        $this->BackgroundServicesAssistant = new BackgroundServicesAssistant();

        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        $this->loadComponent('FormProtection');

        //check to see if Instance has been configured
        $instanceResult = $this->checkInstance();

        //if this Instance has not been configured, no need to load Auth
        if ($instanceResult === true) {
            $this->loadAuthComponent();
        }

        //localize the App using the cascading rules
        $this->setupLocalisationConstants();

    }

    /**
     * @param EventInterface $event
     * @return Response|void|null
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //die if application login is requested outside authorised domains
        $domain = str_replace(['http://', 'https://'], "", Router::fullBaseUrl());
        $isAllowed = $this->Settings->isDomainWhitelisted($domain);
        if (!$isAllowed) {
            $this->response = $this->response->withType('text/plain');
            $this->response = $this->response->withStringBody('Not Allowed!');
            return $this->response;
        }

        $this->applyHeaders();

        /*
         * Control what Libraries are loaded
         *  - \templates\element\corelib.php (default libraries to load)
         *  - overwrite by uncommenting
         *  - overwrite via $this->set() in a controller
         *  - overwrite via $this->set() in a view
         */
        //$coreLib = [
        //    'bootstrap' => true,
        //    'datatables' => false,
        //    'feather-icons' => true,
        //    'fontawesome' => true,
        //    'jQuery' => true,
        //    'jQueryUI' => false,
        //];
        //$this->set('coreLib', $coreLib);

        //control page-headers in the GUI
        $this->set('headerShow', true);
        $this->set('headerIcon', __(''));
        $this->set('headerTitle', __(''));
        $this->set('headerSubTitle', __(''));

        //kill flash messages if User has been redirected to /login from /
        $currentPath = $this->request->getPath();
        $redirectParam = $this->request->getQueryParams()['redirect'] ?? null;
        if ($currentPath == '/login' && $redirectParam === null) {
            $this->killAuthFlashMessages();
        }

        $this->redirectByUserLevel();
    }


    /**
     * Hard coded redirects to prevent unauthorised access.
     * Can force a user level to a certain controller/action
     *
     * @return void
     */
    private function redirectByUserLevel(): void
    {
        $authUser = ($this->request->getSession())->read("Auth.User", false);
        if (!$authUser || !isset($authUser['roles_list'])) {
            return;
        }

        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');

        if (in_array('SuperAdmin', $authUser['roles_list'])) {
            //do nothing they can go everywhere
        } elseif (in_array('Admin', $authUser['roles_list'])) {
            //do nothing they can go everywhere
        } elseif (in_array('SuperUser', $authUser['roles_list'])) {
            if (in_array($controller, ['Contents', 'Pages'])) {
                //redirect to special dashboard
                $this->redirect(['controller' => 'DashboardsForSuperUsers']);
            }
            $this->viewBuilder()->setLayoutPath('/interface/superuser');
        } elseif (in_array('User', $authUser['roles_list'])) {
            if (in_array($controller, ['Contents', 'Pages'])) {
                //redirect to special dashboard
                $this->redirect(['controller' => 'DashboardsForUsers']);
            }
            $this->viewBuilder()->setLayoutPath('/interface/user');
        } elseif (in_array('Manager', $authUser['roles_list'])) {
            if (in_array($controller, ['Contents', 'Pages'])) {
                //redirect to special dashboard
                $this->redirect(['controller' => 'DashboardsForManagers']);
            }
            $this->viewBuilder()->setLayoutPath('/interface/manager');
        } elseif (in_array('Supervisor', $authUser['roles_list'])) {
            if (in_array($controller, ['Contents', 'Pages'])) {
                //redirect to special dashboard
                $this->redirect(['controller' => 'DashboardsForSupervisors']);
            }
            $this->viewBuilder()->setLayoutPath('/interface/supervisor');
        } elseif (in_array('Operator', $authUser['roles_list'])) {
            if (in_array($controller, ['Contents', 'Pages'])) {
                //redirect to special dashboard
                //$this->redirect(['controller' => 'DashboardsForOperators']);
                $this->redirect(['controller' => 'ProductionQueue']);
            }
            $this->viewBuilder()->setLayoutPath('/interface/operator');
        }

    }

    /**
     * @throws Exception
     */
    private function loadAuthComponent()
    {
        /**
         * Authentication is the process of identifying users by provided credentials and ensuring
         * that users are who they say they are. Generally, this is done through a username and password,
         * that are checked against a known list of users.
         *
         * Authorization is the process of ensuring that an identified/authenticated user is
         * allowed to access the resources they are requesting.
         */
        $tinyAuthUserConfig = [
            'autoClearCache' => false,
            'multiRole' => true,
            'pivotTable ' => 'roles_users',
            'roleColumn ' => 'roles',
        ];

        $tinyAuthorizeConfig = [
            'loginRedirect' => [
                'controller' => '/',
                'action' => '',
            ],
            'logoutRedirect' => [
                'controller' => 'login',
                'action' => '',
            ],
            'authenticate' => [
                'TinyAuth.MultiColumn' => [
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password',
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'Users',
                ],
            ],
            'autoClearCache' => false,
            'authorize' => [
                'TinyAuth.Tiny' => $tinyAuthUserConfig
            ],
            'checkAuthIn' => 'Controller.initialize',
            'authError' => __('Sorry, you are not authorised to access that location.'),
            'flash' => [
                'element' => 'error',
                'key' => 'flash',
                'params' => ['class' => 'error this']
            ],
        ];

        try {
            $this->loadComponent('TinyAuth.Auth', $tinyAuthorizeConfig);
            //dd($this->Auth);
            //dd($this->Auth->getConfig());
            //dd($this->Auth->user());

            $this->loadComponent('TinyAuth.AuthUser', $tinyAuthUserConfig);
            //dd($this->AuthUser->user());
            //dd($this->AuthUser->getConfig());
        } catch (\Throwable $exception) {
            $this->Auditor->logError($exception->getMessage());
        }
    }

    /**
     * Checks to see if the Instance has been configured.
     * Will auto redirect if not.
     *
     * @return Response|true
     */
    private function checkInstance(): Response|bool
    {
        if (!$this->request->is(['ajax'])) {
            //redirect if possible or trap if using the Dummy driver
            $dbDriver = $this->Connection->config()['driver'];
            if ($dbDriver === 'Dummy') {
                $controller = $this->request->getParam('controller');
                if ($controller !== "Instance") {
                    if ($controller === 'Pages' || $controller === 'Contents') {
                        $this->viewBuilder()->setLayout('blank');
                        return $this->redirect(['controller' => 'instance', 'action' => 'configure']);
                    } else {
                        $redirectUrl = Router::fullBaseUrl() . "/instance/configure";
                        $contents = "<p class=\"center\">Please redirect your browser to&nbsp;<a href=\"{$redirectUrl}\">{$redirectUrl}</a>&nbsp;to install " . APP_NAME . ".</p>";
                        $title = APP_NAME . " Installer";
                        $staticHtml = getcwd() . "/templates/layout/static.php";
                        $staticHtml = file_get_contents($staticHtml);
                        $staticHtml = str_replace("{{contents}}", $contents, $staticHtml);
                        $staticHtml = str_replace("{{title}}", $title, $staticHtml);
                        die($staticHtml);
                    }
                }
            }

            //perform DB Migrations if there are no tables
            if ($dbDriver !== 'Dummy') {
                $tables = $this->Connection->getSchemaCollection()->listTables();
                if (($key = array_search('phinxlog', $tables)) !== false) {
                    unset($tables[$key]);
                }
                if (empty($tables) || !in_array('settings', $tables) || !in_array('users', $tables)) {
                    $InstanceTasks = new InstanceTasks();
                    $InstanceTasks->performMigrations();

                    /** @var User $user */
                    $Users = TableRegistry::getTableLocator()->get('Users');
                    $user = $Users->find('all')->where(['username' => 'SuperAdmin'])->first();
                    $tmpPassword = sha1(mt_rand() . mt_rand() . mt_rand() . mt_rand());
                    $user->password = $tmpPassword;
                    $user->password_expiry = (new DateTime())->subDays(1);
                    $Users->save($user);
                    $this->Flash->info(
                        __('Please login with the following credentials:<br><strong>Username</strong> SuperAdmin<br><strong>Password</strong> {0}', $tmpPassword),
                        ['escape' => false, 'params' => ['clickHide' => false]]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Defines constants that can be used throughput the APP for localisation.
     * See https://toggen.com.au/it-tips/cakephp-4-time-zones/ for some tips
     *
     * This function is loosely mirrored between AppCommand <--> AppController
     *
     * @throws Exception
     */
    private function setupLocalisationConstants(): void
    {
        //localizations from bootstrap
        $defaultLocalisations =
            [
                'location' => '',
                'locale' => Configure::read("App.defaultLocale"),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'HH:mm:ss',
                'datetime_format' => 'yyyy-MM-dd HH:mm:ss',
                'week_start' => 'Sunday',
                'timezone' => Configure::read("App.defaultTimezone"),
            ];

        //localizations from DB
        if (Configure::check('SettingsGrouped.localization')) {
            $appLocalizations = Configure::read('SettingsGrouped.localization');
        } else {
            $dbDriver = $this->Connection->config()['driver'];
            if ($dbDriver === 'Dummy') {
                $appLocalizations = [];
            } else {
                try {
                    $this->Settings->saveSettingsToConfigure();
                } catch (\Throwable $exception) {

                }
                if (Configure::check('SettingsGrouped.localization')) {
                    $appLocalizations = Configure::read('SettingsGrouped.localization');
                } else {
                    $appLocalizations = [];
                }
            }
        }

        //if User is logged in, configure the App for the User
        $userLocalizations = [];
        if (@$this->Auth instanceof AuthComponent && $this->Auth->user()) {
            if (isset($this->Auth->user()['user_localizations'][0])) {
                $userLocalizations = $this->Auth->user()['user_localizations'][0];
                $userLocalizations = array_filter($userLocalizations);
            }
        }

        $compiledLocalisations = array_merge($defaultLocalisations, $appLocalizations, $userLocalizations);

        //set the constants
        if (!defined('LCL')) {
            define('LCL', $compiledLocalisations);
        }
        if (!defined('LCL_LOCATION')) {
            define('LCL_LOCATION', $compiledLocalisations['location']);
        }
        if (!defined('LCL_LOCALE')) {
            define('LCL_LOCALE', $compiledLocalisations['locale']);
        }
        if (!defined('LCL_DF')) {
            define('LCL_DF', $compiledLocalisations['date_format']);
        }
        if (!defined('LCL_TF')) {
            define('LCL_TF', $compiledLocalisations['time_format']);
        }
        if (!defined('LCL_DTF')) {
            define('LCL_DTF', $compiledLocalisations['datetime_format']);
        }
        if (!defined('LCL_WS')) {
            define('LCL_WS', $compiledLocalisations['week_start']);
        }
        if (!defined('LCL_TZ')) {
            define('LCL_TZ', $compiledLocalisations['timezone']);
        }

        //write back a couple of values into Configure
        Configure::write("App.defaultLocale", LCL_LOCALE);

        //read company details into constants
        $company = Configure::read('SettingsGrouped.company');
        if (!defined('COMPANY_NAME')) {
            define('COMPANY_NAME', $company['company_name'] ?? '');
            define('COMPANY_ADDRESS_1', $company['company_address_1'] ?? '');
            define('COMPANY_ADDRESS_2', $company['company_address_2'] ?? '');
            define('COMPANY_SUBURB', $company['company_suburb'] ?? '');
            define('COMPANY_STATE', $company['company_state'] ?? '');
            define('COMPANY_POSTCODE', $company['company_postcode'] ?? '');
            define('COMPANY_PHONE', $company['company_phone'] ?? '');
            define('COMPANY_EMAIL', $company['company_email'] ?? '');
            define('COMPANY_WEB_ADDRESS', $company['company_web_address'] ?? '');
        }
    }

    /**
     * Apply response headers for increased Application security
     */
    private function applyHeaders()
    {
        $this->response = $this->response->withHeader('X-Frame-Options', 'DENY');
    }

    /**
     * kill Flash messages regarding Auth errors
     */
    protected function killAuthFlashMessages(): void
    {
        if ($this->Auth) {
            $messages = $this->request->getSession()->read("Flash.flash");
            if (is_array($messages)) {
                foreach ($messages as $k => $msg) {
                    if ($msg) {
                        if ($msg['message'] == $this->Auth->getConfig('authError')) {
                            $this->request->getSession()->delete("Flash.flash.{$k}");
                        }
                    }
                }
            }
        }
    }

}
