<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\HotFoldersTable;
use App\Model\Table\SeedsTable;
use App\Utility\Feedback\ReturnAlerts;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * ConnectorHotFolders Controller
 * Allows anonymous Users to interact with HotFolders.
 *
 * Pseudo API to serve data from the Application.
 * No User Authentication required to access the data - be careful what is placed here!
 *
 * BearerTokens can be used to control access.
 *      $iBearerTokenValid = $Seeds->validateBearerTokenInRequest($this->request);
 *      if (!$iBearerTokenValid) {
 *          return $this->response;
 *      }
 *
 * Auth->user() can be used to control access.
 *      if (!$this->Auth->user()) {
 *          return $this->response;
 *      }
 */
class ConnectorWebHotFoldersController extends AppController
{
    use ReturnAlerts;

    protected Table|HotFoldersTable $HotFolders;

    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->HotFolders = TableRegistry::getTableLocator()->get('HotFolders');
        $this->set('typeMap', $this->HotFolders->getSchema()->typeMap());

    }

    /**
     * @param EventInterface $event
     * @return Response|void|null
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        $this->FormProtection->setConfig('unlockedActions', ['submit']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

        //uncomment if User must be Authenticated to access this controller
        //if (!$this->Auth->user()) {
        //    $this->addDangerAlerts(__('Invalid User'));
        //    $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
        //    $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
        //    $this->response = $this->response->withType('json');
        //    $this->response = $this->response->withStringBody($responseData);
        //
        //    return $this->response;
        //}

        //only allow specific request types
        if (!$this->request->is(['patch', 'post', 'put', 'get'])) {
            $this->addDangerAlerts(__('Invalid HTTP Method'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        $prefix = $this->request->getParam('prefix');
        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');

        //must call a specific action
        if ($action === 'index') {
            $this->addDangerAlerts(__('Missing Hot Folder'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        //check that the requested action exists
        try {
            $isAction = $this->isAction($action);
        } catch (\Throwable $exception) {
            $isAction = false;
        }
        if (!$isAction) {
            $this->addDangerAlerts(__('Invalid Hot Folder'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

    }

    /**
     * Web submission into a Hot Folder
     *
     * @param $hotFolderName
     * @return \Cake\Http\Response
     */
    public function submit($hotFolderName = null)
    {
        /** @var SeedsTable $Seeds */
        $Seeds = $this->getTableLocator()->get('Seeds');
        $iBearerTokenValid = $Seeds->validateBearerTokenInRequest($this->request);
        if (!$iBearerTokenValid) {
            $this->mergeAlerts($Seeds->getAllAlertsForMerge());
            $this->Auditor->auditWarning(__('Bearer Token checks failed.'));
            $responseData = ['status' => 'danger', 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        $this->HotFolders->saveSubmission($hotFolderName, $this->request);
        $alerts = $this->HotFolders->getAllAlerts();

        if (!empty($alerts['danger'])) {
            $status = 'danger';
        } elseif (!empty($alerts['warning'])) {
            $status = 'warning';
        } elseif (!empty($alerts['info'])) {
            $status = 'info';
        } else {
            $status = 'success';
        }

        $responseData = ['status' => $status, 'alerts' => $this->HotFolders->getAllAlertsLogSequence()];
        $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

}
