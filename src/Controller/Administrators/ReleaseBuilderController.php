<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Utility\Releases\BuildTasks;
use App\Utility\Releases\RemoteUpdateServer;
use Cake\Event\EventInterface;
use Exception;

/**
 * ReleaseBuilder Controller
 *
 */
class ReleaseBuilderController extends AppController
{
    private RemoteUpdateServer $RemoteUpdateServer;
    private BuildTasks $BuildTasks;

    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->RemoteUpdateServer = new RemoteUpdateServer();
        $this->BuildTasks = new BuildTasks();
    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        //if ($this->request->is('ajax')) {
        //    $this->FormProtection->setConfig('validate', false);
        //}
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $hKeyCheck = (bool)$this->InternalOptions->getSecurityKey();
        $hSaltCheck = (bool)$this->InternalOptions->getSecuritySalt();
        $this->set('hKeyCheck', $hKeyCheck);
        $this->set('hSaltCheck', $hSaltCheck);

        if ($this->request->is('post')) {
            $passChecks = true;

            $data = ($this->request->getData());
            $result = $this->RemoteUpdateServer->updateData($data);
            if (!$result) {
                $this->Flash->error(__('Error saving the Remote Update Server configuration.'));
                $passChecks = false;
            }

            $hKey = $this->request->getData('encryption-key');
            if (in_array($hKey, ['', 1, '1', 'true', true], true)) {
                //bypass modification
            } elseif (strlen($hKey) >= 40) {
                $this->InternalOptions->updateSecurityKey($hKey);
            } else {
                $this->Flash->error(__('The Encryption Key needs to be 40 characters or longer.'));
                $passChecks = false;
            }

            $hSalt = $this->request->getData('encryption-salt');
            if (in_array($hSalt, ['', 1, '1', 'true', true], true)) {
                //bypass modification
            } elseif (strlen($hSalt) >= 40) {
                $this->InternalOptions->updateSecuritySalt($hSalt);
            } else {
                $this->Flash->error(__('The Encryption Salt needs to be 40 characters or longer.'));
                $passChecks = false;
            }

            if ($passChecks) {
                $this->Flash->success(__('The Release Builder Configuration has been updated.'));
                return $this->redirect(['action' => 'check']);
            }
        }
    }

    public function check()
    {
        $this->BuildTasks->makeReleaseBuilderBatFile();

        $batPath = ROOT . DS . "bin\\ReleaseBuilder.bat";
        $this->set('batPathIsValid', is_file($batPath));
        $this->set('batPath', $batPath);
    }

}
