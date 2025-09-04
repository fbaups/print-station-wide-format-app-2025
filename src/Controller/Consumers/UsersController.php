<?php
declare(strict_types=1);

namespace App\Controller\Consumers;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     * @throws \Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->set('typeMap', $this->Users->getSchema()->typeMap());

    }

    /**
     * @param EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Skip authorization - TinyAuth middleware handles this
        if (isset($this->Authorization)) {
            $this->Authorization->skipAuthorization();
        }

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['edit']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

    }

    /**
     * Index method
     *
     * @return Response|null Renders view
     */
    public function index()
    {
        return $this->redirect('/');
    }

    /**
     * invite a new user method
     *
     * @return Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function invite()
    {
        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $peerRoles = $this->Users->Roles->getPeerRoles($this->getCurrentUserRoles(), false);
            $rolesToApply = $this->request->getData('roles');
            $rolesToApplyCleaned = $this->Users->Roles->validatePeerRoles($peerRoles, $rolesToApply);

            if (empty($rolesToApplyCleaned)) {
                $this->Flash->error(__('Failed to create invitation as a matching Role could not be found. Please, try again.'));
                return $this->redirect(['controller' => 'users', 'action' => 'invite']);
            }

            $data = $this->request->getData();
            $data['roles']['_ids'] = array_keys($rolesToApplyCleaned);
            $data['username'] = $data['email'];
            /** @var User $user */
            $user = $this->Users->sendInvitationLink($data);

            if ($user) {
                $this->Flash->success(__('An invitation has been sent to {0}.', $user->email));

                $mode = Configure::read('mode');
            if ($mode && in_array(strtolower($mode), ['dev', 'development'])) {
                    $userInfo = $this->Users->userInvitationData;
                    $this->Flash->info(
                        __('Invitation URL for {0}: <strong>{1}</strong>', $userInfo['full_name'], $userInfo['invitation_url']),
                        ['escape' => false, 'params' => ['clickHide' => false]]
                    );
                }

                $this->Auditor->auditInfo(__('Invitation sent to ID:{0} {1} {2} <{3}>.', $user->id, $user->first_name, $user->last_name, $user->email));

                return $this->redirect('/');
            }
            $this->Flash->error(__('Failed to create an invitation. Please, try again.'));

        }

        $peerRoles = $this->Users->Roles->getPeerRoles($this->getCurrentUserRoles());
        $this->set(compact('user', 'peerRoles'));
    }

}
