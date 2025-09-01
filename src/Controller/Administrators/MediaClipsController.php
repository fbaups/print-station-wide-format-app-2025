<?php
declare(strict_types=1);

namespace App\Controller\Administrators;

use App\Controller\AppController;
use App\Model\Entity\Artifact;
use App\Model\Table\ArtifactsTable;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Exception;
use Laminas\Diactoros\UploadedFile;

/**
 * MediaClips Controller
 *
 * @property \App\Model\Table\MediaClipsTable $MediaClips
 * @method \App\Model\Entity\MediaClip[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MediaClipsController extends AppController
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
        $this->set('typeMap', $this->MediaClips->getSchema()->typeMap());

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
                'rank',
                'artifact_link',
                'activation',
                'expiration',
                'auto_delete',
                'duration',
                'actions',
            ];

            $recordsTotal = $this->MediaClips->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->MediaClips->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $mediaClips = $this->MediaClips->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['MediaClips.id', 'MediaClips.rank', 'MediaClips.artifact_link', 'MediaClips.duration', 'MediaClips.trim_start', 'MediaClips.trim_end'],
                'text_fields' => ['MediaClips.name', 'MediaClips.description',],
            ];
            $mediaClips = $this->MediaClips->applyDatatablesQuickSearchFilter($mediaClips, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $mediaClips = $this->MediaClips->applyDatatablesColumnFilters($mediaClips, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $mediaClips->count());

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
                        $order['MediaClips.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $mediaClips = $this->paginate($mediaClips);
            $this->set(compact('mediaClips'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->MediaClips->getAllAlertsLogSequence());
            return;
        }

        $this->set('mediaClips', []);
        $this->set('isAjax', $isAjax);
    }

    /**
     * View method
     *
     * @param string|null $id Media Clip id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mediaClip = $this->MediaClips->get($id, [
            'contain' => [],
        ]);

        $artifact = $this->MediaClips->getArtifactBehindMediaClip($mediaClip);

        $this->set(compact('mediaClip', 'artifact'));

        $pdfMimeTypes = $this->MediaClips->getPdfMimeTypes();
        $imageMimeTypes = $this->MediaClips->getImageMimeTypes();
        $videoMimeTypes = $this->MediaClips->getVideoMimeTypes();
        $audioMimeTypes = $this->MediaClips->getAudioMimeTypes();

        $this->set(compact('pdfMimeTypes', 'imageMimeTypes', 'videoMimeTypes', 'audioMimeTypes'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mediaClip = $this->MediaClips->newEmptyEntity();

        if ($this->request->is('post')) {
            /** @var ArtifactsTable $Artifacts */
            $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

            $activation = (new DateTime())->second(0);
            $months = intval(Configure::read("Settings.repo_purge"));
            $expiration = (clone $activation)->addMonths($months)->second(0);
            $autoDelete = true;

            $files = $this->request->getData('files');

            /** @var UploadedFile[] $files */
            foreach ($files as $file) {
                /*
                 * Save the Artifact first
                 */
                $originalFilename = $file->getClientFilename();
                $checkedFilename = $Artifacts->sanitizeFilename($originalFilename);
                if ($originalFilename !== $checkedFilename) {
                    $this->Flash->info(__("NOTE: The file name was changed from '{$originalFilename}' to '{$checkedFilename}' due to URL encoding"));
                }

                $data = [
                    'blob' => $file->getStream()->getContents(),
                    'name' => $checkedFilename,
                    'description' => "Media Clip: [{$this->request->getData('name')}] [{$this->request->getData('description')}]",
                    'activation' => $activation,
                    'expiration' => $expiration,
                    'auto_delete' => $autoDelete,
                ];

                $isSafe = $Artifacts->isBlobDataSafe($data['blob']);
                if (!$isSafe) {
                    $this->Flash->error(__('The media clip could not be saved as it was not in an acceptable file format.'));

                    $alerts = $Artifacts->getAllAlerts();
                    $this->Flash->flashAllAlerts($alerts);

                    return $this->redirect(['action' => 'index']);
                }

                $artifact = $Artifacts->createArtifact($data);
                if ($artifact) {
                    //$this->Flash->success(__('Media clip file has been saved.'));
                } else {
                    $this->Flash->error(__('The media clip file could not be saved. Please, try again.'));
                    $errors = $Artifacts->getDangerAlerts();
                    $this->Flash->error(json_encode($errors, JSON_PRETTY_PRINT));

                    return $this->redirect(['action' => 'index']);
                }

                /*
                 * Save the Media Clip
                 */
                if (floatval($artifact->artifact_metadata->duration) > 0) {
                    $duration = floatval($artifact->artifact_metadata->duration);
                } else {
                    $duration = 20;
                }
                $mediaClip = $this->MediaClips->patchEntity($mediaClip, $this->request->getData());
                $mediaClip->artifact_link = $artifact->id;
                $mediaClip->activation = $activation;
                $mediaClip->expiration = $expiration;
                $mediaClip->auto_delete = $autoDelete;
                $mediaClip->trim_start = 0;
                $mediaClip->trim_end = 0;
                $mediaClip->duration = $duration;
                $mediaClip->fitting = 'fit';
                $mediaClip->muted = false;
                $mediaClip->loop = false;
                $mediaClip->autoplay = true;

                if ($this->MediaClips->save($mediaClip)) {
                    $this->Flash->success(__('The media clip has been saved. Please edit additional properties.'));

                    return $this->redirect(['action' => 'edit', $mediaClip->id]);
                }
                $this->Flash->error(__('The media clip could not be saved. Please, try again.'));

                return $this->redirect(['action' => 'index']);

            }
        }


        $this->set(compact('mediaClip'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Media Clip id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $mediaClip = $this->MediaClips->get($id, [
            'contain' => [],
        ]);

        /** @var Artifact $artifact */
        $artifact = $this->MediaClips->getArtifactBehindMediaClip($mediaClip);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $mediaClip = $this->MediaClips->patchEntity($mediaClip, $this->request->getData());

            //update the Artifact if certain Media Clip properties have changed
            if ($artifact) {
                $saveFlag = false;
                if ($mediaClip->isDirty('activation') || $mediaClip->isDirty('expiration') || $mediaClip->isDirty('auto_delete')) {
                    $saveFlag = true;
                    //find all Media Clips that reference this artifact
                    $query = $this->MediaClips->find();
                    $query = $query
                        ->select([
                            'min_activation' => 'MIN(activation)',
                            'max_expiration' => 'MAX(expiration)',
                            'auto_delete_false_count' => $query->func()->sum(
                                $query->newExpr()->add([
                                    'CASE WHEN auto_delete = 0 THEN 1 ELSE 0 END'
                                ])
                            )
                        ])
                        ->where(['artifact_link' => $artifact->id])
                        ->enableHydration(false);
                    $result = $query->first();

                    //compare the DB dates with the new requested date
                    $minActivation = max($result['min_activation'], $mediaClip->activation->toDateTimeString());
                    $maxExpiration = max($result['max_expiration'], $mediaClip->expiration->toDateTimeString());

                    //update the Artifact to match the found Media Clips
                    $artifact->activation = $minActivation;
                    $artifact->expiration = $maxExpiration;
                    $artifact->auto_delete = $result['auto_delete_false_count'] > 0 ? false : true; //if all referenced Media Clips are true, then set the Artifact to true
                }

                if ($mediaClip->isDirty('name') || $mediaClip->isDirty('description')) {
                    $saveFlag = true;
                    $artifact->description = "Media Clip: [{$this->request->getData('name')}] [{$this->request->getData('description')}]";
                }

                if ($saveFlag) {
                    /** @var ArtifactsTable $Artifacts */
                    $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
                    $Artifacts->save($artifact);
                }
            }

            if ($this->MediaClips->save($mediaClip)) {
                $this->Flash->success(__('The media clip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The media clip could not be saved. Please, try again.'));
        }

        $this->set(compact('mediaClip', 'artifact'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Media Clip id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mediaClip = $this->MediaClips->get($id);

        //check if other Media Clips are using the same Artifact
        $countMediaClipsUsingArtifact = $this->MediaClips->find('all')->select('id', true)->where(['artifact_link' => $mediaClip->artifact_link])->count();

        if ($this->MediaClips->delete($mediaClip)) {
            $this->Flash->success(__('The media clip has been deleted.'));
            if ($countMediaClipsUsingArtifact === 1) {
                /** @var ArtifactsTable $Artifacts */
                $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
                $Artifacts->deleteAll(['id' => $mediaClip->artifact_link]);
                $Artifacts->ArtifactMetadata->deleteAll(['artifact_id' => $mediaClip->artifact_link]);
            }
        } else {
            $this->Flash->error(__('The media clip could not be deleted. Please, try again.'));
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

        $recordData = $this->MediaClips->redactEntity($id, ['']);

        $artifact = $this->MediaClips->getArtifactBehindMediaClip($recordData);

        if (strtolower($format) === 'json') {
            $responseData = json_encode($recordData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        } else {
            $this->viewBuilder()->setLayout('ajax');
            $this->set(compact('recordData', 'artifact'));
        }

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        $pdfMimeTypes = $Artifacts->getPdfMimeTypes();
        $imageMimeTypes = $Artifacts->getImageMimeTypes();
        $videoMimeTypes = $Artifacts->getVideoMimeTypes();
        $audioMimeTypes = $Artifacts->getAudioMimeTypes();
        $this->set(compact('pdfMimeTypes', 'imageMimeTypes', 'videoMimeTypes', 'audioMimeTypes'));

    }

}
