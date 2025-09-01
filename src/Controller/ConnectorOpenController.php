<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\DataBlobsTable;
use App\Model\Table\SeedsTable;
use App\Model\Table\UsersTable;
use App\Utility\Feedback\ReturnAlerts;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * ConnectorOpen Controller
 *
 * Pseudo API to serve data from the Application.
 * No User Authentication required to access the data - be careful what is placed here!
 *
 */
class ConnectorOpenController extends AppController
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
        $this->FormProtection->setConfig('unlockedActions', ['json']);

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
     * Similar to Closed Controller but with less info.
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

        //cakePHP4/5
        $token = [
            'csrfToken' => $this->request->getAttribute('csrfToken'),
        ];

        $responseData = json_encode($token, JSON_PRETTY_PRINT);

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

    /**
     * Example Listener method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function exampleListener(...$urlPath)
    {
        //do something with the request
        $params = $this->request->getQueryParams();
        $data = $this->request->getData();
        $headers = $this->request->getHeaders();
        $attributes = $this->request->getAttributes();

        $alerts = [
            'success' => [],
            'danger' => [],
            'warning' => [],
            'info' => [],
            'data' => $data,
            'params' => $params,
            'attributes' => $attributes,
        ];
        $responseData = ['status' => 'success', 'alerts' => $alerts];
        $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

}
