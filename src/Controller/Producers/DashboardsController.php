<?php
declare(strict_types=1);

namespace App\Controller\Producers;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use Exception;

class DashboardsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function beforeFilter(EventInterface $event)
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
        $this->set('title', 'Default Dashboard');
        $this->viewBuilder()->setTemplate('primary');
    }

    public function secondary()
    {
        $this->set('title', 'Secondary Dashboard');
        $this->viewBuilder()->setTemplate('secondary');
    }

    public function tertiary()
    {
        $this->set('title', 'Tertiary Dashboard');
        $this->viewBuilder()->setTemplate('tertiary');
    }

}
