<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Artifact;
use App\Model\Table\SeedsTable;
use App\Model\Table\SettingsTable;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Exception;
use Intervention\Image\ImageManager;
use Laminas\Diactoros\UploadedFile;
use function PHPUnit\Framework\isFinite;
use function PHPUnit\Framework\isNan;
use function PHPUnit\Framework\isNull;

/**
 * Artifacts Controller
 *
 * @property \App\Model\Table\ArtifactsTable $Artifacts
 * @method \App\Model\Entity\Artifact[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ArtifactsController extends AppController
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
        $this->set('typeMap', $this->Artifacts->getSchema()->typeMap());

    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        $this->FormProtection->setConfig('unlockedActions', ['mobile-tx']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

    }

    /**
     * Index method
     *
     * @return Response|null|void Renders view
     */
    public function index()
    {
        $isAjax = false;

        if ($this->request->is('ajax')) {
            $datatablesQuery = $this->request->getQuery();

            //$headers must match the View
            $headers = [
                'id',
                'name',
                'description',
                'size',
                'mime_type',
                'activation',
                'expiration',
                'auto_delete',
                'actions',
            ];

            $recordsTotal = $this->Artifacts->find('all')
                ->select(['id'], true)
                ->count();
            $this->set('recordsTotal', $recordsTotal);

            //set some JSON fields back to STRING type for searching
            //$this->Artifacts->convertJsonFieldsToString('[some-col-name]');

            //create a Query
            $artifacts = $this->Artifacts->find('all');

            //apply quick search filter
            $quickFilterOptions = [
                'numeric_fields' => ['Artifacts.id', 'Artifacts.size'],
                'text_fields' => ['Artifacts.name', 'Artifacts.description', 'Artifacts.token'],
            ];
            $artifacts = $this->Artifacts->applyDatatablesQuickSearchFilter($artifacts, $datatablesQuery, $quickFilterOptions);

            //apply column filters
            $artifacts = $this->Artifacts->applyDatatablesColumnFilters($artifacts, $datatablesQuery, $headers);

            //final filtered count
            $this->set('recordsFiltered', $artifacts->count());

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
                        $order['Artifacts.' . $orderBy] = $orderDirection;
                    }
                }
            }

            $this->paginate = [
                'limit' => $datatablesQuery['length'],
                'page' => intval(($datatablesQuery['start'] / $datatablesQuery['length']) + 1),
                'order' => $order,
            ];
            $artifacts = $this->paginate($artifacts);
            $this->set(compact('artifacts'));
            $this->set('isAjax', $isAjax);
            $this->set('message', $this->Artifacts->getAllAlertsLogSequence());

            //dd($artifacts->first());
            return;
        }

        $this->set('artifacts', []);
        $this->set('isAjax', $isAjax);

        /** @var SettingsTable $Settings */
        $Settings = TableRegistry::getTableLocator()->get('Settings');
        $repoCheckResult = $Settings->checkRepositoryDetails();
        $this->set('repoCheckResult', $repoCheckResult);
    }

    /**
     * View method
     *
     * @param string|null $id Artifact id.
     * @return Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $artifact = $this->Artifacts->get($id, contain: ['ArtifactMetadata']);
        $pdfMimeTypes = $this->Artifacts->getPdfMimeTypes();
        $imageMimeTypes = $this->Artifacts->getImageMimeTypes();

        $this->Artifacts->populateEmptyArtifactMetadataExif($artifact);

        $this->set(compact('artifact', 'pdfMimeTypes', 'imageMimeTypes'));
    }

    /**
     * Add method
     *
     * @return Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $artifact = $this->Artifacts->newEmptyEntity();
        if ($this->request->is('post')) {

            $files = $this->request->getData('files');

            /** @var UploadedFile[] $files */
            foreach ($files as $file) {
                $originalFilename = $file->getClientFilename();
                $checkedFilename = $this->Artifacts->sanitizeFilename($originalFilename);
                if ($originalFilename !== $checkedFilename) {
                    $this->Flash->info(__("NOTE: The file name was changed from '{$originalFilename}' to '{$checkedFilename}' due to URL encoding"));
                }

                $data = [
                    'blob' => $file->getStream()->getContents(),
                    'name' => $checkedFilename,
                    'description' => $this->request->getData('description'),
                    'activation' => $this->request->getData('activation'),
                    'expiration' => $this->request->getData('expiration'),
                    'auto_delete' => $this->request->getData('auto_delete'),
                ];

                $isSafe = $this->Artifacts->isBlobDataSafe($data['blob']);
                if (!$isSafe) {
                    $this->Flash->error(__('The artifact could not be saved as it was not in an acceptable file format.'));

                    $alerts = $this->Artifacts->getAllAlerts();
                    $this->Flash->flashAllAlerts($alerts);

                    return $this->redirect(['action' => 'index']);
                }

                $result = $this->Artifacts->createArtifact($data);
                if ($result) {
                    $this->Flash->success(__('The artifact has been saved.'));
                } else {
                    $this->Flash->error(__('The artifact could not be saved. Please, try again.'));
                    $errors = $this->Artifacts->getDangerAlerts();
                    $this->Flash->error(json_encode($errors, JSON_PRETTY_PRINT));
                }
            }

            return $this->redirect(['action' => 'index']);
        }
        $this->set(compact('artifact'));
        $this->set('_serialize', ['artifact']);

    }

    /**
     * Edit method
     *
     * @param string|null $id Artifact id.
     * @return Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $artifact = $this->Artifacts->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            //name cannot be changed
            unset($data['name']);
            $artifact = $this->Artifacts->patchEntity($artifact, $data);
            if ($this->Artifacts->save($artifact)) {
                $this->Flash->success(__('The artifact has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The artifact could not be saved. Please, try again.'));
        }
        $this->set(compact('artifact'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Artifact id.
     * @return Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $artifact = $this->Artifacts->get($id);
        if ($this->Artifacts->delete($artifact)) {
            $this->Flash->success(__('The artifact has been deleted.'));
        } else {
            $this->Flash->error(__('The artifact could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Preview method
     *
     * @param string|null $id Foo Author id.
     * @return Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function preview($id = null, $format = 'json')
    {
        if (!$this->request->is(['ajax', 'get'])) {
            $responseData = json_encode(false, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
        }

        $recordData = $this->Artifacts->redactEntity($id, ['']);

        if (strtolower($format) === 'json') {
            $responseData = json_encode($recordData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        } else {
            $this->viewBuilder()->setLayout('ajax');
            $this->set(compact('recordData'));
        }

    }

    /**
     * Fetch an Artifact from the Repository.
     * If Artifact not found, return a default image to avoid 404 errors.
     *
     * @param null $token
     * @param null $namePlaceholder
     * @return Response
     */
    public function fetch($token = null, $namePlaceholder = null): Response
    {
        /** @var Artifact $artifact */
        $artifact = $this->Artifacts->find('all')->where(['token' => $token])->first();

        if (!$artifact) {
            $imgRes = $this->Artifacts->getImageResource();
            $this->response = $this->response->withType($imgRes->mime());
            $this->response = $this->response->withStringBody($imgRes->stream()->getContents());

            return $this->response;
        }

        $dataString = file_get_contents($artifact->full_unc);
        $this->response = $this->response->withType($artifact->mime_type);
        $this->response = $this->response->withStringBody($dataString);

        return $this->response;
    }

    /**
     * Fetch an Artifact meta information from the Repository.
     * Returns a JSON object {}
     *
     * @param null $token
     * @param null $namePlaceholder
     * @return Response
     */
    public function metaToken($token = null, $namePlaceholder = null): Response
    {
        if ($this->request->isAll(['ajax', 'get']) && $token) {
            $fields = [
                'name',
                'description',
                'size',
                'mime_type',
                'activation',
                'expiration',
                'token',
                'hash_sum',
                'url',
            ];
            $artifacts = $this->Artifacts->findByToken($token)
                ->select($fields, true);

            $artifactsCleaned = [];
            /** @var Artifact $artifact */
            foreach ($artifacts as $artifact) {
                $artifactsCleaned = $artifact->toArray();
                $artifactsCleaned['full_url'] = $artifact->full_url;
                unset($artifactsCleaned['url']);
            }
            if ($artifactsCleaned) {
                $result = json_encode($artifactsCleaned, JSON_PRETTY_PRINT);
            } else {
                $result = '{}';
            }
        } else {
            $result = '{}';
        }

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($result);
        return $this->response;
    }

    /**
     * Fetch an Artifact meta information from the Repository.
     * Returns a JSON array of objects [{},{},{}]
     *
     * @param null $token
     * @param null $namePlaceholder
     * @return Response
     */
    public function metaGroup($token = null, $namePlaceholder = null): Response
    {
        if ($this->request->isAll(['ajax', 'get']) && $token) {
            $fields = [
                'name',
                'description',
                'size',
                'mime_type',
                'activation',
                'expiration',
                'token',
                'hash_sum',
                'url',
            ];
            $artifacts = $this->Artifacts->findByGrouping($token)
                ->select($fields, true);

            $artifactsCleaned = [];
            /** @var Artifact $artifact */
            foreach ($artifacts as $artifact) {
                $artifactsCleanedTmp = $artifact->toArray();
                $artifactsCleanedTmp['full_url'] = $artifact->full_url;
                unset($artifactsCleanedTmp['url']);
                $artifactsCleaned[] = $artifactsCleanedTmp;
            }
            $result = json_encode($artifactsCleaned, JSON_PRETTY_PRINT);
        } else {
            $result = '[]';
        }

        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($result);
        return $this->response;
    }


    /**
     * Fetch a down-sampled image of the Artifact from the Repository.
     * For PDF files, an image of the first page is returned.
     * If Artifact not found, return a default image to avoid 404 errors.
     *
     * @param null $token
     * @param null $size
     * @param null $namePlaceholder
     * @return Response
     */
    public function sample($token = null, $size = 'thumbnail', $namePlaceholder = null): Response
    {
        /** @var Artifact $artifact */
        $artifact = $this->Artifacts->find('all')->where(['token' => $token])->first();

        if (!$artifact) {
            $imgRes = $this->Artifacts->getImageResource();
            $this->response = $this->response->withType($imgRes->mime());
            $this->response = $this->response->withStringBody($imgRes->stream()->getContents());

            return $this->response;
        }

        $allowedSizes = [
            'icon',
            'thumbnail',
            'preview',
            'lr',
            'mr',
            'hr',
        ];

        if (!in_array($size, $allowedSizes)) {
            $size = 'thumbnail';
        }

        $fileParts = pathinfo($artifact->full_unc);

        $imageLocation = "{$fileParts['dirname']}/samples/{$fileParts['filename']}_$size.{$fileParts['extension']}";

        if (!is_file($imageLocation)) {
            $this->Artifacts->createSampleSizes($artifact);
        }

        $dataString = file_get_contents($imageLocation);
        $this->response = $this->response->withType($artifact->mime_type);
        $this->response = $this->response->withStringBody($dataString);

        return $this->response;
    }


    /**
     * Receiver for mobile upload
     *
     * @param $seedToken
     * @return Response|void
     */
    public function mobileRx($seedToken = null)
    {
        if ($this->request->isAll(['get'])) {
            $this->viewBuilder()->setTemplate('mobile_rx_options');
        }

        if ($this->request->isAll(['post'])) {
            $maxTime = $this->request->getData('max_time');
            $bidLimit = $this->request->getData('max_uploads');

            /** @var SeedsTable $Seeds */
            $Seeds = TableRegistry::getTableLocator()->get('seeds');
            $url = '/artifacts/mobile-tx';
            $options = [
                'activation' => new DateTime(),
                'expiration' => new DateTime("+ {$maxTime} minutes"),
                'url' => $url,
                'bids' => 0,
                'bid_limit' => $bidLimit,
                'user_link' => 0,
            ];
            $seed = $Seeds->createSeed($options);

            $pushUrl = rtrim(Router::url("/", true), "/") . "$url/$seed->token/";

            $this->set('pushUrl', $pushUrl);
            $this->set('seed', $seed);
            $this->set('reloadSeconds', $seed->getTTL());
        }
    }

    public function mobileTx($seedToken = null, $artifactToken = null)
    {
        if (is_null($seedToken)) {
            return $this->redirect('/');
        }

        if ($artifactToken) {
            $artifactToken = sha1($artifactToken);
        }

        /** @var SeedsTable $Seed */
        $Seed = TableRegistry::getTableLocator()->get('Seeds');
        $isTokenValid = $Seed->validateSeed($seedToken);

        if (!$isTokenValid) {
            $this->viewBuilder()->setLayout('error');
            $this->viewBuilder()->setTemplate('mobile-tx-error');
            $header = "Oops!";
            $message = "Sorry, the link to upload is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $Seed->getSeed($seedToken);
        $this->set('seed', $seed);

        //uploading an Artifact
        if ($this->request->isAll(['ajax', 'post'])) {
            if ($this->request->getData('action') === 'upload') {
                /** @var UploadedFile $uploadFile */
                $uploadFile = ($this->request->getData('file'));
                $uuid = $this->request->getData('uuid');
                if (Security::isValidUuidOrSha1($uuid)) {
                    $uuid = sha1($uuid);
                } else {
                    $uuid = sha1(Security::randomBytes(2048));
                }

                $data = [
                    'blob' => $uploadFile->getStream()->getContents(),
                    'name' => $uploadFile->getClientFilename(),
                    'size' => $uploadFile->getSize(),
                    'token' => $uuid,
                    'grouping' => $seedToken,
                ];
                $result = $this->Artifacts->createArtifact($data);
                $result = json_encode($result, JSON_PRETTY_PRINT);

                $this->response = $this->response->withType('json');
                $this->response = $this->response->withStringBody($result);

                return $this->response;
            }
        }

        //deleting an uploaded Artifact
        if ($this->request->isAll(['ajax', 'post'])) {
            if ($this->request->getData('action') === 'delete') {
                $artifact = $this->Artifacts->findByToken($artifactToken)->first();

                if ($artifact) {
                    $result = $this->Artifacts->delete($artifact);
                } else {
                    $result = false;
                }
                $result = json_encode($result, JSON_PRETTY_PRINT);

                $this->response = $this->response->withType('json');
                $this->response = $this->response->withStringBody($result);

                return $this->response;
            }
        }

        $this->viewBuilder()->setLayout('mobile-upload');
    }

}
