<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;
use Exception;

/**
 * DataObjects Controller
 *
 */
class DataObjectsController extends AppController
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

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['csrf-token']);

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
        $responseData = json_encode(false, JSON_PRETTY_PRINT);

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

    /**
     * CSRF Token method
     *
     * @return Response|null
     */
    public function csrfToken(): ?Response
    {
        //cakePHP3
        //$token = ['csrfToken' => $this->request->getParam('_csrfToken')];

        //cakePHP4
        $token = ['csrfToken' => $this->request->getAttribute('csrfToken')];

        $responseData = json_encode($token, JSON_PRETTY_PRINT);

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }
}
