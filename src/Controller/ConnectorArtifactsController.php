<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Artifact;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\SeedsTable;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Exception;
use Laminas\Diactoros\UploadedFile;
use League\MimeTypeDetection\FinfoMimeTypeDetector;

/**
 * ConnectorArtifacts Controller
 * Allows anonymous Users to interact with Artifacts.
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
class ConnectorArtifactsController extends AppController
{
    use ReturnAlerts;

    protected Table|ArtifactsTable $Artifacts;

    /**
     * Initialize controller
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        $this->set('typeMap', $this->Artifacts->getSchema()->typeMap());

    }

    /**
     * @param EventInterface $event
     * @return Response|void|null
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        //prevent some actions from needing CSRF Token validation for AJAX requests
        //$this->FormProtection->setConfig('unlockedActions', ['csrf-token']);

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
        if (!$this->request->is(['patch', 'post', 'put', 'get', 'delete', 'ajax'])) {
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
            $this->addDangerAlerts(__('Missing Action'));
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
            $this->addDangerAlerts(__('Invalid Action'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

    }


    /**
     * Web interface submission of an Artifact from a mobile device
     *
     * @param null $seedToken
     * @param null $artifactToken
     * @return Response|void
     */
    public function mobileUpload($seedToken = null, $artifactToken = null)
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
            $this->viewBuilder()->setTemplate('mobile_upload_error');
            $header = "Oops!";
            $message = "Sorry, the link to upload is no longer valid.";
            $this->set('header', $header);
            $this->set('message', $message);
            return null;
        }

        $seed = $Seed->getSeed($seedToken);
        $this->set('seed', $seed);


        //display the interface to upload a file
        if ($this->request->isAll(['get'])) {
            $pushUrl = rtrim(Router::url("/", true), "/") . $seed->url;
            $this->set('pushUrl', $pushUrl);
            $this->set('reloadSeconds', $seed->getTTL());

            $this->viewBuilder()->setLayout('mobile-upload');
            $this->viewBuilder()->setTemplate('mobile_upload_device_transmitter');

            return;
        }

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
        if (!$this->Auth->user()) {
            $this->addDangerAlerts(__('Invalid User'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

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
        if (!$this->Auth->user()) {
            $this->addDangerAlerts(__('Invalid User'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

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

        $sampleSizeString = "sample_unc_{$size}";
        $imageLocation = $artifact->$sampleSizeString;
        if (!is_file($imageLocation)) {
            $this->Artifacts->createSampleSizes($artifact);

            $imgRes = $this->Artifacts->getImageResource();
            $this->response = $this->response->withType($imgRes->mime());
            $this->response = $this->response->withStringBody($imgRes->stream()->getContents());

            return $this->response;
        }

        $dataString = file_get_contents($imageLocation);
        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromBuffer($dataString);
        $this->response = $this->response->withType($mimeType);
        $this->response = $this->response->withStringBody($dataString);

        return $this->response;
    }


    /**
     * Fetch an image for a light-table.
     * If Artifact not found, return a default image to avoid 404 errors.
     *
     * @param null $token
     * @param null $pageNumber
     * @param null $namePlaceholder
     * @return Response
     */
    public function lightTable($token = null, $pageNumber = null, $namePlaceholder = null): Response
    {
        if (!$this->Auth->user()) {
            $this->addDangerAlerts(__('Invalid User'));
            $responseData = ['status' => $this->getHighestAlertLevel(), 'alerts' => $this->getAllAlertsLogSequence()];
            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);

            return $this->response;
        }

        /** @var Artifact $artifact */
        $artifact = $this->Artifacts->find('all')->where(['token' => $token])->first();

        if (!$artifact) {
            $imgRes = $this->Artifacts->getImageResource();
            $this->response = $this->response->withType($imgRes->mime());
            $this->response = $this->response->withStringBody($imgRes->stream()->getContents());

            return $this->response;
        }

        $lightTableUncs = $artifact->light_table_uncs;

        if (isset($lightTableUncs[$pageNumber]) && is_file($lightTableUncs[$pageNumber])) {
            $imageLocation = $lightTableUncs[$pageNumber];
        } else {
            $imageLocation = false;
        }

        if (!is_file($imageLocation)) {
            $this->Artifacts->createLightTableImagesErrand($artifact);

            //this could take a while so serve a default image
            $imgRes = $this->Artifacts->getImageResource();
            $this->response = $this->response->withType($imgRes->mime());
            $this->response = $this->response->withStringBody($imgRes->stream()->getContents());

            return $this->response;
        }

        $dataString = file_get_contents($imageLocation);
        $detector = new FinfoMimeTypeDetector();
        $mimeType = $detector->detectMimeTypeFromBuffer($dataString);
        $this->response = $this->response->withType($mimeType);
        $this->response = $this->response->withStringBody($dataString);

        return $this->response;
    }

}
