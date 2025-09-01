<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Controller\AppController;
use App\Controller\Component\CheckDatabaseDriversComponent;
use App\Model\Table\BackgroundServicesTable;
use App\Utility\Instances\InstanceTasks;
use App\Utility\Instances\LoadBalancerProxyDetector;
use App\Utility\Releases\VersionControl;
use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;
use arajcany\PrePressTricks\Graphics\FFmpeg\FFmpegCommands;
use arajcany\PrePressTricks\Graphics\Ghostscript\GhostscriptCommands;
use arajcany\PrePressTricks\Graphics\ImageMagick\ImageMagickCommands;
use arajcany\PrePressTricks\Utilities\ImageInfo;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Exception;

/**
 * Instance Controller
 *
 * @property BackgroundServicesAssistant $BackgroundServicesAssistant
 * @property BackgroundServicesTable $BackgroundServices
 * @property CheckDatabaseDriversComponent $CheckDatabaseDrivers
 * @property VersionControl $Version
 */
class InstanceController extends AppController
{
    private string $remote_update_url;
    private VersionControl $Version;
    public Table|BackgroundServicesTable $BackgroundServices;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('CheckDatabaseDrivers');

        if ($this->connectionDriver !== 'Dummy') {
            $this->BackgroundServices = TableRegistry::getTableLocator()->get('BackgroundServices');

            $this->Version = new VersionControl();
        }

        try {
            if ($this->connectionDriver !== 'Dummy') {
                $this->remote_update_url = $this->Settings->getRemoteUpdateUrl();
            } else {
                $this->remote_update_url = '';
            }
        } catch (\Throwable $exception) {
            $this->remote_update_url = '';
        }
    }

    /**
     * @param EventInterface $event
     * @return Response|void|null
     * @throws Exception
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['configure']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

        /*
         * Figure out which action to perform based on DB driver state and other stuff
         */
        if (!$this->request->is('ajax')) {
            if ($this->CheckDatabaseDrivers->configExists() && $this->request->getParam('action') === "configure") {
                return $this->redirect(['action' => 'updates']);
            }

            if (!$this->CheckDatabaseDrivers->configExists() && $this->request->getParam('action') !== "configure") {
                return $this->redirect(['controller' => 'instance', 'action' => 'configure']);
            }
        }
    }

    /**
     * Index method
     *
     * @return Response|null|void Renders view
     */
    public function index()
    {
        $GhostscriptCommands = new GhostscriptCommands();
        $CallasCommands = new CallasCommands();
        $ImageInfo = new ImageInfo();
        $ImageMagickCommands = new ImageMagickCommands();
        $FFmpegCommands = new FFmpegCommands();

        $this->set('GhostscriptCommands', $GhostscriptCommands);
        $this->set('CallasCommands', $CallasCommands);
        $this->set('ImageInfo', $ImageInfo);
        $this->set('ImageMagickCommands', $ImageMagickCommands);
        $this->set('FFmpegCommands', $FFmpegCommands);

        $Detector = new LoadBalancerProxyDetector($this->request);
        $isLoadBalancerOrProxy = $Detector->isLoadBalancerOrProxy();
        $this->set('isLoadBalancerOrProxy', $isLoadBalancerOrProxy);
        $serverParams = $Detector->getServerParams();
        $this->set('serverParams', $serverParams);
    }

    /**
     * Display a list of Updates
     */
    public function updates()
    {
        //initiate an index of the site
        $InstanceTasks = new InstanceTasks();
        $errand = $InstanceTasks->generateFsoStatsErrand();
        $this->set('fsoErrand', $errand);

        $countRunningServices = $this->BackgroundServicesAssistant->countRunningServices();
        $this->set('countOfRunningServices', $countRunningServices);

        $hash = $this->Version->_getOnlineVersionHistoryHash();
        if ($hash) {
            $hash = @array_reverse($hash);
        } else {
            $hash = [];
        }
        $this->set('versions', $hash);


        $this->set('remote_update_url', $this->remote_update_url);

        $settingRemoteUpdateUrl = $this->Settings->find('all')->where(['property_key' => 'remote_update_url'])->first();
        $this->set('remote_update_url_id', $settingRemoteUpdateUrl->id);

        $this->set('currentVersion', $this->Version->getCurrentVersionTag());

        return null;
    }

    /**
     * Upgrade to the requested version
     *
     * @param null $upgradeFileReleaseDate
     * @return Response|null
     */
    public function upgrade($upgradeFileReleaseDate = null): ?Response
    {
        if (!in_array(strtolower(Configure::read('mode')), ['uat', 'test', 'prod', 'production'])) {
            $this->Flash->error(__('You are not allowed to Upgrade!'));
            return $this->redirect(['action' => 'updates']);
        } elseif (empty(Configure::read('mode'))) {
            $this->Flash->error(__('Please add a Config value of  ["mode"=>"prod"] to allow upgrading'));
            return $this->redirect(['action' => 'updates']);
        }

        if (!$upgradeFileReleaseDate) {
            $this->Flash->error(__('Sorry, invalid upgrade file.'));
            return $this->redirect(['action' => 'updates']);
        }

        $upgradeFileReleaseDate = Security::decrypt64Url($upgradeFileReleaseDate);

        $versionHistory = $this->Version->_getOnlineVersionHistoryHash();
        $tag = 0;
        $installerUrl = false;
        foreach ($versionHistory as $version) {
            if (isset($version['release_date']) && $upgradeFileReleaseDate == $version['release_date']) {
                $tag = $version['tag'];
                $installerUrl = $version['installer_url'];
            }
        }

        $InstanceTasks = new InstanceTasks();
        $upgradeStatus = false;
        if ($installerUrl) {
            $upgradeStatus = $InstanceTasks->performUpgrade($installerUrl);
        }

        if ($upgradeStatus) {
            $this->Flash->success(__('Successfully upgraded to version {0}.', $tag));
        } else {
            $this->Flash->error(__('Sorry, failed to upgrade to version {0}.', $tag));
        }

        //flash all Alerts
        $this->Flash->flashMassInsertAlerts($InstanceTasks->getAllAlertsForMassInsert());

        return $this->redirect(['controller' => 'instance', 'action' => 'updates']);
    }

    /**
     * Configure method
     * Directed here if the DummyDB config is being used. See AppController()
     *
     * @return Response|null
     * @throws Exception
     */
    public function configure(): ?Response
    {
        //because this is the installation page, we need to force https in the router if possible
        $Detector = new LoadBalancerProxyDetector($this->request);
        $Detector->upgradeRouterFullBaseUrlToHttps();

        $this->viewBuilder()->setLayout('instance-form');

        $this->killAuthFlashMessages();

        $data = $this->request->getData();

        $isAjax = isset($data['is-ajax']) && boolval($data['is-ajax']);

        if ($this->request->is(['ajax', 'patch', 'post', 'put']) && $isAjax) {
            $this->loadComponent('CheckDatabaseDrivers');

            if (isset($data['database-driver-selection'])) {
                $dumpConfig = asBool($data['dump']);
                if ($data['database-driver-selection'] === 'Sqlserver') {
                    $return = $this->CheckDatabaseDrivers->checkSqlserver($data['server']['sql'], $dumpConfig);
                } else if ($data['database-driver-selection'] === 'Mysql') {
                    $return = $this->CheckDatabaseDrivers->checkMysql($data['server']['mysql'], $dumpConfig);
                } else if ($data['database-driver-selection'] === 'Sqlite') {
                    $return = $this->CheckDatabaseDrivers->checkSqlite($data['server']['sqlite'], $dumpConfig);
                } else {
                    $return = false;
                }

                if (is_array($return)) {
                    $return['dump'] = $dumpConfig;
                }

            } elseif (isset($data['instance-configuration-selection'])) {
                $currentMode = Configure::read('mode');
                $newMode = $data['instance-configuration-selection'];
                if (in_array($newMode, ['dev', 'test', 'prod']) && is_file(CONFIG . 'app_local.php')) {
                    $contents = file_get_contents(CONFIG . 'app_local.php');
                    $contents = str_replace("'mode' => '{$currentMode}',", "'mode' => '{$newMode}',", $contents);
                    if (file_put_contents(CONFIG . 'app_local.php', $contents)) {
                        $return = true;
                    } else {
                        $return = false;
                    }
                } else {
                    $return = false;
                }

            } else {
                $return = false;
            }

            $responseData = json_encode($return, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        return null;
    }

    public function migrations(): ?Response
    {
        try {
            $InstanceTasks = new InstanceTasks();
            $result = $InstanceTasks->performMigrations();

            if ($result) {
                $this->Flash->success(__('Database migrations completed successfully.'));
            } else {
                $this->Flash->info(__('There were no migrations to perform.'));
            }

        } catch (\Throwable $exception) {
            $this->Flash->error(__('Failed to complete the database migrations.'));
            $this->Flash->error($exception->getMessage());
        }

        return $this->redirect($this->referer());
    }

    public function clearCache(): ?Response
    {
        try {
            $results = Cache::clearAll();
            $this->Flash->success(__('Cache cleared.'));

            foreach ($results as $cacheName => $result) {
                if ($result == 1) {
                    $result = "Cleared";
                } elseif ($result == 0) {
                    $result = "Not Cleared";
                }

                $this->Flash->info("$cacheName: $result");
            }

        } catch (\Throwable $exception) {
            $this->Flash->error(__('Could not clear the Cache.'));
        }

        return $this->redirect($this->referer());
    }

}

