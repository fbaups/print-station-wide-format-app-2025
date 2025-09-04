<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\UsersTable;
use App\Utility\Feedback\ReturnAlerts;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * ConnectorClosed Controller
 *
 * Pseudo API to serve data from the Application.
 * Users must be logged in to access the data.
 * This is enforced by requiring $this->Auth->user() in the beforeFilter()
 *
 */
class ConnectorClosedController extends AppController
{
    use ReturnAlerts;

    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * @param EventInterface $event
     * @return Response|void|null
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['csrf-token']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

        //uncomment if User must be Authenticated to access this controller
        if (!$this->Authentication->getIdentity()) {
            $this->addDangerAlerts(__('Invalid User'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        //only allow specific request types
        if (!$this->request->is(['get'])) {
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
            $this->addDangerAlerts(__('Missing Listener'));
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
            $this->addDangerAlerts(__('Invalid Listener'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

    }

    /**
     * CSRF Token method.
     * Similar to Open Controller but with more info.
     *
     * @return Response|null
     */
    public function csrfToken(): ?Response
    {
        if (!$this->request->is('ajax')) {
            $this->addDangerAlerts(__('Invalid HTTP Method'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        /** @var UsersTable $Users */
        $Users = TableRegistry::getTableLocator()->get('Users');

        $identity = $this->Authentication->getIdentity();
        $usersSessionData = $identity ? $Users->getExtendedUserSessionData($identity->id) : [];
        $sessionTimeout = $usersSessionData['session_timeout'];
        $inactivityTimeout = $usersSessionData['inactivity_timeout'];
        $sessionTimeoutTimestamp = time() + $sessionTimeout;

        //cakePHP4/5
        $token = [
            'csrfToken' => $this->request->getAttribute('csrfToken'),
            'session_started' => $this->request->getSession()->started(),
            'session_timeout' => $sessionTimeout,
            'session_timeout_timestamp' => $sessionTimeoutTimestamp,
            'inactivity_timeout' => $inactivityTimeout,
        ];

        $responseData = json_encode($token, JSON_PRETTY_PRINT);

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

    /**
     * ReturnAlerts Token method
     *
     * @return Response|null
     */
    public function returnAlerts(): ?Response
    {
        $ts = microtimestamp('.');
        $data = $this->request->getSession()->read('ReturnAlerts.sequence', [$ts => 'No Return Alerts for this HTTP Session']);

        $responseData = json_encode($data, JSON_PRETTY_PRINT);

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }
}
