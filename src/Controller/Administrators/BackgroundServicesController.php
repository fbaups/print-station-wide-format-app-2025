<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Controller\AppController;
use App\Model\Table\BackgroundServicesTable;
use App\Model\Table\HeartbeatsTable;
use App\Model\Table\SettingsTable;
use App\Utility\Instances\InstanceTasks;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;
use ZipArchive;

/**
 * BackgroundServices Controller
 *
 * @property BackgroundServicesAssistant $BackgroundServicesAssistant
 * @property BackgroundServicesTable $BackgroundServices
 * @property HeartbeatsTable $Heartbeats
 */
class BackgroundServicesController extends AppController
{
    public InstanceTasks $InstanceTasks;
    public Table|BackgroundServicesTable $BackgroundServices;
    public Table|HeartbeatsTable $Heartbeats;

    /**
     * Initialize method
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->InstanceTasks = new InstanceTasks();
        $this->BackgroundServices = TableRegistry::getTableLocator()->get('BackgroundServices');

        $this->Heartbeats = TableRegistry::getTableLocator()->get('Heartbeats');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        $this->FormProtection->setConfig('unlockedActions', ['manage']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        //if ($this->request->is('ajax')) {
        //    $this->FormProtection->setConfig('validate', false);
        //}

    }

    /**
     * Index method
     *
     * @return Response|null
     */
    public function index(): ?Response
    {
        $services = $this->BackgroundServicesAssistant->_getServices();
        $this->set('services', $services);

        $running = $this->BackgroundServicesAssistant->areProtectionServicesRunning();
        $this->set('areProtectionServicesRunning', $running);

        $this->set('isNssm', $this->BackgroundServicesAssistant->isNssm);
        if (!$this->BackgroundServicesAssistant->isNssm) {
            $this->viewBuilder()->setTemplate('index_nssm');
        }

        return null;
    }

    /**
     * Create Batch files that aid with Install/Remove of the Windows Service
     *
     * @return Response|null
     * @throws Exception
     */
    public function downloadNssm(): ?Response
    {
        $this->set('isNssm', $this->BackgroundServicesAssistant->isNssm);
        $nssmUrl = "https://nssm.cc/ci/nssm-2.24-103-gdee49fc.zip";
        $nssmUrlChecksum = "0722c8a775deb4a1460d1750088916f4f5951773";
        $nssmZipBasename = array_reverse(explode("/", $nssmUrl))[0];
        $nssmZipFilename = pathinfo($nssmZipBasename, PATHINFO_FILENAME);
        $nssmZipSaveLocation = $this->BackgroundServicesAssistant->batchLocation . $nssmZipBasename;

        //check if download exists
        if (is_file($nssmZipSaveLocation)) {
            $nssmLocalChecksum = sha1_file($nssmZipSaveLocation);
            if ($nssmLocalChecksum == $nssmUrlChecksum) {
                $performDownload = false;
            } else {
                $performDownload = true;
            }
        } else {
            $performDownload = true;
        }

        //download (or not)
        if ($performDownload) {
            $nssmDownload = file_get_contents($nssmUrl);
            $nssmDownloadChecksum = sha1($nssmDownload);
        } else {
            $nssmDownloadChecksum = false;
        }

        //save if ok
        if ($performDownload && $nssmDownloadChecksum == $nssmUrlChecksum) {
            file_put_contents($nssmZipSaveLocation, $nssmDownload);
        } elseif ($performDownload && $nssmDownloadChecksum != $nssmUrlChecksum) {
            $this->Flash->error(__('Sorry, there appears to be an issue with downloading NSSM. Please try again later.'));
            return $this->redirect(['action' => 'index']);
        }

        $osBit = strlen(decbin(~0));
        $exeName = "{$nssmZipFilename}/win{$osBit}/nssm.exe";

        $zip = new ZipArchive();
        $zip->open($nssmZipSaveLocation);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($exeName == $stat['name']) {
                $exe = $zip->getFromIndex($stat['index']);
                file_put_contents($this->BackgroundServicesAssistant->nssm, $exe);
                break;
            }
        }
        $zip->close();
        unlink($nssmZipSaveLocation);

        $this->Flash->success(__('Downloaded and installed NSSM in {0}', $this->BackgroundServicesAssistant->nssm));
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Install the Windows Service and create Batch files that aid with Install/Remove of such
     *
     * @return Response|null
     * @throws Exception
     */
    public function install(): ?Response
    {
        $this->set('isNssm', $this->BackgroundServicesAssistant->isNssm);

        $this->set('serviceNamePrefix', $this->BackgroundServicesAssistant->getAppNameCamelized());
        $this->set('phpVersionMatchToIis', $this->InstanceTasks->getPhpBinary());

        $errandBackgroundServiceSetting = $this->Settings->find()->where(['property_key' => 'errand_background_service_limit'])->first();
        $messageBackgroundServiceSetting = $this->Settings->find()->where(['property_key' => 'message_background_service_limit'])->first();
        $databasePurgerBackgroundServiceSetting = $this->Settings->find()->where(['property_key' => 'database_purger_background_service_limit'])->first();
        $hotFolderBackgroundServiceSetting = $this->Settings->find()->where(['property_key' => 'hot_folder_background_service_limit'])->first();
        $scheduledTaskBackgroundServiceSetting = $this->Settings->find()->where(['property_key' => 'scheduled_task_background_service_limit'])->first();
        $this->set('errandBackgroundServiceSetting', $errandBackgroundServiceSetting);
        $this->set('messageBackgroundServiceSetting', $messageBackgroundServiceSetting);
        $this->set('databasePurgerBackgroundServiceSetting', $databasePurgerBackgroundServiceSetting);
        $this->set('hotFolderBackgroundServiceSetting', $hotFolderBackgroundServiceSetting);
        $this->set('scheduledTaskBackgroundServiceSetting', $scheduledTaskBackgroundServiceSetting);

        if ($this->request->is(['post'])) {

            //Save the Settings in case they have changed
            /** @var SettingsTable $Settings */
            $Settings = TableRegistry::getTableLocator()->get('Settings');
            $Settings->setSetting('errand_background_service_limit', $this->request->getData('errand_background_service_limit'));
            $Settings->setSetting('message_background_service_limit', $this->request->getData('message_background_service_limit'));
            $Settings->setSetting('database_purger_background_service_limit', $this->request->getData('database_purger_background_service_limit'));
            $Settings->setSetting('hot_folder_background_service_limit', $this->request->getData('hot_folder_background_service_limit'));
            $Settings->setSetting('scheduled_task_background_service_limit', $this->request->getData('scheduled_task_background_service_limit'));

            $options = [
                'php_version' => $this->request->getData('php_version'),
                'username' => $this->request->getData('username'),
                'password' => $this->request->getData('password'),
                'service_start' => $this->request->getData('service_start'),
                'errand_background_service_limit' => $this->request->getData('errand_background_service_limit'),
                'message_background_service_limit' => $this->request->getData('message_background_service_limit'),
                'database_purger_background_service_limit' => $this->request->getData('database_purger_background_service_limit'),
                'hot_folder_background_service_limit' => $this->request->getData('hot_folder_background_service_limit'),
                'scheduled_task_background_service_limit' => $this->request->getData('scheduled_task_background_service_limit'),
            ];

            $result = $this->BackgroundServicesAssistant->installBackgroundServices($options);

            if ($result) {
                $options = ['escape' => false];
                $this->Flash->flashMassInsertAlerts($this->BackgroundServicesAssistant->getAllAlertsForMassInsert(), $options);
            } else {
                $this->Flash->error(__('Failed to install Windows Services.'));
            }
            return $this->redirect(['action' => 'index']);
        }

        return null;
    }

    /**
     * Remove the Windows Services
     *
     * @return Response|null
     * @throws Exception
     */
    public function uninstall(): ?Response
    {
        $this->set('isNssm', $this->BackgroundServicesAssistant->isNssm);

        $countAllServices = $this->BackgroundServicesAssistant->countAllServices();
        $countRunningServices = $this->BackgroundServicesAssistant->countRunningServices();
        if ($countRunningServices > 0) {
            $this->Flash->error(__('There are {0} Background Services running. Please stop them before uninstalling.', $countRunningServices));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post'])) {

            $result = $this->BackgroundServicesAssistant->uninstallBackgroundServices();

            if ($result) {
                if (!$this->BackgroundServicesAssistant->areProtectionServicesRunning()) {
                    $countAllServicesAfter = $this->BackgroundServicesAssistant->countAllServices();
                    $countRemovedServices = $countAllServicesAfter - $countAllServices;
                    $this->Flash->success(__('Uninstalled {0} Windows Services.', $countRemovedServices));
                }
                $this->Flash->flashMassInsertAlerts($this->BackgroundServicesAssistant->getAllAlertsForMassInsert());
            } else {
                $this->Flash->error(__('Failed to uninstall the Windows Services.'));
            }
            return $this->redirect(['action' => 'index']);
        }

        return null;
    }

    /**
     * @param $serviceName
     * @return Response|null
     */
    public function stop($serviceName): ?Response
    {
        $this->BackgroundServicesAssistant->kill($serviceName);
        $this->Flash->flashMassInsertAlerts($this->BackgroundServicesAssistant->getAllAlertsForMassInsert());

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @param $serviceName
     * @return Response|null
     */
    public function start($serviceName): ?Response
    {
        $this->BackgroundServicesAssistant->start($serviceName);
        $this->Flash->flashMassInsertAlerts($this->BackgroundServicesAssistant->getAllAlertsForMassInsert());

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @return Response|null
     */
    public function recycle(): ?Response
    {
        $this->BackgroundServicesAssistant->kill('all');
        $this->BackgroundServicesAssistant->start('were-running');
        $this->Flash->flashMassInsertAlerts($this->BackgroundServicesAssistant->getAllAlertsForMassInsert());

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @return Response|null
     */
    public function shutdown(): ?Response
    {
        $this->BackgroundServicesAssistant->kill('all');
        $this->BackgroundServicesAssistant->start('were-running');
        $this->Flash->flashMassInsertAlerts($this->BackgroundServicesAssistant->getAllAlertsForMassInsert());

        return $this->redirect(['action' => 'index']);
    }

    public function manage()
    {
        if (!$this->request->is(['ajax'])) {
            return $this->redirect(['action' => 'index']);
        }

        $data = $this->request->getData();
        $serviceName = $data['service-name'];
        $serviceAction = $data['service-action'];
        $isValidServiceName = $this->BackgroundServicesAssistant->_isValidServiceName($serviceName);
        $isValidServiceAction = $this->BackgroundServicesAssistant->_isValidServiceAction($serviceAction);
        if (!$isValidServiceName || !$isValidServiceAction) {
            $responseData = ['response' => false, 'task' => 'reload'];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        $result = $this->BackgroundServicesAssistant->handleServiceRequest($serviceName, $serviceAction);


        $responseData = json_encode($result, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }


    public function servicesInfo($serviceName = null): Response
    {
        $services = $this->BackgroundServicesAssistant->_getServices(true);

        $result = [];
        foreach ($services as $service) {
            $result[$service['name']] = $service;
        }

        $responseData = json_encode($result, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

}
