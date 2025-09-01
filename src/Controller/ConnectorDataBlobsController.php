<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\DataBlobsTable;
use App\Model\Table\SeedsTable;
use App\Utility\Feedback\ReturnAlerts;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * ConnectorDataBlobs Controller
 * Allows anonymous Users to interact with DataBlobs.
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
class ConnectorDataBlobsController extends AppController
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
        $this->FormProtection->setConfig('unlockedActions', ['save']);

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
     * Get a listing of the DataBlobs in the DB
     *
     * @param $limit
     * @param $page
     * @param $order
     * @return Response
     */
    public function list($limit = null, $page = null, $order = null)
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

        /** @var DataBlobsTable $DataBlobs */
        $DataBlobs = TableRegistry::getTableLocator()->get('DataBlobs');


        if (!is_numeric($limit)) {
            $limit = 100;
        } else {
            $limit = intval($limit);
            $limit = min(100, $limit);
        }

        if (!is_numeric($page)) {
            $page = 1;
        } else {
            $page = intval($page);
        }

        if ($order && in_array(strtolower($order), ['d', 'desc', 'descending'])) {
            $order = 'desc';
        } elseif ($order && in_array(strtolower($order), ['a', 'asc', 'ascending'])) {
            $order = 'asc';
        } else {
            $order = 'desc';
        }

        $dataBlobs = $DataBlobs->find('all')
            ->select(['id', 'created', 'modified', 'activation', 'expiration', 'auto_delete', 'format', 'hash_sum'], true)
            ->limit($limit)
            ->page($page);

        if ($order === 'asc') {
            $dataBlobs = $dataBlobs->orderByAsc('id');
        } else {
            $dataBlobs = $dataBlobs->orderByDesc('id');
        }

        $recordCount = $DataBlobs->find('all')->select(['id'], true)->count();

        $responseData = [
            'total_records' => $recordCount,
            'total_pages' => ceil($recordCount / $limit),
            'page_limit' => $limit,
            'page' => $page,
            'data' => $dataBlobs->toArray()];
        $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

    public function info($id = null)
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

        /** @var DataBlobsTable $DataBlobs */
        $DataBlobs = TableRegistry::getTableLocator()->get('DataBlobs');

        if (!is_numeric($id)) {
            $id = 0;
        } else {
            $id = intval($id);
        }

        $dataBlob = $DataBlobs->find('all')->where(['id' => $id])->first();

        if ($dataBlob) {

            if ($dataBlob['format'] === 'application/json') {
                $dataBlob['blob'] = json_decode($dataBlob['blob'], true);
            } else {
                $dataBlob['blob'] = '';
            }
            $responseData = $dataBlob;


        } else {
            $responseData = [];
        }

        $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }


    /**
     * Save data to the DB
     *
     * @return \Cake\Http\Response|null|void
     */
    public function save(...$urlPath)
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

        /** @var DataBlobsTable $DataBlobs */
        $DataBlobs = TableRegistry::getTableLocator()->get('DataBlobs');

        $grouping = implode("/", $urlPath);

        $DataBlobs->saveDataBlob($this->request, $grouping);
        $alerts = $DataBlobs->getAllAlerts();

        if (!empty($alerts['danger'])) {
            $status = 'danger';
        } elseif (!empty($alerts['warning'])) {
            $status = 'warning';
        } elseif (!empty($alerts['info'])) {
            $status = 'info';
        } else {
            $status = 'success';
        }

        $responseData = ['status' => $status, 'alerts' => $DataBlobs->getAllAlertsLogSequence()];
        $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);

        return $this->response;
    }

}
