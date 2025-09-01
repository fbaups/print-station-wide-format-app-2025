<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\OutputProcessor\EpsonPrintAutomateOutputProcessor;
use App\OutputProcessor\OutputProcessorBase;
use App\VendorIntegrations\Fujifilm\PressReady;
use Cake\Event\EventInterface;
use Exception;

/**
 * OutputProcessors Controller
 *
 * @property \App\Model\Table\OutputProcessorsTable $OutputProcessors
 * @method \App\Model\Entity\OutputProcessor[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OutputProcessorsController extends AppController
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
        $this->set('typeMap', $this->OutputProcessors->getSchema()->typeMap());

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

        $EOP = new EpsonPrintAutomateOutputProcessor();
        $epsonPresets = $EOP->getEpsonPresets();
        $epsonPresetsByUser = $EOP->getEpsonPresetsByUser();

        if ($this->request->is('ajax')) {
            //DataTables POSTed the data as a querystring, parse and assign to $datatablesQuery
            parse_str($this->request->getBody()->getContents(), $datatablesQuery);

            //$headers must match the View
            $headers = [
                'id',
                'type',
                'name',
                'description',
                'is_enabled',
                'parameters',
                'actions',
            ];

            $recordsTotal = $this->OutputProcessors->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->OutputProcessors->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $outputProcessors = $this->OutputProcessors->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                //'numeric_fields' => ['OutputProcessors.id', 'OutputProcessors.rank', 'OutputProcessors.priority'],
                'text_fields' => ['OutputProcessors.name', 'OutputProcessors.description'],
            ];
            $outputProcessors = $this->OutputProcessors->applyDatatablesQuickSearchFilter($outputProcessors, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $outputProcessors = $this->OutputProcessors->applyDatatablesColumnFilters($outputProcessors, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $outputProcessors->count());

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
                        $order['OutputProcessors.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $outputProcessors = $this->paginate($outputProcessors);
            $this->set(compact('outputProcessors'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->OutputProcessors->getAllAlertsLogSequence());
            return;
        }

        $this->set('outputProcessors', []);
        $this->set('isAjax', $isAjax);

        $servicesStats = $this->BackgroundServicesAssistant->_getServicesStats();
        $this->set('servicesStats', $servicesStats);
    }

    /**
     * View method
     *
     * @param string|null $id Output Processor id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $outputProcessor = $this->OutputProcessors->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('outputProcessor'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $OP = new OutputProcessorBase();
        $outputProcessorTypes = $OP->getOutputProcessorTypes();

        $outputProcessor = $this->OutputProcessors->newEmptyEntity();
        $outputProcessor->parameters = $outputProcessor->parameters ?? [];
        $outputProcessor->parameters = array_merge($OP->getDefaultOutputConfiguration(), $outputProcessor->parameters);

        $this->set(compact('outputProcessor', 'outputProcessorTypes'));

        $EOP = new EpsonPrintAutomateOutputProcessor();
        $epsonPresets = $EOP->getEpsonPresets();
        $epsonPresetsByUser = $EOP->getEpsonPresetsByUser();
        $this->set('epsonPresets', $epsonPresets);
        $this->set('epsonPresetsByUser', $epsonPresetsByUser);

        $PressReady = new PressReady();
        $pressReadyPdfHotFolders = $PressReady->getPdfHotFolders();
        $pressReadyPdfHotFolderOptions = $PressReady->getPdfHotFoldersOptionsList();
        $pressReadyCsvHotFolders = $PressReady->getCsvHotFolders();
        $pressReadyCsvHotFolderOptions = $PressReady->getCsvHotFoldersOptionsList();
        $pressReadyCsvCompiledHotFolders = $PressReady->compileCsvHotFolders();
        $this->set('pressReadyPdfHotFolders', $pressReadyPdfHotFolders);
        $this->set('pressReadyCsvHotFolders', $pressReadyCsvHotFolders);
        $this->set('pressReadyCsvCompiledHotFolders', $pressReadyCsvCompiledHotFolders);
        $this->set('pressReadyPdfHotFolderOptionsList', $pressReadyPdfHotFolderOptions);
        $this->set('pressReadyCsvHotFolderOptionsList', $pressReadyCsvHotFolderOptions);

        if ($this->request->is('post')) {
            $outputProcessor->parameters = $this->OutputProcessors->formatParameters($this->request->getData(), $outputProcessor->parameters);

            //Folder validation checks
            if ($this->request->getData('type') === 'Folder') {
                if (strlen($outputProcessor->parameters['fso_path']) <= 4) {
                    $this->Flash->error(__('Please provide a valid Folder Path'));
                    return;
                }
            }

            //sFTP validation checks
            if ($this->request->getData('type') === 'sFTP') {

            }

            //EpsonPrintAutomate validation checks
            if ($this->request->getData('type') === 'EpsonPrintAutomate') {

            }

            $outputProcessor = $this->OutputProcessors->patchEntity($outputProcessor, $this->request->getData());
            if ($this->OutputProcessors->save($outputProcessor)) {
                $this->Flash->success(__('The output processor has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The output processor could not be saved. Please, try again.'));
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Output Processor id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $OP = new OutputProcessorBase();
        $outputProcessorTypes = $OP->getOutputProcessorTypes();

        $outputProcessor = $this->OutputProcessors->get($id);
        $outputProcessor->parameters = $outputProcessor->parameters ?? [];
        $outputProcessor->parameters = array_merge($OP->getDefaultOutputConfiguration(), $outputProcessor->parameters);

        $this->set(compact('outputProcessor', 'outputProcessorTypes'));

        $EOP = new EpsonPrintAutomateOutputProcessor();
        $epsonPresets = $EOP->getEpsonPresets();
        $epsonPresetsByUser = $EOP->getEpsonPresetsByUser();
        $this->set('epsonPresets', $epsonPresets);
        $this->set('epsonPresetsByUser', $epsonPresetsByUser);

        $PressReady = new PressReady();
        $pressReadyPdfHotFolders = $PressReady->getPdfHotFolders();
        $pressReadyPdfHotFolderOptions = $PressReady->getPdfHotFoldersOptionsList();
        $pressReadyCsvHotFolders = $PressReady->getCsvHotFolders();
        $pressReadyCsvHotFolderOptions = $PressReady->getCsvHotFoldersOptionsList();
        $pressReadyCsvCompiledHotFolders = $PressReady->compileCsvHotFolders();
        $this->set('pressReadyPdfHotFolders', $pressReadyPdfHotFolders);
        $this->set('pressReadyCsvHotFolders', $pressReadyCsvHotFolders);
        $this->set('pressReadyCsvCompiledHotFolders', $pressReadyCsvCompiledHotFolders);
        $this->set('pressReadyPdfHotFolderOptionsList', $pressReadyPdfHotFolderOptions);
        $this->set('pressReadyCsvHotFolderOptionsList', $pressReadyCsvHotFolderOptions);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $outputProcessor->parameters = $this->OutputProcessors->formatParameters($this->request->getData(), $outputProcessor->parameters);

            //Folder validation checks
            if ($this->request->getData('type') === 'Folder') {
                if (strlen($outputProcessor->parameters['fso_path']) <= 4) {
                    $this->Flash->error(__('Please provide a valid Folder Path'));
                    return;
                }
            }

            //sFTP validation checks
            if ($this->request->getData('type') === 'sFTP') {

            }

            //EpsonPrintAutomate validation checks
            if ($this->request->getData('type') === 'EpsonPrintAutomate') {

            }

            $outputProcessor = $this->OutputProcessors->patchEntity($outputProcessor, $this->request->getData());
            if ($this->OutputProcessors->save($outputProcessor)) {
                $this->Flash->success(__('The output processor has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The output processor could not be saved. Please, try again.'));
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Output Processor id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $outputProcessor = $this->OutputProcessors->get($id);
        if ($this->OutputProcessors->delete($outputProcessor)) {
            $this->Flash->success(__('The output processor has been deleted.'));
        } else {
            $this->Flash->error(__('The output processor could not be deleted. Please, try again.'));
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

        $recordData = $this->OutputProcessors->redactEntity($id, ['']);

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
