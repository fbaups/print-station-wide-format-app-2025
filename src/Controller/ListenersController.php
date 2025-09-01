<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Exception;

/**
 * Listeners Controller
 *
 */
class ListenersController extends AppController
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
    }

    /**
     * @param EventInterface $event
     * @return Response|void|null
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

        //only allow simple GET requests
        if (!$this->request->is(['get'])) {
            $responseData = ['status' => 'danger', 'alerts' => ['danger' => [__('Invalid HTTP Method')]]];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        $controller = $this->request->getParam('controller');
        $action = $this->request->getParam('action');

        //must call a specific action
        if ($action === 'index') {
            $responseData = ['status' => 'danger', 'alerts' => ['danger' => [__('Missing Listener')]]];
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
            $responseData = ['status' => 'danger', 'alerts' => ['danger' => [__('Invalid Listener')]]];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

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
