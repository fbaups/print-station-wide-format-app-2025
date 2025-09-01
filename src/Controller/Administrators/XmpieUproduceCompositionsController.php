<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\ErrandsTable;
use App\Model\Table\InternalOptionsTable;
use App\VendorIntegrations\XMPie\uProduceCompositionMaker;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;
use Exception;
use Laminas\Diactoros\UploadedFile;

/**
 * XmpieUproduceCompositions Controller
 *
 * @property \App\Model\Table\XmpieUproduceCompositionsTable $XmpieUproduceCompositions
 * @method \App\Model\Entity\XmpieUproduceComposition[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class XmpieUproduceCompositionsController extends AppController
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
        $this->set('typeMap', $this->XmpieUproduceCompositions->getSchema()->typeMap());

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
        $this->FormProtection->setConfig('unlockedActions', ['index']); //allow index for DataTables index refresh

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
        $isAjax = false;

        if ($this->request->is('ajax')) {
            //DataTables POSTed the data as a querystring, parse and assign to $datatablesQuery
            parse_str($this->request->getBody()->getContents(), $datatablesQuery);

            //$headers must match the View
            $headers = [
                'id',
                'name',
                'description',
                'errand_link',
                'artifact_link',
                'actions',
            ];

            $recordsTotal = $this->XmpieUproduceCompositions->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->XmpieUproduceCompositions->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $xmpieUproduceCompositions = $this->XmpieUproduceCompositions->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['XmpieUproduceCompositions.errand_link', 'XmpieUproduceCompositions.artifact_link'],
                'text_fields' => ['XmpieUproduceCompositions.name', 'XmpieUproduceCompositions.description', 'XmpieUproduceCompositions.guid'],
            ];
            $xmpieUproduceCompositions = $this->XmpieUproduceCompositions->applyDatatablesQuickSearchFilter($xmpieUproduceCompositions, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $xmpieUproduceCompositions = $this->XmpieUproduceCompositions->applyDatatablesColumnFilters($xmpieUproduceCompositions, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $xmpieUproduceCompositions->count());

            $this->viewBuilder()->setLayout('ajax');
            $this->response = $this->response->withType('json');
            $isAjax = true;
            $this->set('datatablesQuery', $datatablesQuery);

            $order = [];
            if (isset($datatablesQuery['order']) && is_array($datatablesQuery['order'])) {
                foreach ($datatablesQuery['order'] as $item) {
                    if (isset($headers[$item['column']])) {
                        $orderBy = $headers[$item['column']];
                        $orderDirection = $item['dir'];
                        $order['XmpieUproduceCompositions.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $xmpieUproduceCompositions = $this->paginate($xmpieUproduceCompositions);
            $this->set(compact('xmpieUproduceCompositions'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->XmpieUproduceCompositions->getAllAlertsLogSequence());
            return;
        }

        $this->set('xmpieUproduceCompositions', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Xmpie Uproduce Composition id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $xmpieUproduceComposition = $this->XmpieUproduceCompositions->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('xmpieUproduceComposition'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $Session = $this->request->getSession();
        $xmpCredentialsCount = ($Session->read('IntegrationCredentials.XMPie-uProduce.count'));
        if ($xmpCredentialsCount == 0) {
            $this->Flash->error(__('Sorry, could not find a XMPie uProduce Server.'));
            return $this->redirect(['action' => 'index']);
        }

        $xmpieUproduceComposition = $this->XmpieUproduceCompositions->newEmptyEntity();
        if ($this->request->is('post')) {

            $requestData = $this->request->getData();
            $requestData['guid'] = Security::guid();

            //save the trigger file as an Artifact
            $files = $this->request->getUploadedFiles();
            $artifact = false;
            if (isset($files['trigger_file'])) {
                $file = $files['trigger_file'];
                if ($file instanceof UploadedFile) {
                    /** @var ArtifactsTable $Artifacts */
                    $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
                    $data['activation'] = $requestData['activation'] ?? null;
                    $data['expiration'] = $requestData['expiration'] ?? null;
                    $data['auto_delete'] = $requestData['auto_delete'] ?? false;
                    $artifact = $Artifacts->createArtifactFromUploadedFile($file, $data);
                    if (!$artifact) {
                        $this->Flash->error(__('Save Error'));
                        $this->Flash->error(json_encode($Artifacts->getAllAlertsLogSequence()));

                        return $this->redirect(['action' => 'index']);
                    }
                    $requestData['artifact_link'] = $artifact->id;
                    $requestData['integration_credential_link'] =  $requestData['integration_credential_id'];
                }
            }

            if (!$artifact) {
                $this->Flash->error(__('Error saving the trigger file. Please, try again.'));
                return $this->redirect(['action' => 'index']);
            }

            //save the data
            $xmpieUproduceComposition = $this->XmpieUproduceCompositions->patchEntity($xmpieUproduceComposition, $requestData);
            if ($this->XmpieUproduceCompositions->save($xmpieUproduceComposition)) {
                $this->Flash->success(__('The XMPie uProduce Composition has been saved.'));
            }

            if (!$xmpieUproduceComposition) {
                $this->Flash->error(__('The XMPie uProduce Composition could not be saved. Please, try again.'));
                return $this->redirect(['action' => 'index']);
            }

            //create the Errand
            /** @var ErrandsTable $Errands */
            $Errands = TableRegistry::getTableLocator()->get('Errands');
            $errandName = substr("XMPie uProduce Composition [ID: $xmpieUproduceComposition->id] [Name: $xmpieUproduceComposition->name]", 0, 128);
            $options = [
                'name' => $errandName,
                'class' => '\\App\\VendorIntegrations\\XMPie\\uProduceCompositionMaker',
                'method' => 'composeFromRecord',
                'parameters' => [
                    'xmpieUproduceCompositionId' => $xmpieUproduceComposition->id,
                ],
            ];
            $errand = $Errands->createErrand($options);

            if (!$errand) {
                $this->Flash->error(__('Could not create an Errand to run the XMPie uProduce Composition. Please, try again.'));
                return $this->redirect(['action' => 'index']);
            }

            //update the XmpieUproduceComposition record with the Errand ID
            $xmpieUproduceComposition->errand_link = $errand->id;
            $this->XmpieUproduceCompositions->save($xmpieUproduceComposition);

            $this->Flash->success(__('XMPie uProduce will be triggered to produce a composition.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('xmpieUproduceComposition'));

        /** @var InternalOptionsTable $IC */
        $IC = \Cake\ORM\TableRegistry::getTableLocator()->get('IntegrationCredentials');
        $integrationCredentials = $IC->find('all')->where(['type' => 'XMPie-uProduce', 'is_enabled' => true]);
        $this->set(compact('integrationCredentials'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Xmpie Uproduce Composition id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $xmpieUproduceComposition = $this->XmpieUproduceCompositions->get($id);
        if ($this->XmpieUproduceCompositions->delete($xmpieUproduceComposition)) {
            $this->Flash->success(__('The XMPie uProduce Composition has been deleted.'));
        } else {
            $this->Flash->error(__('The XMPie uProduce Composition could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Preview method
     *
     * @param string|null $id Foo Author id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function preview($id = null, $format = 'json')
    {
        if (!$this->request->is(['ajax', 'get'])) {
            $responseData = json_encode(false, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
        }

        $recordData = $this->XmpieUproduceCompositions->redactEntity($id, ['']);

        if (strtolower($format) === 'json') {
            $responseData = json_encode($recordData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        } else {
            $this->viewBuilder()->setLayout('ajax');
            $this->viewBuilder()->setTemplatePath('DataTablesPreviewer');
            $this->set(compact('recordData'));
        }

    }

}
