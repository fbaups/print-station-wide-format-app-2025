<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Table\ArtifactsTable;
use App\Model\Table\DataBlobsTable;
use App\Model\Table\MediaClipsTable;
use App\Model\Table\SeedsTable;
use App\Model\Table\TheatrePinsTable;
use App\Utility\Feedback\ReturnAlerts;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;
use TinyAuth\Controller\Component\AuthUserComponent;

/**
 * Theatre Controller
 *
 * A way to serve Media Clips to anonymous Users via a PIN. Users enter a PIN to view content.
 *
 * Authenticated users (i.e. $this->Auth->user()) don't need to
 * enter a pin as the 'PIN.state' will be automatically flagged as TRUE
 */
class TheatreController extends AppController
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
        //$this->FormProtection->setConfig('unlockedActions', ['index']);

        //prevent all actions from needing CSRF Token validation for AJAX requests
        if ($this->request->is('ajax')) {
            $this->FormProtection->setConfig('validate', false);
        }

        //auto-set the PIN.state if the user is Authenticated
        if ($this->Authentication->getIdentity()) {
            $this->request->getSession()->write('PIN.state', true);
        }

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


        //check that the requested action exists
        try {
            $isAction = $this->isAction($action);
        } catch (\Throwable $exception) {
            $isAction = false;
        }

        if (!$isAction) {
            return $this->redirect(['action' => 'index']);
        }

    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        if (!$this->request->getSession()->read('PIN.state')) {
            $this->viewBuilder()->setLayout('theatre-pin');
            $this->viewBuilder()->setTemplate('index_pin');
            return null;
        }

        $this->viewBuilder()->setLayout('theatre');

        return null;
    }

    /**
     * Respond to AJAX calls to validate a PinCode
     *
     * @return Response|null
     */
    public function pin()
    {
        if ($this->request->is('post')) {
            $pin = $this->request->getData('pin');

            /** @var TheatrePinsTable $TheatrePins */
            $TheatrePins = TableRegistry::getTableLocator()->get('TheatrePins');
            $pinCode = $TheatrePins->validatePinCode($pin);
            if ($pinCode) {
                $this->request->getSession()->write('PIN.state', true);
                $responseData = true;
            } else {
                $responseData = false;
            }

            $responseData = json_encode($responseData, JSON_PRETTY_PRINT);
            $this->response = $this->response->withType('json');
            $this->response = $this->response->withStringBody($responseData);
            return $this->response;
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * @return \Cake\Http\Response|null|void
     */
    public function mediaClipPlayer()
    {
        if (!$this->request->getSession()->read('PIN.state')) {
            return $this->redirect(['action' => 'index']);
        }

        $this->viewBuilder()->setLayout('theatre');

        /** @var MediaClipsTable $MediaClips */
        $MediaClips = TableRegistry::getTableLocator()->get('MediaClips');

        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        $mediaClips = $MediaClips->find('all')->orderBy(['rank', 'name'])->limit(100);
        $this->set('mediaClips', $mediaClips);

        $artifactIds = (clone $mediaClips)->select(['artifact_link'], true);
        $artifacts = $Artifacts->find('all')->where(['id IN' => $artifactIds]);
        $this->set('artifacts', $artifacts);
    }

    /**
     * @return \Cake\Http\Response|null|void
     */
    public function slideshowPlayer()
    {
        if (!$this->request->getSession()->read('PIN.state')) {
            return $this->redirect(['action' => 'index']);
        }

        $this->viewBuilder()->setLayout('theatre');


    }

    /**
     * TODO this has not been completed as it need to be re-architected from the AutomationSlideshow project
     *
     * @param ...$splat
     * @return Response|null
     */
    public function slideshowPlayerSequence(...$splat)
    {
        if (!$this->request->getSession()->read('PIN.state')) {
            return $this->redirect(['action' => 'index']);
        }

        $errorSeconds = 0; //emulate server and local time difference of X seconds (i.e. clock wrong on local computer)

        $now = (new \App\Utility\DateTime\DateTime())->subSeconds($errorSeconds);

        $data = [
            'created' => $now->toIso8601String(),
            'created_ts' => $now->toUnixMilliseconds(),
            'clip_count' => 0,
            'checksum' => '<calculated>',
            'tracks' => [
                '01' => [],
                '02' => [],
                '03' => [],
                '04' => [],
                '05' => [],
            ]
        ];

        //checksum for each clip
        $clipCount = 0;
        foreach ($data['tracks'] as $kt => $track) {
            $kt = strval($kt);
            foreach ($track as $km => $mediaClip) {
                $data['tracks'][$kt][$km]['id'] = sha1(json_encode($mediaClip));
                $clipCount++;
            }
        }
        $data['clip_count'] = $clipCount;

        //checksum for all tracks
        $data['checksum'] = sha1(json_encode($data['tracks']));

        $responseData = json_encode($data, JSON_PRETTY_PRINT);
        $this->response = $this->response->withType('json');
        $this->response = $this->response->withStringBody($responseData);
        return $this->response;


        /*
         *
Your input 	Epoch date 	Converted epoch
2025-05-30T02:40:00+00:00	1748572800	Fri, 30 May 2025 02:40:00 +0000
2025-05-30T02:40:10+00:00	1748572810	Fri, 30 May 2025 02:40:10 +0000
2025-05-30T02:40:20+00:00	1748572820	Fri, 30 May 2025 02:40:20 +0000
2025-05-30T02:40:30+00:00	1748572830	Fri, 30 May 2025 02:40:30 +0000

{
    "created": "2025-05-30T02:46:59+00:00",
    "created_ts": 1748573219524,
    "clip_count": 1,
    "checksum": "156bcdd8cb89888d39534ab7832c3577c2db0b00",
    "tracks": {
        "01": [
        {
                "id": "58c704a5adfc404d06c88c3848ff6eb0f48b1ed0",
                "format": "video",
                "duration": 317.115,
                "source": "https:\/\/example.com.au\/video-a.mp4",
                "start": 1748572800000,
                "stop": 1748572810000,
                "start_ts": "2025-05-30T02:40:00+00:00",
                "stop_ts": "2025-05-30T02:40:10+00:00",
                "fitting": "fit",
                "muted": false,
                "loop": false,
                "autoplay": true,
                "class": "",
                "attributes": ""
            }
        ],
        "02": [],
        "03": [],
        "04": [],
        "05": [
            {
                "id": "02a3532a66f53f75b06ecb0c703e342a9497e02a",
                "format": "image",
                "duration": 317.115,
                "source": "https:\/\/example.com.au\/image-1.jpg",
                "start": 1748572810000,
                "stop": 1748572820000,
                "start_ts": "2025-05-30T02:40:10+00:00",
                "stop_ts": "2025-05-30T02:40:20+00:00",
                "fitting": "fit",
                "muted": false,
                "loop": false,
                "autoplay": true,
                "class": "",
                "attributes": ""
            },
            {
                "id": "f441284a31c0e464a870ff1c50541ba51c433c57",
                "format": "video",
                "duration": 317.115,
                "source": "https:\/\/example.com.au\/video-b.mp4",
                "start": 1748572820000,
                "stop": 1748572830000,
                "start_ts": "2025-05-30T02:40:20+00:00",
                "stop_ts": "2025-05-30T02:30:20+00:00",
                "fitting": "fit",
                "muted": false,
                "loop": false,
                "autoplay": true,
                "class": "",
                "attributes": ""
            }
        ]
    }
}
         */
    }


}
