<?php

namespace App\Controller\Administrators;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Controller\AppController;
use App\HotFolderWorkflows\ProcessUStoreOrders;
use App\HotFolderWorkflows\ProcessWooCommerceOrders;
use App\Log\Engine\Auditor;
use App\MessageGateways\SmsGatewayFactory;
use App\Model\Entity\Artifact;
use App\Model\Entity\CodeWatcherProject;
use App\Model\Entity\IntegrationCredential;
use App\Model\Entity\OutputProcessor;
use App\Model\Entity\User;
use App\Model\Table\ApplicationLogsTable;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\BackgroundServicesTable;
use App\Model\Table\CodeWatcherProjectsTable;
use App\Model\Table\DocumentsTable;
use App\Model\Table\ErrandsTable;
use App\Model\Table\HeartbeatsTable;
use App\Model\Table\IntegrationCredentialsTable;
use App\Model\Table\InternalOptionsTable;
use App\Model\Table\JobsTable;
use App\Model\Table\MessagesTable;
use App\Model\Table\OrdersTable;
use App\Model\Table\OutputProcessorsTable;
use App\Model\Table\ScheduledTasksTable;
use App\Model\Table\SeedsTable;
use App\Model\Table\SubscriptionsTable;
use App\Model\Table\UsersTable;
use App\OrderManagement\PdfOrdering;
use App\OrderManagement\PhotoPackageOrdering;
use App\OrderManagement\uStoreOrdering;
use App\OutputProcessor\BackblazeBucketOutputProcessor;
use App\OutputProcessor\EpsonPrintAutomateOutputProcessor;
use App\OutputProcessor\FolderOutputProcessor;
use App\OutputProcessor\FujifilmXmfPressReadyCsvHotFolderProcessor;
use App\OutputProcessor\FujifilmXmfPressReadyPdfHotFolderProcessor;
use App\OutputProcessor\Handler\OutputProcessorHandlerForOrdersJobsDocuments;
use App\OutputProcessor\sFTPOutputProcessor;
use App\Utility\CodeWatcher\Sweeper;
use App\Utility\Feedback\DebugSqlCapture;
use App\Utility\Gravatar\Gravatar;
use App\Utility\Instances\InstanceTasks;
use App\Utility\IntegrationCredentials\sFTP\sftpCommunicationsFlow;
use App\Utility\Network\CACert;
use App\Utility\Releases\GitTasks;
use App\Utility\Releases\RemoteUpdateServer;
use App\Utility\Storage\BackblazeB2Inspector;
use App\Utility\Storage\SftpInspector;
use App\Utility\Storage\UncInspector;
use App\Utility\Storage\UrlInspector;
use App\VendorIntegrations\Fujifilm\PressReady;
use App\XMPie\uProduce\Tasks\CompositionMaker;
use arajcany\BackblazeB2Client\BackblazeB2\AuthorizationCache;
use arajcany\PhotoPackageAdapter\Adapters\PackageReader;
use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;
use arajcany\PrePressTricks\Graphics\FFmpeg\FFmpegCommands;
use arajcany\PrePressTricks\Utilities\ImageInfo;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Cake\Utility\Xml;
use DOMDocument;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use GuzzleHttp\Client;
use League\Flysystem\StorageAttributes;
use Pelago\Emogrifier\CssInliner;
use phpseclib3\Crypt\RSA;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Throwable;
use function PHPUnit\Framework\isFinite;

/**
 * Developers Controller
 *
 * @property InternalOptionsTable $InternalOptions;
 * @property ArtifactsTable $Artifacts;
 * @property SeedsTable $Seeds;
 * @property ErrandsTable $Errands;
 * @property MessagesTable $Messages;
 * @property UsersTable $Users;
 * @property HeartbeatsTable $Heartbeats;
 * @property SubscriptionsTable $Subscriptions;
 * @property IntegrationCredentialsTable $IntegrationCredentials;
 */
class DevelopersController extends AppController
{
//    protected InternalOptionsTable|\Cake\ORM\Table $InternalOptions;
    private $Artifacts;
    private $Errands;
    private $Messages;
    private $Heartbeats;
    private $Subscriptions;
//    private $TrackLogins;
//    private $TrackHits;
    private Table|IntegrationCredentialsTable $IntegrationCredentials;

    public function initialize(): void
    {
        parent::initialize();

        $this->InternalOptions = TableRegistry::getTableLocator()->get('InternalOptions');
        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        $this->Errands = TableRegistry::getTableLocator()->get('Errands');
        $this->Messages = TableRegistry::getTableLocator()->get('Messages');
        $this->Users = TableRegistry::getTableLocator()->get('Users');
        $this->Heartbeats = TableRegistry::getTableLocator()->get('Heartbeats');
        $this->Subscriptions = TableRegistry::getTableLocator()->get('Subscriptions');
        $this->IntegrationCredentials = TableRegistry::getTableLocator()->get('IntegrationCredentials');
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
     * @return Response|null
     */
    public function index(): Response|null
    {
        return null;
    }

    /**
     * Index method
     *
     * @return Response|null
     */
    public function internalOptions(): Response|null
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        //clear test
//        $toDebug['cache-before'] = $this->InternalOptions->getOption('company_name');
//        $this->getTableLocator()->get('InternalOptions')->dumpInternalOptions();
//        $toDebug['cache-after'] = $this->InternalOptions->getOption('company_name');

        $toDebug['getSecurityKey'] = $this->InternalOptions->getSecurityKey();
        $toDebug['getSecuritySalt'] = $this->InternalOptions->getSecuritySalt();
        $toDebug['getAuthorText'] = $this->InternalOptions->getAuthorText();

//        debug(Cache::read('InternalOptions', 'query_results_app'));
//        debug(Configure::read('InternalOptions'));

        $this->set('toDebug', $toDebug);

        return null;
    }

    /**
     * Index method
     *
     * @return Response|null
     */
    public function remoteUpdates(): Response|null
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $ru = new RemoteUpdateServer();
        $toDebug['checkB2Server'] = $ru->checkB2Server();
//        $toDebug['checkSftpServer'] = $ru->checkSftpServer();
//        $toDebug['checkUncServer'] = $ru->checkUncServer();
//        $toDebug['checkUrlServer'] = $ru->checkUrlServer();
//        $toDebug['getRemoteServer'] = $ru->getRemoteUpdateServer();

        $this->set('toDebug', $toDebug);

        return null;
    }

    public function bucketFlysystem()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

//        try {
//            // Instantiate the S3 client with your AWS credentials
//            $s3Client = new S3Client([
//                'credentials' => [
//                    'key' => '00404b758b152580000000001',
//                    'secret' => 'K004ffPbhEfEm5MN3bApMW/5bfBDQTM'
//                ],
//                'endpoint' => 'https://s3.us-west-004.backblazeb2.com',
//                'region' => 'ap-southeast-2',
//                'version' => 'latest',
//                /**
//                 * keyID:
//                 * 00404b758b152580000000001
//                 * keyName:
//                 * ApplicationWorks-FBAU-GCS-PS
//                 * applicationKey:
//                 * K004ffPbhEfEm5MN3bApMW/5bfBDQTM
//                 */
//                'http' => [
//                    'verify' => (new CACert())->getCertPath(),
//                ]
//            ]);
//
//            $bucketname = 'FBAU-GCS-PS'; //test bucket
//
//            $adapter = new AwsS3V3Adapter($s3Client, $bucketname);
//            $filesystem = new Filesystem($adapter);
//            $path = '';
//            $listing = $filesystem->listContents($path, false)->sortByPath();
////            $toDebug['$listing'] = $listing->toArray();
//            //print_r($listing->toArray());
//
//            /**
//             * @var StorageAttributes $item
//             */
//            foreach ($listing as $item) {
//                $path = $item->path();
//                if ($item instanceof FileAttributes) {
//                    // handle the file
//                    print_r("File: {$item->path()}\r\n");
//                    $toDebug['Files'][] = $item->path();
//                } elseif ($item instanceof DirectoryAttributes) {
//                    // handle the directory
//                    print_r("Path: {$item->path()}\r\n");
//                    $toDebug['Paths'][] = $item->path();
//                }
//            }
//
//            //check if directory/file exists
//            $filePath = 'piq_jobs/J954405';
//            $resultPathExists = $filesystem->directoryExists($filePath);
//            $toDebug['$resultPathExists'] = $resultPathExists;
//
//            $filePath = 'piq_jobs/J954405/J954405_2_tn.png';
//            $resultFileExists = $filesystem->fileExists($filePath);
//            $toDebug['$resultFileExists'] = $resultFileExists;
//
//            //download a file
//            $filePath = 'piq_jobs/J954405/J954405_1.pdf';
//            $fileContents = $filesystem->read($filePath);
//            $resultWritten = file_put_contents('c:/temp/s3file.pdf', $fileContents);
//            $toDebug['$resultWritten'] = $resultWritten;
//
//        } catch (Throwable $exception) {
//            $toDebug['$exception'] = $exception;
//        }


        $this->set('toDebug', $toDebug);

    }

    public function checkIntegrationCredentials()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $result = $this->IntegrationCredentials->updateLastStatusForAll();
        $toDebug['$result'] = $result;;

        $this->set('toDebug', $toDebug);
    }

    public function checkIntegrationCredentialsSftp()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var IntegrationCredential $result */
        $result = $this->IntegrationCredentials->find('all')->where(['type' => 'sftp'])->first();
        //$toDebug['$result'] = $result;;

        $SftpFlow = new sftpCommunicationsFlow($result);

        $check = $SftpFlow->checkViaRawConnection($result->sftp_getParametersDecrypted());
        $toDebug['$check'] = $check;;


        $this->set('toDebug', $toDebug);
    }

    public function storageInspector()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var IntegrationCredential $result */
        //$result = $this->IntegrationCredentials->find('all')->where(['type' => 'sftp'])->first();
        //$toDebug['$result'] = $result;

        //$Inspector = new SftpInspector();
        //
        //$sftp = [
        //    'sftp_host' => '192.168.0.185',
        //    'sftp_port' => 22,
        //    'sftp_username' => 'sftp_test',
        //    'sftp_password' => 'N6e$W7v6SS!*3H',
        //    'sftp_timeout' => 2,
        //    'sftp_path' => 'web',
        //    'http_host' => 'http://192.168.0.185',
        //];
        //
        //$result = $Inspector->inspectSftpServer($sftp);
        //$toDebug['sft_report'] = $Inspector->getInspectionReport();
        //$toDebug['sft_debug'] = $Inspector->getAllAlerts();
        //
        //
        //$Inspector = new UncInspector();
        //
        //$unc = [
        //    'unc_host' => '\\\\192.168.0.185\\',
        //    'unc_path' => 'web',
        //    'http_host' => 'http://192.168.0.185',
        //];
        //
        //$result = $Inspector->inspectUncServer($unc);
        //$toDebug['unc_report_1'] = $Inspector->getInspectionReport();
        //$toDebug['unc_debug_1'] = $Inspector->getAllAlerts();
        //
        //
        //$Inspector = new UncInspector();
        //
        //$unc = [
        //    'unc_host' => 'M:\\GenericRepository\\_ApplicationWorks\\',
        //    'unc_path' => '',
        //    'http_host' => 'https://applicationworks.codeworthy.name/repository/',
        //];
        //
        //$result = $Inspector->inspectUncServer($unc);
        //$toDebug['unc_report_2'] = $Inspector->getInspectionReport();
        //$toDebug['unc_debug_2'] = $Inspector->getAllAlerts();
        //
        //
        //$Inspector = new UrlInspector();
        //$url = [
        //    'http_host' => 'https://httpbin.org/status/200',
        //    'http_method' => 'GET',
        //    'http_timeout' => 5
        //];
        //$result = $Inspector->inspectUrlConnection($url);
        //$toDebug['url_200_report'] = $Inspector->getInspectionReport();
        //$toDebug['url_200_debug'] = $Inspector->getAllAlerts();
        //
        //
        //$Inspector = new UrlInspector();
        //$url = [
        //    'http_host' => 'https://httpbin.org/status/404',
        //    'http_method' => 'GET',
        //    'http_timeout' => 5
        //];
        //$result = $Inspector->inspectUrlConnection($url);
        //$toDebug['url_404_report'] = $Inspector->getInspectionReport();
        //$toDebug['url_404_debug'] = $Inspector->getAllAlerts();

        $Inspector = new BackblazeB2Inspector();
        $b2Config = [
            "b2_bucket_name" => "PaperBabyCreative",
            "b2_key_id" => "0005ac35c8e5043000000000a",
            "b2_key" => Security::decrypt64("NDkzOTI5ZGE3YWQ0ODY0ZTUyMDUyNmE0YjFjODliZTI2MDIyNGJlODlmZTAyMThkZDBlZGU2NTdmMGM2NjhkMiHT1I0RzHWO6yvWC3VWdjr9e0nc7RLg0syOyvx/q9qtEeyzR6rWQDa48XprC31IbQ=="),
            "b2_bucket" => "454a8cb325ccc87e85000413",
            "b2_path" => "123",
            "http_host" => 'https://f000.backblazeb2.com/file/PaperBabyCreative/123/',
        ];
        //$result = $Inspector->inspectBackBlazeB2Server($b2Config);
        //$toDebug['b2_report'] = $Inspector->getInspectionReport();
        //$toDebug['b2_debug'] = $Inspector->getAllAlerts();

        $this->set('toDebug', $toDebug);
    }

    public function b2()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];
        $start = microtime(true);


        $Inspector = new BackblazeB2Inspector();
        $b2Config = [
            //DevAllBuckets
            "b2_key_id" => "0005ac35c8e5043000000000c",
            "b2_key" => 'K000MEpHDg1/pakVvjTU9QJ4EOGtD/A',
        ];
        $b2Config = [
            //DevOneBucket
            "b2_key_id" => "0005ac35c8e5043000000000d",
            "b2_key" => 'K000GNb0L9D+SzY2910peDRJa0Ck5Xg',
        ];
        //$b2Config = [
        //    //DevOneBucketNamePrefix
        //    "b2_key_id" => "0005ac35c8e5043000000000e",
        //    "b2_key" => 'K000MLmNJ8fdIRnR2IRPZZtjVj/WBtA',
        //];

        $result = $Inspector->getAccountAuthorisation($b2Config);
        dd($result);


        ////test using the native API to get info
        //$b2Config = [
        //    //DevAllBuckets
        //    "b2_key_id" => "0005ac35c8e5043000000000c",
        //    "b2_key" => 'K000MEpHDg1/pakVvjTU9QJ4EOGtD/A',
        //];
        //$b2Config = [
        //    //DevOneBucket
        //    "b2_key_id" => "0005ac35c8e5043000000000d",
        //    "b2_key" => 'K000GNb0L9D+SzY2910peDRJa0Ck5Xg',
        //];
        //$requestConfig = [
        //    'auth' => [$b2Config['b2_key_id'], $b2Config['b2_key']],
        //];
        //$client = new Client([
        //    'verify' => (new CACert())->getCertPath(),
        //]);
        //$response = $client->request('GET', 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account', $requestConfig);
        //
        //$authData = json_decode($response->getBody(), true);
        //$apiUrl = $authData['apiUrl'] ?? null; // For API calls
        //$downloadUrl = $authData['downloadUrl'] ?? null; // For public downloads
        //$authToken = $authData['authorizationToken'] ?? null;
        //$toDebug['$apiUrl'] = $apiUrl;
        //$toDebug['$downloadUrl'] = $downloadUrl;
        //$toDebug['$authToken'] = $authToken;
        //$toDebug['$authData'] = $authData;


        //BackblazeB2 Client options
        $config = [
            //DevAllBuckets
            'applicationKeyId' => '0005ac35c8e5043000000000c',
            'applicationKey' => 'K000MEpHDg1/pakVvjTU9QJ4EOGtD/A',
            //'authorizationCache' => false, //uncomment to stop using the AuthorizationCache, but why would you?
        ];

        $config = [
            //DevOneBucket
            'applicationKeyId' => '0005ac35c8e5043000000000d',
            'applicationKey' => 'K000GNb0L9D+SzY2910peDRJa0Ck5Xg',
            //'authorizationCache' => false, //uncomment to stop using the AuthorizationCache, but why would you?
        ];

        //Guzzle options
        $guzzleConfig = [
            'verify' => (new CACert())->getCertPath()
        ];

        $B2Client = new \arajcany\BackblazeB2Client\BackblazeB2\Client($config, $guzzleConfig);

        //$toDebug['authorizeAccount()'] = $B2Client->authorizeAccount(); //Zaxbux\BackblazeB2\Object\AccountAuthorization
        $toDebug['refreshAccountAuthorization()'] = $B2Client->refreshAccountAuthorization();

        //this will only work for a key pair that has access to all buckets
        //$toDebug['listBuckets()'] = $B2Client->listBuckets();

        //this will only give results for key pair that have access to a single bucket
        $bucketId = $B2Client->allowedBucketId();
        $bucketName = $B2Client->allowedBucketName();
        $Bucket = $B2Client->getBucketById($bucketId);
        $toDebug['allowedBucketId()'] = $bucketId;
        $toDebug['allowedBucketName()'] = $bucketName;
        //$toDebug['getBucketById()'] = $Bucket;
        //$toDebug['getBucketByName()'] = $B2Client->getBucketByName($bucketName);

        $toDebug['id()'] = $Bucket->id();
        $toDebug['type()'] = $Bucket->type();
        $toDebug['name()'] = $Bucket->name();
        $toDebug['accountId()'] = $Bucket->accountId();
        $toDebug['options()'] = $Bucket->options();
        $toDebug['jsonSerialize()'] = $Bucket->jsonSerialize();
        $toDebug['corsRules()'] = $Bucket->corsRules();
        $toDebug['defaultServerSideEncryption()'] = $Bucket->defaultServerSideEncryption();
        $toDebug['fileLockConfiguration()'] = $Bucket->fileLockConfiguration();
        $toDebug['info()'] = $Bucket->info();
        $toDebug['lifecycleRules()'] = $Bucket->lifecycleRules();

        //$toDebug['listFileNames()'] =$B2Client->listFileNames();


        $end = microtime(true);
        $toDebug['time'] = $end - $start;
        $this->set('toDebug', $toDebug);
    }

    public function generateKeys()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $key = RSA::createKey(2048);
        $privateKey = $key->toString('PKCS1');     // private key
        $publicKey = $key->getPublicKey()->toString('OpenSSH'); // public key

        $toDebug['$privateKey'] = $privateKey;
        $toDebug['$publicKey'] = $publicKey;

        $this->set('toDebug', $toDebug);
    }

    public function os()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $pids = $this->Heartbeats->getApplicationPids();
        $toDebug['$pids'] = $pids;;

        $taskList = $this->Heartbeats->getApplicationTasks();
        $toDebug['$taskList'] = $taskList;;


        $this->set('toDebug', $toDebug);
    }


    public function dates()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $dates = [
            '2',
            '20',
            '202',
            '2023',
            '28/3/23',
            '28/3/2023',
            '2023-03',
            '2023-03-04',
        ];

        foreach ($dates as $date) {
            try {
                $toDebug[$date] = (new DateTime($date))->format("Y-m-d H:i:s");
            } catch (Throwable $exception) {
                $toDebug[$date] = $exception->getMessage();
            }
        }

        $date = '2023-2-1';
        $fDate = new DateTime($date);
        $toDebug['feb start'] = (clone $fDate)->startOfMonth()->format("Y-m-d H:i:s");
        $toDebug['feb end'] = (clone $fDate)->endOfMonth()->format("Y-m-d H:i:s");

        $this->set('toDebug', $toDebug);
    }


    public function errand()
    {
        /** @var BackgroundServicesTable $BackgroundServices */
        $BackgroundServices = TableRegistry::getTableLocator()->get('BackgroundServices');
        $threadNumbers = $BackgroundServices->getThreadNumbers('errand');

        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        //calling via the special *Table syntax
        foreach (range(1, 10) as $loop) {
            $options = [
                'name' => Security::purl(),
                'class' => 'SeedsTable',
                'method' => 'createSeed',
                'parameters' => [0 => ['token' => sha1(Security::randomBytes(2048))]],
                'lock_to_thread' => array_rand(array_values($threadNumbers)),
            ];

            $errand = $this->Errands->createErrand($options);
            $toDebug['$errand_' . $loop] = $errand;
        }

        //calling via namespace
        foreach (range(1, 10) as $loop) {
            $options = [
                'name' => Security::purl(),
                'class' => '\\App\\Log\\Engine\\Auditor',
                'method' => 'logInfo',
                'parameters' => [__('Here is some info...{0}', Security::purl())],
                'lock_to_thread' => array_rand(array_values($threadNumbers)),
            ];

            $errand = $this->Errands->createErrand($options);
            $toDebug['$errand_' . $loop] = $errand;
        }

        $this->set('toDebug', $toDebug);
    }


    public function errandTypeMap()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

//        $result = $this->Errands->getSchema()->typeMap();
        $result = $this->Heartbeats->getSchema()->getColumn('description');
        dd($result);

        $this->set('toDebug', $toDebug);
    }


    public function errandSearch()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $this->Errands->getSchema()->setColumnType('parameters', 'string');
        $errands = $this->Errands->find('all')
            ->select(['id'])
            ->where(['parameters like' => '%tok%']);

        $toDebug['$errands query'] = DebugSqlCapture::captureDump($errands, true);
        //$toDebug['$errands'] = $errands->toArray();

        $this->set('toDebug', $toDebug);
    }


    public function errandReset()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $counter = $this->Errands->resetErrand('3414', 120, true);
        $toDebug['$counter'] = $counter;

        $this->set('toDebug', $toDebug);
    }


    public function seed()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        foreach (range(0, 20) as $loop) {
            $token = sha1(mt_rand() . mt_rand() . mt_rand() . mt_rand());
            $toDebug['$expired_' . $loop] = $this->Seeds->createExpiredSeedFromToken($token);;
        }

        $this->set('toDebug', $toDebug);
    }


    public function session()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $toDebug['time'] = time();
        $toDebug['session_started'] = $this->request->getSession()->started();
        $toDebug['session_headers'] = $this->request->getSession()->id();
        $toDebug['Configure'] = Configure::read();
        $toDebug['session'] = $this->request->getSession()->read();

        $this->set('toDebug', $toDebug);
    }


    public function messageTable()
    {
        //sending an email via the Messages Table (the preferred way)

        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var User $user */
        $user = $this->Users->find('all')->where(['id' => 1])->first();

        $rnd = mt_rand();
        $data = [
            'name' => __('{0} Test Welcome Email {1}', $user->first_name, $rnd),
            'description' => 'Sending mail from Developers->message().',
            'transport' => 'default',
            'profile' => 'default',
            'layout' => 'user_messages',
            'template' => 'user_welcome',
            'view_vars' => [
                'entities' => [
                    'user' => $user->id,
//                    'artifacts' => [1, 2, 3, 4],
//                    'pings' => ['table' => 'users', 'id' => 2],
//                    'pongs' => ['table' => 'users', 'id' => [3, 4, 5]],
                ],
                'foo' => 'This is a foo',
                'bar' => 'This is a bar',
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'company_address' => $this->Settings->getSetting('comapny_name'),
                'random' => $rnd,
            ],
            'email_to' => [$user->email => __("{0} {1}", $user->first_name, $user->last_name)],
            'subject' => __('Welcome {0} ID:{1}', $user->first_name, $user->id),
        ];

        $message = $this->Messages->createMessage($data);
        $toDebug['$message'] = $message;

        //calling send but usually done via background service
//        $sendResult = $this->Messages->sendMessage($message);
//        $toDebug['sendResult'] = $sendResult;

//        sleep(3);
//
        //calling resend but usually done via background service
//        $sendResult = $this->Messages->resendMessage($message);
//        $toDebug['resendResult'] = $sendResult;

        $this->set('toDebug', $toDebug);
    }


    public function messageCake()
    {
        //sending an email via the CakePHP Classes

        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $moreViewVars = [
            'entities' => [
                'user' => 1,
                'artifacts' => [1, 2, 3, 4],
                'pings' => ['table' => 'users', 'id' => 2],
                'pongs' => ['table' => 'users', 'id' => [3, 4, 5]],
            ],
            'foo' => 'This is a foo',
            'bar' => 'This is a bar',
            'firstName' => 'James',
            'lastName' => 'Brown',
        ];

        try {
            $Mailer = new Mailer('default');

            $mailer = $Mailer
                ->setTransport('default')
                ->setProfile('default')
                ->setViewVars($moreViewVars)
                ->setEmailFormat('html')
                ->setFrom(['me@example.com' => 'My Site'])
                ->setTo('you@example.com')
                ->setSubject('About 123');

            $mailer->viewBuilder()
                ->setTemplate('user_welcome')
                ->setLayout('user_welcome');

            $mailer->send();

        } catch (Throwable $exception) {
            dd($exception->getMessage());
        }

        $this->set('toDebug', $toDebug);
    }


    public function messageCakeMore()
    {
        //sending an email via the CakePHP Classes

        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $moreViewVars = [
            'entities' => [
                'user' => 1,
                'artifacts' => [1, 2, 3, 4],
                'pings' => ['table' => 'users', 'id' => 2],
                'pongs' => ['table' => 'users', 'id' => [3, 4, 5]],
            ],
            'foo' => 'This is a foo',
            'bar' => 'This is a bar',
            'firstName' => 'James',
            'lastName' => 'Brown',
        ];

        try {
            $Mailer = new Mailer('default');

            $mailer = $Mailer
                ->setTransport('default')
                ->setProfile('default')
                ->setViewVars($moreViewVars)
                ->setEmailFormat('both')
                ->setFrom(['me@example.com' => 'My Site'])
                ->setTo('you@example.com')
                ->setSubject('About 123');

            $mailer->viewBuilder()
                ->setTemplate('user_welcome')
                ->setLayout('user_welcome');


            $modifyEmailBody = true;
            if ($modifyEmailBody) {
                //use this path to Emogrify the message
                $message = $mailer->render()->getMessage();
                $htmlBody = $message->getBodyHtml();
                $htmlBody = str_replace("Welcome ", "Hello ", $htmlBody);
                $message = $message->setBodyHtml("<b>Welcome Text HTML</b>");
                $message = $message->setBodyText("Welcome Text Body");
                $result = $mailer->getTransport()->send($message);
                dd($result);
            } else {
                $result = $mailer->send();
                dd($result);
            }


            //$mailer->send();
        } catch (Throwable $exception) {
            dd($exception->getMessage());
        }

        $this->set('toDebug', $toDebug);
    }


    public function messageCakeParts()
    {
        //sending an email via the CakePHP Classes

        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $moreViewVars = [
            'entities' => [
                'user' => 1,
                'artifacts' => [1, 2, 3, 4],
                'pings' => ['table' => 'users', 'id' => 2],
                'pongs' => ['table' => 'users', 'id' => [3, 4, 5]],
            ],
            'foo' => 'This is a foo',
            'bar' => 'This is a bar',
            'firstName' => 'James',
            'lastName' => 'Brown',
        ];
        $moreViewVars['entities'] = $this->Messages->expandEntities($moreViewVars['entities']);

        try {
            $render = new \Cake\Mailer\Renderer();
            $render->viewBuilder()
                ->setTemplate('user_welcome')
                ->setLayout('user_welcome')
                ->setVars($moreViewVars);
            $bodyHtml = ($render->render('', ['html']))['html'];

            $bodyHtmlInline = CssInliner::fromHtml($bodyHtml)->inlineCss()->render();

            $message = new \Cake\Mailer\Message();
            $message
                ->setEmailFormat('html')
                ->setFrom(['me@example.com' => 'My Site'])
                ->setTo('you@example.com')
                ->setSubject('About 123')
                ->setBodyHtml($bodyHtmlInline);

            $transport = TransportFactory::get('default');
            $result = $transport->send($message);
            dd($result);


            //$mailer->send();
        } catch (Throwable $exception) {
            dd($exception->getMessage());
        }

        $this->set('toDebug', $toDebug);
    }

    public function transport()
    {
        $transport = TransportFactory::get('default');
        dump($transport->getConfig());
        $mailer = Mailer::getConfig('default');
        dump($mailer);

        dd(123);
    }

    public function subscriptions()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var SubscriptionsTable $Subscriptions */
        $Subscriptions = TableRegistry::getTableLocator()->get('Subscriptions');
        $user = $Subscriptions->getCurrentAuthenticatedUser();

        $allSubs = $Subscriptions->findAllSubscriptions();
        $myActiveSubs = $Subscriptions->findmyActiveSubscriptions($user->id);
        $myInactiveSubs = $Subscriptions->findMyInactiveSubscriptions($user->id);
        $myAllowedSubs = $Subscriptions->findMyAllowedSubscriptions($user->id);

        $toDebug['$allSubs'] = $allSubs->find('list')->toArray();
        $toDebug['$myActiveSubs'] = $myActiveSubs->find('list')->toArray();
        $toDebug['$myInactiveSubs'] = $myInactiveSubs->find('list')->toArray();
        $toDebug['$myAllowedSubs'] = $myAllowedSubs->find('list')->toArray();

        //sqld($mySubs);

        $this->set('toDebug', $toDebug);
    }


    public function exif()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        //$result = $this->Artifacts->imageExif("\\\\localhost\\GenericRepo\\_TestPhotos\\IMG_7072.HEIC");
        //$toDebug['IMG_7072'] = $result;

        //$result = $this->Artifacts->imageExif("\\\\localhost\\GenericRepo\\_TestPhotos\\Chrysanthemum.jpg");
        //$toDebug['Chrysanthemum'] = $result;

        $ImageInfo = new ImageInfo();
        $toDebug['getExifToolPath'] = $ImageInfo->getExifToolPath();

        $imageFile = "\\\\localhost\\GenericRepo\\_TestPhotos\\IMG_7072.HEIC";
        $imageFile = "\\\\localhost\\GenericRepo\\_TestPhotos\\IMG_7072.jpg";

        $result = $ImageInfo->getImageMeta($imageFile);
        $toDebug['getImageMeta'] = $result;

        $result = $ImageInfo->getExif($imageFile);
        $toDebug['getExif'] = $result;

        $this->set('toDebug', $toDebug);
    }


    public function artifactPdf()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

//        $result = $this->Artifacts->createLightTableImages(174);
//        $toDebug['$result'] = $result;
//        $toDebug['messages'] = $this->Artifacts->getAllAlertsLogSequence();

//        $artifact = $this->Artifacts->get(174);
//        $light_table_urls =  $artifact->light_table_urls;
//        $toDebug['$light_table_urls'] = $light_table_urls;

        $result = $this->Artifacts->createSampleSizes(545);
        $toDebug['$result'] = $result;
        $toDebug['messages'] = $this->Artifacts->getAllAlertsLogSequence();

//        $pdfPath = "C:\\WebAppsDev\\GenericRepo\\e4\\d8\\35\\445\\fb\\9e2\\0b\\7b\\59\\87f\\81f\\720\\57d\\0b4\\67\\fd1\\Portrait A4 Size.pdf";
//        $result = $this->Artifacts->pdfReport($pdfPath);
//        $toDebug['$result'] = $result;


        $this->set('toDebug', $toDebug);
    }

    public function artifactMetadata()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $result = $this->Artifacts->ArtifactMetadata->deleteOrphaned();
        $toDebug['$result'] = $result;
        $toDebug['alerts'] = $this->Artifacts->ArtifactMetadata->getAllAlertsLogSequence();

        $this->set('toDebug', $toDebug);
    }


    public function artifactChunk()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $tries = range(1, 1000000);

        $counts = [];
        foreach ($tries as $try) {
            $token = sha1('abc');
            $result = $this->Artifacts->str_split_random($token, 1, 5);
            $result = implode("/", $result);
            $counts[$result] = 1;
        }

        $toDebug[] = count($counts);
        //$toDebug[] = $counts;

        $this->set('toDebug', $toDebug);
    }


    public function artifacts()
    {
        $this->viewBuilder()->setTemplate('to_debug_artifacts');
        $toDebug = [];

//        $deleted = $this->Artifacts->deleteTopExpired();
//        $toDebug['$deleted'] = $deleted;

//        dump($this->Artifacts->asEntity('74cb14921c89ea4be6902311e242ffd200ee497f')); //token
//        dump($this->Artifacts->asEntity('f5f8ad26819a471318d24631fa5055036712a87e')); //hash_sum
//        dump($this->Artifacts->asEntity('96')); //id
//        die();

//        $errand = $this->Artifacts->createSampleSizesErrand(96, preventDuplicateCreation: false);
//        $toDebug['errand'] = $errand;


//        $result = $this->Artifacts->createSampleSizes(96);
//        $toDebug['sample sizes'] = $result;

        /** @var Artifact $artifact */

        $secured = $this->Artifacts->findAndSecureArtifactUncPaths(1);
        $toDebug['$secured'] = $secured;


        $artifact = $this->Artifacts->get(1386);
        //$toDebug['$artifact'] = $artifact->jsonSerialize();


        $toDebug[] = "Static URL";
        $artifact->setRepoModeAsStatic();
        $toDebug['Static $artifact->full_url'] = $artifact->full_url;
        $toDebug[] = "";


        $toDebug[] = "Dynamic URL";
        $artifact->setRepoModeAsDynamic();
        $toDebug['Dynamic $artifact->full_url'] = $artifact->full_url;
        $toDebug[] = "";


        $toDebug[] = "Static Sample URLs";
        $artifact->setRepoModeAsStatic();
        $toDebug['Static $artifact->sample_url_icon'] = $artifact->sample_url_icon;
        $toDebug['Static $artifact->sample_url_thumbnail'] = $artifact->sample_url_thumbnail;
        $toDebug['Static $artifact->sample_url_preview'] = $artifact->sample_url_preview;
        $toDebug['Static $artifact->sample_url_lr'] = $artifact->sample_url_lr;
        $toDebug['Static $artifact->sample_url_mr'] = $artifact->sample_url_mr;
        $toDebug['Static $artifact->sample_url_hr'] = $artifact->sample_url_hr;
        $toDebug[] = "";


        $toDebug[] = "Dynamic Sample URLs";
        $artifact->setRepoModeAsDynamic();
        $toDebug['Dynamic $artifact->sample_url_icon'] = $artifact->sample_url_icon;
        $toDebug['Dynamic $artifact->sample_url_thumbnail'] = $artifact->sample_url_thumbnail;
        $toDebug['Dynamic $artifact->sample_url_preview'] = $artifact->sample_url_preview;
        $toDebug['Dynamic $artifact->sample_url_lr'] = $artifact->sample_url_lr;
        $toDebug['Dynamic $artifact->sample_url_mr'] = $artifact->sample_url_mr;
        $toDebug['Dynamic $artifact->sample_url_hr'] = $artifact->sample_url_hr;
        $toDebug[] = "";


        $toDebug[] = "Static Light-Table URLs";
        $artifact->setRepoModeAsStatic();
        $toDebug = array_merge($toDebug, $artifact->light_table_urls);
        $toDebug[] = "";


        $toDebug[] = "Dynamic Light-Table URLs";
        $artifact->setRepoModeAsDynamic();
        $toDebug = array_merge($toDebug, $artifact->light_table_urls);
        $toDebug[] = "";


//        $toDebug[] = "UNC Paths";
//        $toDebug['$artifact->full_unc'] = $artifact->full_unc;
//        $toDebug['$artifact->sample_unc_icon'] = $artifact->sample_unc_icon;
//        $toDebug['$artifact->sample_unc_thumbnail'] = $artifact->sample_unc_thumbnail;
//        $toDebug['$artifact->sample_unc_preview'] = $artifact->sample_unc_preview;
//        $toDebug['$artifact->sample_unc_lr'] = $artifact->sample_unc_lr;
//        $toDebug['$artifact->sample_unc_mr'] = $artifact->sample_unc_mr;
//        $toDebug['$artifact->sample_unc_hr'] = $artifact->sample_unc_hr;
//
//
//
//        $data = [
//            'blob' => file_get_contents(TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles/Chrysanthemum.jpg"),
//            'name' => 'Chrysanthemum.jpg',
//            'description' => 'A picture of a chrysanthemum flower.',
//        ];
//        $result = $this->Artifacts->createArtifact($data);
//        $toDebug['Chrysanthemum'] = $result;
//
//        $rnd = mt_rand(1111, 9999);
//        copy(
//            TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles/Chrysanthemum.jpg",
//            TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles/Chrysanthemum{$rnd}.jpg"
//        );
//        $data = [
//            'tmp_name' => TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles/Chrysanthemum{$rnd}.jpg",
//            'name' => "Chrysanthemum{$rnd}.jpg",
//            'description' => 'A picture of a chrysanthemum flower that was copied.',
//        ];
//        $result = $this->Artifacts->createArtifact($data);
//        $toDebug['Chrysanthemum tmp file'] = $result;
//
//        $result = $this->Artifacts->createArtifactFromUrl('flower.jpg', 'https://localhost/Chrysanthemum.jpg');
//        $toDebug['Chrysanthemum URL'] = $result;
//
//        $result = $this->Artifacts->createArtifactFromImageResource('rectangle.jpg', $this->Artifacts->getImageResource());
//        $toDebug['Chrysanthemum URL'] = $result;
//
//        $options = [
//            'width' => mt_rand(64, 256),
//            'height' => mt_rand(64, 256),
//            'background' => '#808080',
//            'format' => 'png',
//            'quality' => '90',
//        ];
//        $result = $this->Artifacts->createArtifactPlaceholder($options);
//        $toDebug['random placeholder'] = $result;

        $this->set('toDebug', $toDebug);
    }


    public function trackHits()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $start = (new DateTime())->subMinutes(5);
        $end = (new DateTime())->addMinutes(5);

        $query = $this->TrackHits->findRemoteUserData();
        //sqld($query);
        $toDebug['$query'] = $query->toArray();


        $this->set('toDebug', $toDebug);
    }


    public function ffmpeg()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $FFmpeg = new FFmpegCommands();

        $videoPath = "C:\\Users\\arajcany\\Pictures\\30 Seconds Countdown.mp4";
        $analysis = $FFmpeg->videoAnalyse($videoPath);
        $toDebug['analysis'] = $analysis;


        $this->set('toDebug', $toDebug);
    }


    public function returnAlerts()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var ApplicationLogsTable $ApplicationLogs */
        $ApplicationLogs = TableRegistry::getTableLocator()->get('ApplicationLogs');
        $this->Artifacts->setIoDatabase($ApplicationLogs);

        $this->Artifacts->setIoJson(true);

        $this->Artifacts->addInfoAlerts("addInfoAlerts");
        $this->Artifacts->addDangerAlerts("addDangerAlerts");
        $this->Artifacts->addSuccessAlerts("addSuccessAlerts");
        $this->Artifacts->addWarningAlerts("addWarningAlerts");
        //$this->Artifacts->addWarningAlerts([1, 2, 3, 4, 5, 6, 7, 8, 9]);

//        $toDebug['getAllAlertsForMassInsert'] = $this->Artifacts->getAllAlertsForMassInsert();
//        $toDebug['getAllAlertsLogSequence'] = $this->Artifacts->getAllAlertsLogSequence();

//        $sql = $ApplicationLogs->findReturnAlerts();
        //sqld($sql);
//        $toDebug['$sql'] = $sql->toArray();

        //$this->request->getSession()->delete('ReturnAlerts');

        $inSession = $this->request->getSession()->read('ReturnAlerts');
        $toDebug['session'] = $inSession;

        $this->set('toDebug', $toDebug);
    }


    public function xmpie()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var \App\Model\Table\IntegrationCredentialsTable $IC */
        $IC = TableRegistry::getTableLocator()->get('IntegrationCredentials');

        /** @var IntegrationCredential $xmpCredentials */
        $xmpCredentials = $IC->find('all')->where(['type' => 'XMPie-uProduce', 'is_enabled' => true])->first();

        $xmpOptions = $xmpCredentials->uProduce_getUserCredentials();

        $soapOptions = [];

        $config = [
            'security' => false,
            'timezone' => 'utc',
        ];

        $XMPieCompositions = new CompositionMaker($xmpOptions, $soapOptions, $config);
        $triggerFilePath = "W:\\arajcany_Projects\\XMPie-uProduce-Client\\Examples\\TriggerFiles\\SimpleDocument-1541.xlsx";
        $jobIds = $XMPieCompositions->produceFromTriggerFile($triggerFilePath);

        $toDebug['$jobIds'] = $jobIds;
        //$toDebug['$XMPieCompositions'] = $XMPieCompositions;

        $this->set('toDebug', $toDebug);
    }


    public function outputProcessorFolder()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OutputProcessorsTable = new OutputProcessorsTable();
        $outputProcessor = $OutputProcessorsTable->find('all')->where(['type' => 'Folder'])->first();

        $Handler = new OutputProcessorHandlerForOrdersJobsDocuments();
        $Handler->setOutputIsErrand(false);

        $orderId = 240;

        $result = $Handler->outputProcessOrder($outputProcessor, $orderId);

        $toDebug['$result'] = $result;
        $Handler->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }


    public function outputProcessorPressReadyCsvHotFolder()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OutputProcessorsTable = new OutputProcessorsTable();
        $outputProcessor = $OutputProcessorsTable->find('all')->where(['type' => 'FujifilmXmfPressReadyCsvHotFolder'])->first();

        $Handler = new OutputProcessorHandlerForOrdersJobsDocuments();
        //$Handler->setOutputIsErrand(false);

        $orderId = 240;

        $result = $Handler->outputProcessOrder($outputProcessor, $orderId);

        $toDebug['$result'] = $result;
        $Handler->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }


    public function outputProcessorPressReadyPdfHotFolder()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OutputProcessorsTable = new OutputProcessorsTable();
        $outputProcessor = $OutputProcessorsTable->find('all')->where(['type' => 'FujifilmXmfPressReadyPdfHotFolder'])->first();

        $Handler = new OutputProcessorHandlerForOrdersJobsDocuments();
        $Handler->setOutputIsErrand(false);

        $orderId = 224;

        $result = $Handler->outputProcessOrder($outputProcessor, $orderId);

        $toDebug['$result'] = $result;
        $Handler->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }

    public function outputProcessorFolderRawPaths()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $FOP = new FolderOutputProcessor();

//        $file = 'C:\\WebAppsDev\\GenericRepo\\708\\456\\b9a\\9d8\\d4\\99d\\e3\\ec\\fa\\779\\78\\04c\\c88\\c21\\6db\\02_Page_Numbers.pdf';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy', //copy||mover the file
//            'fso_sub_folder' => false,
//        ];
//        $FOP->output($file, $options);
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();
//
//        $file = 'C:\\WebAppsDev\\GenericRepo\\708\\456\\b9a\\9d8\\d4\\99d\\e3\\ec\\fa\\779\\78\\04c\\c88\\c21\\6db\\02_Page_Numbers.pdf';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy', //copy||mover the file
//            'fso_sub_folder' => true,
//        ];
//        $FOP->output($file, $options);
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();
//
//        $file = 'C:\\WebAppsDev\\GenericRepo\\708\\456\\b9a\\9d8\\d4\\99d\\e3\\ec\\fa\\779\\78\\04c\\c88\\c21\\6db\\02_Page_Numbers.pdf';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy', //copy||mover the file
//            'fso_sub_folder' => 'down',
//        ];
//        $FOP->output($file, $options);
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();

//        $folder = 'C:\\WebAppsDev\\GenericRepo\\708\\456\\b9a\\9d8\\d4\\99d\\e3\\ec\\fa\\779\\78\\04c\\c88\\c21\\6db\\samples';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy', //copy||mover the file
//            'fso_sub_folder' => false,
//        ];
//        $FOP->output($folder, $options);
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();
//
//        $folder = 'C:\\WebAppsDev\\GenericRepo\\708\\456\\b9a\\9d8\\d4\\99d\\e3\\ec\\fa\\779\\78\\04c\\c88\\c21\\6db\\samples';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy', //copy||mover the file
//            'fso_sub_folder' => true,
//        ];
//        $FOP->output($folder, $options);
//
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();
//
//        $folder = 'C:\\WebAppsDev\\GenericRepo\\708\\456\\b9a\\9d8\\d4\\99d\\e3\\ec\\fa\\779\\78\\04c\\c88\\c21\\6db\\samples';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy', //copy||mover the file
//            'fso_sub_folder' => 'level-down',
//        ];
//        $FOP->output($folder, $options);
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();

//        $folder = 'C:\\Users\\andrew.rajcany\\Pictures\\FolderPics';
//        $options = [
//            'fso_path' => 'C:\\tmp',
//            'fso_copy_or_move' => 'copy',
//            //'prefix' => false,
//            'prefix' => 'Order123-Job456',
//            //'counter' => true,
//            'fso_sub_folder' => true,
//        ];
//        $FOP->output($folder, $options);
//        $toDebug[] = $FOP->getAllAlertsLogSequence();
//        $FOP->clearAllReturnAlerts();

        $str = '[{{Counter}}] {{DateTimeStamp}} {{RandomNumber6}} - {{RandomNumber128}} - {{RandomString6}} - {{RandomString128}} - {{GUID}} - {{GUID}}';
        $str = '.{{DateTimeStamp}}/../..\\.{{RandomString6}}.jpg.';
        $opts = [
            'filenameBuilder' => $str,
        ];
        $result = $FOP->compileFilenameVariable($opts);
        $toDebug[] = $result;

        $this->set('toDebug', $toDebug);
    }

    public function tidyPath()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $FOP = new FolderOutputProcessor();


        $badPaths = [
            'c:\some\path\#&?^~!@#$%^&*())_+=`file.jpg',  //this is actually ok for a file name
            'c:\some\path\/file:*?"<>|.jpg',  //this is actually ok for a file name
            'c:\some\path\..\file.jpg',
            'c:\some\path|path\file|name.jpg',
            'c:\some\path\.file.jpg',
            'c:\some\.path\file.jpg',
            'c:\some\path\\file.jpg',
            '//server/share/path//file.jpg',
            'c:\some\path\\file.',
            'c:/some/path/file.jpg',
            'c:/some/path/\\//file.jpg',
            'c:/some/path/\\//',
            '//server/share/path//',
        ];

        foreach ($badPaths as $badPath) {
            $toDebug[] = [$badPath, $FOP->tidyPath($badPath)];
        }

        $this->set('toDebug', $toDebug);
    }


    public function outputProcessorSftp()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OP = new sFTPOutputProcessor();

        $folder = 'C:\\Users\\andrew.rajcany\\Pictures\\FolderPics';
        $options = [
            'sftp_host' => 'noteworthy.name',
            'sftp_port' => '22',
            'sftp_username' => 'sftp_test',
            'sftp_password' => 'N6e$W7v6SS!*3H',
            'sftp_timeout' => '6',
            'sftp_path' => '/WebServer',
            'sftp_copy_or_move' => 'copy',
            'sftp_sub_folder' => 'halo',
        ];
        $OP->output($folder, $options);
        $toDebug[] = $OP->getAllAlertsLogSequence();
        $OP->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }


    public function outputProcessorEpson()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OP = new EpsonPrintAutomateOutputProcessor();

//        $folder = 'C:\\Users\\andrew.rajcany\\Pictures\\FolderPics';
//        $options = [
//
//        ];
//        $OP->output($folder, $options);

        $toDebug[] = $OP->getDefaultOutputConfiguration();

        $file = 'C:\\Users\\andrew.rajcany\\Desktop\\Auburn Test IK  output 20240507\\1st - Test Poster Print - Colour\\J07CEB37512697\\16x40PosterPrint(Portrait)\\img_0.jpg';
        $options = [
            'epa_username' => 'user',
            'epa_password' => 'P@$$',
            'epa_preset' => 'poster',
        ];
        $toDebug[] = $OP->output($file, $options);


        $toDebug[] = $OP->getAllAlertsLogSequence();
        $OP->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }


    public function outputBackblaze()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OP = new BackblazeBucketOutputProcessor();

//        $folder = 'C:\\Users\\andrew.rajcany\\Pictures\\FolderPics';
//        $options = [
//
//        ];
//        $OP->output($folder, $options);

        /** @var OutputProcessorsTable $outputProcessorsTable */
        $outputProcessorsTable = TableRegistry::getTableLocator()->get('OutputProcessors');

        $toDebug[] = $OP->getDefaultOutputConfiguration();

        $file = 'C:\\Users\\andrew.rajcany\\Desktop\\Auburn Test IK  output 20240507\\1st - Test Poster Print - Colour\\J07CEB37512697\\16x40PosterPrint(Portrait)\\img_0.jpg';
        $file = 'C:\\Users\\andrew.rajcany\\Pictures\\FolderPics\\Desert.jpg';
        /** @var OutputProcessor $op */
        $op = $outputProcessorsTable->find()->where(['id' => 3])->first();
        $options = $op->parameters;

        $toDebug[] = $OP->output($file, $options);


        $toDebug[] = $OP->getAllAlertsLogSequence();
        $OP->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }


    public function microtime()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        foreach (range(1, 10) as $num) {
            $mt = microtime();
            $toDebug[] = $mt;
        }

        foreach (range(1, 10) as $num) {
            $mt = microtime(true);
            $toDebug[] = $mt;
        }

        foreach (range(1, 10) as $num) {
            $mt = microtimestamp('.');
            $toDebug[] = $mt;
        }


        $this->set('toDebug', $toDebug);
    }


    public function outputOrderJobDocument()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $OP = new OutputProcessorHandlerForOrdersJobsDocuments();


        $orderId = '';
        $jobId = '';
        $documentId = '';

        $toDebug['$orderId'] = $OP->outputProcessOrder(1, 101);
//        $toDebug['$jobId'] = $OP->outputProcessJob(1, 270);
//        $toDebug['$documentId'] = $OP->outputProcessDocument(1, 270);


        $toDebug[] = $OP->getAllAlertsLogSequence();
        $OP->clearAllReturnAlerts();

        $this->set('toDebug', $toDebug);
    }


    public function user()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $toDebug[' $this->Auth->user()'] = $this->Auth->user('id');


        $this->set('toDebug', $toDebug);
    }


    public function users()
    {
        $toDebug = [];

        //$this->Users = TableRegistry::getTableLocator()->get('Users');
        //$users = $this->Users->find('all')
        //    ->contain(['UserStatuses', 'Roles', 'UserLocalizations']);
        //$toDebug['$users'] =  $users->toArray();

        //$toDebug['$this->Auth'] =  $this->Auth;
        //$toDebug['$this->AuthUser'] =  $this->AuthUser;
        //$toDebug['$this->AuthUser->hasRoles(superadmin)'] = $this->AuthUser->hasRoles('superadmin');
        $toDebug['timeout'] = $this->Users->getUserRolesSessionTimeoutSeconds();


        $this->set('toDebug', $toDebug);
    }


    public function roles()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

//        $toDebug['$this->AuthUser->roles()'] = $this->Users->Roles->getPeerRoles($this->AuthUser->roles());
//        $toDebug['user'] = $this->Users->Roles->getPeerRoles('user');
//        $toDebug["['user', 'manager']"] = $this->Users->Roles->getPeerRoles(['user', 'supervisor']);

//        $peerRoles = $this->Users->Roles->getPeerRoles($this->AuthUser->roles());
        $peerRoles = $this->Users->Roles->getPeerRoles(7);
        $toDebug['$peerRoles'] = $peerRoles;
        $toDebug['clean_1'] = $this->Users->Roles->validatePeerRoles($peerRoles, 'User');
        $toDebug['clean_2'] = $this->Users->Roles->validatePeerRoles($peerRoles, '4');
        $toDebug['clean_3'] = $this->Users->Roles->validatePeerRoles($peerRoles, ['4', 5]);
        $toDebug['clean_4'] = $this->Users->Roles->validatePeerRoles($peerRoles, ['User', 'Operator']);
        $toDebug['clean_5'] = $this->Users->Roles->validatePeerRoles($peerRoles, ['User', 'Fake']);
        $toDebug['clean_6'] = $this->Users->Roles->validatePeerRoles($peerRoles, [1, 2, 3, 4, 5, 6, 7, 8, 9]);


        $this->set('toDebug', $toDebug);
    }


    public function hasAccess()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        //$toDebug['$this->Auth->user()'] = $this->Auth->user();
        $toDebug["['controller' => 'users']"] = $this->AuthUser->hasAccess(['controller' => 'Users']);

        $this->set('toDebug', $toDebug);
    }


    public function logger()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        //Log::write('error', 'Could not process for userid={user}', ['user' => 1]);

        $Auditor = new Auditor();
        $Auditor->logEmergency('Level ={level}', ['level' => 'logEmergency']);
        $Auditor->logAlert('Level ={level}', ['level' => 'logAlert']);
        $Auditor->logCritical('Level ={level}', ['level' => 'logCritical']);
        $Auditor->logError('Level ={level}', ['level' => 'logError']);
        $Auditor->logWarning('Level ={level}', ['level' => 'logWarning']);
        $Auditor->logNotice('Level ={level}', ['level' => 'logNotice']);
        $Auditor->logInfo('Level ={level}', ['level' => 'logInfo']);
        $Auditor->logDebug('Level ={level}', ['level' => 'logDebug']);

        $this->set('toDebug', $toDebug);
    }


    public function events()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $Auditor = new Auditor();
        $Auditor->audit('User \'{user}\' did some stuff here...', ['user' => 'Andrew'], 'D10');

        $this->set('toDebug', $toDebug);
    }


    public function cache()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $toDebug['Cache1'] = Cache::read('first_run');
        $toDebug['Cache2'] = Cache::read('setttings');
        $toDebug['Cache3'] = Cache::read('InternalOptions');
        $toDebug['clear'] = $this->clearCache();


        $this->set('toDebug', $toDebug);
    }


    public function driver()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $dbDriver = $this->Seeds->getDriver();
        $toDebug['$dbDriver'] = $dbDriver;
//        $dbDriver = $this->Connection->config()['driver'];
//        $toDebug['$dbDriver'] = $dbDriver;


        $this->set('toDebug', $toDebug);
    }


    public function backgroundServices()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $BS = new BackgroundServicesAssistant();
        $toDebug['$services'] = $BS->_getServices();
        $toDebug['$commands'] = $BS->generateCommands();
        //$toDebug['installBackgroundServices'] = $BS->installBackgroundServices();
        //$toDebug['uninstallBackgroundServices'] = $BS->uninstallBackgroundServices();


        $this->set('toDebug', $toDebug);
    }


    public function zipPackager()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $ZP = new ZipPackager();
        $path = TextFormatter::makeDirectoryTrailingSmartSlash("M:\\GenericRepository\\_ApplicationWorksHotFolders\\CreateArtifact");
        $path = "C:\\HotFoldersDev\\PhotoPackageOrders\\J07CEB37512697\\";
        $list = $ZP->rawFileList($path);
        $toDebug['$list'] = $list;

//        foreach ($list as $file) {
//            $blob = file_get_contents($path . $file);
//            $data = [
//                'blob' => $blob,
//                'name' => pathinfo($file, PATHINFO_BASENAME),
//            ];
//            $this->Artifacts->createArtifact($data);
//        }

        $checksum = $ZP->fileStats($path);
        $toDebug['$checksum'] = $checksum;

        $this->set('toDebug', $toDebug);
    }


    public function migrate()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $InstanceTasks = new InstanceTasks();
        $InstanceTasks->performMigrations();

        $this->set('toDebug', $toDebug);
    }


    public function level()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $cmd = "NET SESSION 2>&1";
        $out = null;
        $ret = null;
        exec($cmd, $out, $ret);

        $toDebug['$cmd'] = $cmd;
        $toDebug['$out'] = $out;
        $toDebug['$ret'] = $ret;

        $this->loadComponent('BackgroundServices');
        $toDebug['services'] = $this->BackgroundServices->_getServices();

        $this->set('toDebug', $toDebug);

    }


    public function inflector()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        $toDebug['slug'] = Text::slug("01. Static_Eliminator (Eng Sub) - 03122024.mp4");
        $toDebug['dasherize'] = Inflector::dasherize("01. Static_Eliminator (Eng Sub) - 03122024.mp4");

        $this->set('toDebug', $toDebug);

    }


    public function food()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $rec = file_get_contents(CONFIG . "Migrations/recipes.json");
        $recs = json_decode($rec, JSON_OBJECT_AS_ARRAY);

        $maxIngredient = 0;
        $maxMethod = 0;

        $insertAuthors = [];
        $authorsList = [];

        $insertRecipes = [];

        $insertAuthorsRecipes = [];

        $insertMethods = [];

        $insertIngredients = [];

        foreach ($recs as $r => $rec) {
            if (!isset($rec['Ingredients']) || !isset($rec['Method'])) {
                continue;
            }


            $currentDate = gmdate("Y-m-d H:i:s");

            $randomDate = mt_rand(0, 1662809270);
            $randomDate = date("Y-m-d H:i:s", $randomDate);


            //authors
            $authorId = (array_search($rec['Author'], $authorsList)) + 1;
            if (!in_array($rec['Author'], $authorsList)) {
                $insertAuthors[] = [
                    'created' => $currentDate,
                    'modified' => $currentDate,
                    'name' => $rec['Author'],
                    'is_active' => mt_rand(0, 1),
                ];
                $authorsList[] = $rec['Author'];
            }


            //recipes
            $insertRecipes[] = [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => $rec['Name'],
                'description' => $rec['Description'],
                'publish_date' => $randomDate,
                'ingredient_count' => count($rec['Ingredients']),
                'method_count' => count($rec['Method']),
                'is_active' => mt_rand(0, 1),
                'meta' => '',
            ];
            $recipeId = $r + 1;

            //habtm table
            $insertAuthorsRecipes[] = [
                'foo_author_id' => $authorId,
                'foo_recipe_id' => $recipeId,
            ];


            //Ingredients
            $ingredients = $rec['Ingredients'];
            foreach ($ingredients as $ingredient) {
                $maxIngredient = max($maxIngredient, strlen($ingredient));
                $insertIngredients[] = [
                    'foo_recipe_id' => $recipeId,
                    'created' => $currentDate,
                    'modified' => $currentDate,
                    'text' => $ingredient,
                ];
            }


            //Method
            $methods = $rec['Method'];
            foreach ($methods as $method) {
                $maxMethod = max($maxMethod, strlen($method));
                $insertMethods[] = [
                    'foo_recipe_id' => $recipeId,
                    'created' => $currentDate,
                    'modified' => $currentDate,
                    'text' => $method,
                ];
            }

        }

        dd($insertMethods);

        $this->set('toDebug', $toDebug);
    }

    public function appTable()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $toDebug[] = $this->Users->UserStatuses->getIdByNameOrALias('disabled');
        $toDebug[] = $this->Users->UserStatuses->getIdByNameOrALias('aaaaaa');
        $toDebug[] = $this->Users->UserStatuses->getIdsByNameOrALias(['disabled', 'active']);
        $toDebug[] = $this->Users->UserStatuses->getIdsByNameOrALias(['aaaaaa', 'bbbbbbb']);

        $this->set('toDebug', $toDebug);


    }


    public function removalIssue()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];
        $zipPackager = new ZipPackager();

        $zipFilePathName = "S:\\ContractUsageReporting\\20230209_110704_ContractUsageReporting_v1.0.4.zip";
        $baseExtractDir = "S:\\ContractUsageReporting\\_issueWithZipPackagerRemovingFiles\\ContractUsageReporting";

        //$report = $zipPackager->getZipFsoDifference($zipFilePathName, $baseExtractDir, true);
        //dd($report);

        $unzipResult = $zipPackager->extractZipDifference($zipFilePathName, $baseExtractDir, true);
        dd($unzipResult);


        $toDebug['$removeList'] = $removeList;

        $this->set('toDebug', $toDebug);


    }


    public function getGitIgnored()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];
        $GT = new GitTasks();
        $gitIgnored = $GT->getIgnoredFiles();

        $toDebug['$gitIgnored'] = $gitIgnored;

        $this->set('toDebug', $toDebug);


    }


    public function findNonPhpFiles()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];
        $zipPackager = new ZipPackager();
        $GT = new GitTasks();

//        $vendorDir = ROOT . DS . 'vendor';
//
//        $options = [
//            'sha1' => false,
//            'crc32' => false,
//            'mime' => false,
//            'size' => false,
//        ];
//        $stats = $zipPackager->fileStats($vendorDir, null, $options);
//
//        $nonPhp = [];
//        foreach ($stats as $stat) {
//            if (isset($stat['file'])) {
//                if (!TextFormatter::endsWith($stat['file'], '.php')) {
//                    $nonPhp[] = pathinfo($stat['file'], PATHINFO_BASENAME);
//                }
//            }
//        }
//
//        $nonPhp = array_unique($nonPhp);
//        asort($nonPhp, SORT_NATURAL);
//        $nonPhp = array_values($nonPhp);
//
//        $toDebug['$nonPhp'] = $nonPhp;

        $baseDir = ROOT . DS . 'vendor';
        $baseDir = ROOT . DS;
        $ignoreFilesFolders = [
            ".editorconfig",
            ".git\\",
            ".gitattributes",
            ".github\\",
            ".gitignore",
            ".idea\\",
            ".travis.yml",
            "LICENSE",
            "README.md",
            "bin\\ReleaseBuilder.bat",
            "bin\\ComposerCommands.txt",
            "bin\\installer\\",
            "composer.json",
            "composer.lock",
            "config\\Migrations\\notes.txt",
            "config\\Migrations\\schema-dump-default.lock",
            "config\\Stub_DB.sqlite",
            "config\\config_local.php",
            "config\\internal.sqlite",
            "config\\remote_update.json",
            "logs\\",
            "phpcs.xml",
            "phpstan.neon",
            "phpunit.xml.dist",
            "src\\Command\\DevelopersCommand.php",
            "src\\Command\\PingPongCommand.php",
            "src\\Command\\ReleasesCommand.php",
            "src\\Controller\\DevelopersController.php",
            "src\\Controller\\ReleasesController.php",
            "templates\\Developers\\",
            "templates\\element\\sidenav_developer.php",
            "templates\\Releases\\",
            "templates\\plugin\\bake\\",
            "tests\\",
            "tmp\\",

            //Remove the Foo MVC used to check how masses of data looks in the GUI
            "config\\Migrations\\20220910120050_CreateFooAuthorsRecipes.php",
            "src\\Controller\\FooAuthorsController.php",
            "src\\Controller\\FooIngredientsController.php",
            "src\\Controller\\FooMethodsController.php",
            "src\\Controller\\FooRecipesController.php",
            "src\\Controller\\FooTagsController.php",
            "src\\Model\\Entity\\FooAuthor.php",
            "src\\Model\\Entity\\FooIngredient.php",
            "src\\Model\\Entity\\FooMethod.php",
            "src\\Model\\Entity\\FooRecipe.php",
            "src\\Model\\Entity\\FooTag.php",
            "src\\Model\\Table\\FooAuthorsTable.php",
            "src\\Model\\Table\\FooIngredientsTable.php",
            "src\\Model\\Table\\FooMethodsTable.php",
            "src\\Model\\Table\\FooRecipesTable.php",
            "src\\Model\\Table\\FooTagsTable.php",
            "templates\\FooAuthors\\add.php",
            "templates\\FooAuthors\\edit.php",
            "templates\\FooAuthors\\index.php",
            "templates\\FooAuthors\\view.php",
            "templates\\FooIngredients\\add.php",
            "templates\\FooIngredients\\edit.php",
            "templates\\FooIngredients\\index.php",
            "templates\\FooIngredients\\view.php",
            "templates\\FooMethods\\add.php",
            "templates\\FooMethods\\edit.php",
            "templates\\FooMethods\\index.php",
            "templates\\FooMethods\\view.php",
            "templates\\FooRecipes\\add.php",
            "templates\\FooRecipes\\edit.php",
            "templates\\FooRecipes\\index.php",
            "templates\\FooRecipes\\view.php",
            "templates\\FooTags\\add.php",
            "templates\\FooTags\\edit.php",
            "templates\\FooTags\\index.php",
            "templates\\FooTags\\view.php",
            "tests\\Fixture\\FooAuthorsFixture.php",
            "tests\\Fixture\\FooIngredientsFixture.php",
            "tests\\Fixture\\FooMethodsFixture.php",
            "tests\\Fixture\\FooRecipesFixture.php",
            "tests\\Fixture\\FooTagsFixture.php",
            "tests\\TestCase\\Controller\\FooAuthorsControllerTest.php",
            "tests\\TestCase\\Controller\\FooIngredientsControllerTest.php",
            "tests\\TestCase\\Controller\\FooMethodsControllerTest.php",
            "tests\\TestCase\\Controller\\FooRecipesControllerTest.php",
            "tests\\TestCase\\Controller\\FooTagsControllerTest.php",
            "tests\\TestCase\\Model\\Table\\FooAuthorsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooIngredientsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooMethodsTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooRecipesTableTest.php",
            "tests\\TestCase\\Model\\Table\\FooTagsTableTest.php",
        ];


        $gitIgnored = $GT->getIgnoredFiles();
        if ($gitIgnored) {
            $ignoreFilesFolders = array_merge($ignoreFilesFolders, $gitIgnored);
        }

        $rawFileList = $zipPackager->rawFileList($baseDir);
        $rawFileList = $zipPackager->filterOutFoldersAndFiles($rawFileList, $ignoreFilesFolders);
        $rawFileList = $zipPackager->filterOutVendorExtras($rawFileList);

        $specificFiles = [
            ".dockerignore",
            ".env.example",
            ".gitattributes",
            ".gitignore",
            ".gitkeep",
            ".md",
            ".MD",
            ".phpunit.result.cache",
            "composer.json",
            "composer.lock",
            "docker-compose.yml",
            "Dockerfile",
            "docs.Dockerfile",
            "empty",
            "LICENSE",
            "LICENSE.txt",
            "phpcs.xml",
            "phpunit.xml",
            "psalm-baseline.xml",
            "psalm.xml",
            "phpstan-baseline.neon",
            "phpstan.neon.dist",
            "README.md",
            "TODO",
            "VERSION.txt",
        ];
//        $specificFiles = [];
        $rawFileList = $zipPackager->filterOutByFileName($rawFileList, $specificFiles);

        foreach ($rawFileList as $k => $file) {
            if (TextFormatter::endsWith($file, '.php')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.svg')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.twig')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.css')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.map')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.png')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.js')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.jpg')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.otf')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.scss')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.woff2')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.ttf')) {
                unset($rawFileList[$k]);
            }
            if (TextFormatter::endsWith($file, '.less')) {
                unset($rawFileList[$k]);
            }
        }

        $toDebug['$rawFileList'] = $rawFileList;


        $this->set('toDebug', $toDebug);
    }

    public function auth()
    {
        dd(Router::fullBaseUrl());

//        $session = $this->request->getSession();
//        dump($session);
//        dump($this->AuthUser->user());
//        dump($this->Auth->logout());
//        dump($this->AuthUser->user());
//        $this->request->getSession()->destroy();
//        $this->request->getSession()->renew();
//        $session = $this->request->getSession();
//        dump($session);

    }

    public function router()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        $toDebug[] = Router::url(['prefix' => false, 'controller' => 'ConnectorWebHotFolders', 'action' => 'submit', 'hot-folder-name']);
        $toDebug[] = Router::url(['prefix' => false, 'controller' => 'HotFolders', 'action' => 'submit', 'hot-folder-name']);
        $toDebug[] = Router::url(['prefix' => false, 'controller' => 'UserHub', 'action' => 'login', 1234567]);
        $toDebug[] = Router::url(['prefix' => 'Administrators', 'controller' => '/']);

        $this->set('toDebug', $toDebug);

    }


    public function xmp()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $us = new ProcessUStoreOrders();
        $us->execute('C:\\HotFoldersDev\\FlashGraphicsOrderXml\\20240320-105905-78eltk1l0fc4o4oc');

        dd(date('Y-m-d H:i:s'));

        $this->set('toDebug', $toDebug);


    }


    public function woo()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $us = new ProcessWooCommerceOrders();
        $us->execute('M:\\GenericRepository\\_ApplicationWorksHotFolders\Process WooCommerce Order JSON\\woo_orders.json');

        dd(date('Y-m-d H:i:s'));

        $this->set('toDebug', $toDebug);


    }


    public function ojd()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


//        $OJD = new OrderManagementBase();
//        $sampleOrder = $OJD->getSampleOrderJobDoc();
//        $result = $OJD->loadOrder($sampleOrder);
//        $toDebug['$result'] = $result;

//        $WCOrdering = new WooCommerceOrdering();
//        $result = $WCOrdering->loadOrder("W:\\arajcany_Projects\\ApplicationWorks\\tests\\TestCase\\OrderManagement\\order_sample_woo_commerce.json");
//        $toDebug['$result'] = $result;

//        $UStoreOrdering = new uStoreOrdering();
//        $result = $UStoreOrdering->loadOrder("W:\\arajcany_Projects\\ApplicationWorks\\tests\\TestCase\\OrderManagement\\uStore-6173.xml");
//        $toDebug['$result'] = $result;

//        $result = $UStoreOrdering->uStoreDocumentDownload("https://flashexhibtions.ezportal.com.au/uStore/Controls/SDK/OrderOutputProxy.ashx?token=AE55C8FD-F103-48BA-9655-2E3C105A814E");
//        $toDebug['$result'] = $result;
//        if (!$result) {
//            $toDebug['$result-error'] = $UStoreOrdering->getAllAlertsLogSequence();
//        }


        //loading Photo Packages
        $packages = [
            TESTS . "TestCase/OrderManagement/PhotoPackages/FCIM_Pic_Pro_2/Hazel_4782.TXT",
            TESTS . "TestCase/OrderManagement/PhotoPackages/Fujifilm_C8/condition.txt",
            TESTS . "TestCase/OrderManagement/PhotoPackages/JDS/Nature_123456.jds",
            TESTS . "TestCase/OrderManagement/PhotoPackages/NTO/RIP12845.nto",
            TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles",
            //TESTS . "TestCase/OrderManagement/PhotoPackages/JobMaker/hazel_2779.txt",
        ];
        $packages = [
            TESTS . "TestCase/OrderManagement/PhotoPackages/FCIM_Pic_Pro_2/",
            TESTS . "TestCase/OrderManagement/PhotoPackages/Fujifilm_C8/",
            TESTS . "TestCase/OrderManagement/PhotoPackages/JDS/",
            TESTS . "TestCase/OrderManagement/PhotoPackages/NTO/",
            TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles/",
            //TESTS . "TestCase/OrderManagement/PhotoPackages/JobMaker/hazel_2779.txt",
        ];
        foreach ($packages as $file) {
            $PhotoPackageOrdering = new PhotoPackageOrdering();
            $order = $PhotoPackageOrdering->loadOrder($file);
            $toDebug[$file] = $order;
        }

//        //loading Photo single photo in folder
//        $file = TESTS . "TestCase/OrderManagement/PhotoPackages/SimpleFolderOfFiles/Chrysanthemum.jpg";
//        $PhotoPackageOrdering = new PhotoPackageOrdering();
//        $order = $PhotoPackageOrdering->loadOrder($file);

//        //loading Photo single photo in folder
//        $file = "C:\\WebAppsDev\\GenericRepo\\_InputTemp\\20240829-231041-nu6ueu2s-Desert 17249730412889\\Desert 17249730412889";
//        $PhotoPackageOrdering = new PhotoPackageOrdering();
//        $order = $PhotoPackageOrdering->loadOrder($file);
//        $toDebug['$order'] = $order;

//        /** @var DocumentsTable $Documents */
//        $Documents = TableRegistry::getTableLocator()->get('Documents');
//        $Documents->downloadDocument(8);

//        dd(ini_get('memory_limit'));

//        $largeFile = "C:\\Users\\arajcany\\Downloads\\METCQN24-59_Sign1.pdf";
//        $this->Artifacts->createArtifactFromUrl("METCQN24-59_Sign1.pdf", $largeFile);

        $this->set('toDebug', $toDebug);


    }


    public function ojdOutput()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        /** @var OrdersTable $OrdersTable */
        $OrdersTable = TableRegistry::getTableLocator()->get('Orders');
        /** @var JobsTable $JobsTable */
        $JobsTable = TableRegistry::getTableLocator()->get('Jobs');
        /** @var DocumentsTable $DocumentsTable */
        $DocumentsTable = TableRegistry::getTableLocator()->get('Documents');

        $artifacts = $OrdersTable->getArtifacts(61);
        $toDebug['$artifacts'] = $artifacts;

//        $artifacts = $JobsTable->getArtifacts(76); //76-86
//        $toDebug['$artifacts'] = $artifacts;

//        $artifacts = $DocumentsTable->getArtifacts(76); //76-86
//        $toDebug['$artifacts'] = $artifacts;

        $this->set('toDebug', $toDebug);


    }

    public function ustoreUpdate()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

//        /** @var OrdersTable $Orders */
//        $Orders = TableRegistry::getTableLocator()->get('Orders');
//
//        $url = "https://abcsigns.ezportal.com.au/uStore/Controls/SDK/OrderOutputProxy.ashx?token=84477181-6E01-407A-9C85-F33F5373935F";;
//        $url = "https://blaqdemo.s3.ap-southeast-2.amazonaws.com/output/AB38_16650_6000x1000_Blockout_TFL_14mm_2pg_QTYx1_Print_10%25.pdf";
//
//        $result = $Orders->Jobs->Documents->uStoreDocumentDownload($url);
//        if (isset($result['blob'])) {
//            $result['blob'] = substr($result['blob'], 0, 50);
//        }
//
//        $toDebug['$result'] = $result;
//        $toDebug['alerts'] = $Orders->Jobs->Documents->getAllAlertsLogSequence();

        $UStoreOrdering = new uStoreOrdering();
        $orderJsonData = "C:\\HotFoldersProd\\ABCSignsUStoreUpdateOrderJson\\20241204-050225-tti2ynvyc004okc8\\data.json";
        $result = $UStoreOrdering->updateOrder($orderJsonData);
        $toDebug['$result'] = $result;
        $toDebug['alerts'] = $UStoreOrdering->getAllAlertsLogSequence();

        $this->set('toDebug', $toDebug);
    }

    public function pdfOrdering()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        $folder = "C:\\Users\\andrew.rajcany\\Pictures\\PDF-Order-Multi-Level\\";
        $PdfOrdering = new PdfOrdering();
        $result = $PdfOrdering->loadOrder($folder);
        $toDebug['Multi'] = $result;

//        $folder = "C:\\Users\\andrew.rajcany\\Pictures\\PDF-Order-Single-Level\\";
//        $PdfOrdering = new PdfOrdering();
//        $result = $PdfOrdering->loadOrder($folder);
//        $toDebug['Single'] = $result;
//
//        $folder = "C:\\Users\\andrew.rajcany\\Pictures\\PDF-Order-Mixed-Level\\";
//        $PdfOrdering = new PdfOrdering();
//        $result = $PdfOrdering->loadOrder($folder);
//        $toDebug['Mixed'] = $result;

        $this->set('toDebug', $toDebug);
    }


    public function jdf()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $jdf = file_get_contents(TESTS . "TestCase/JDF/Sample_01.jdf");
        $jdf = Xml::toArray(Xml::build($jdf));
        //$toDebug['jdf'] = $jdf;


        $toDebug['hash'] = Hash::flatten($jdf, "/");


        /** @var DOMDocument $xml */
        $xml = Xml::fromArray($jdf, ['pretty' => true, 'return' => 'domdocument']);
        $toDebug['xml'] = h($xml->saveXML());


        $this->set('toDebug', $toDebug);
    }


    public function downloadDocument()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        /** @var DocumentsTable $DocumentsTable */
        $DocumentsTable = TableRegistry::getTableLocator()->get('Documents');
        $result = $DocumentsTable->downloadDocument(2);

        $toDebug['$result'] = $result;

        $this->set('toDebug', $toDebug);


    }


    public function codeWatcher()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        $Sweeper = new Sweeper();

        /** @var CodeWatcherProjectsTable $CodeWatcherProjects */
        $CodeWatcherProjects = TableRegistry::getTableLocator()->get('CodeWatcherProjects');

        ///** @var CodeWatcherProject[] $projects */
        //$projects = $CodeWatcherProjects->find('all')->where(['enable_tracking' => true]);
        //foreach ($projects as $project) {
        //    $rowCount = $Sweeper->captureFsoChanges($project->id);
        //}
        //$toDebug['$rowCount'] = $rowCount;

        $project = $CodeWatcherProjects->find('all')->first();
        $activity = $CodeWatcherProjects->getDailyActivityInTimeRange($project->id);

        $toDebug['$result'] = json_encode($activity);

        $this->set('toDebug', $toDebug);


    }


    public function crowdStrike()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $cmd = __("sc.exe query state= all | find \"SERVICE_NAME: CSFalconService\"");
        exec($cmd, $foundServices, $ret);

        if (!empty($foundServices)) {
            return true;
        }

        $this->set('toDebug', $toDebug);


    }


    public function cacert()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        $CACERT = new CACert();


        $toDebug['$result'] = $CACERT->getCertPath();

        $this->set('toDebug', $toDebug);


    }

    public function serviceStats()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        $BS = new BackgroundServicesAssistant();

        $s = microtimestamp('.');
        $toDebug['_getServicesStats'] = $BS->_getServicesStats();
        $e = microtimestamp('.');
        $toDebug['time'] = $e - $s;


        $this->set('toDebug', $toDebug);
    }

    public function scheduledTasks()
    {
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];


        /** @var ScheduledTasksTable $ScheduledTasks */
        $ScheduledTasks = TableRegistry::getTableLocator()->get('ScheduledTasks');


        $toDebug['$result'] = $ScheduledTasks->getEnabledScheduledTasksKeyedById();

        $this->set('toDebug', $toDebug);
    }

    public function timeHelper()
    {
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('time_helper');
        $toDebug = [];


        $this->set('toDebug', $toDebug);
    }

    public function sms()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $Sms = (new SmsGatewayFactory())->getSmsGateway();

        //send a message
//        $message = $this->Messages->newEmptyEntity();
//        $message->type = "sms";
//        $message->subject = "This is a test message from Application Works";
//        $message->email_to = "+61403121248";
//        $this->Messages->save($message);
//        $message = $Sms->sendSms($message);
//        $toDebug['$message'] = $message;


        //get the account balance
//        $balance = $Sms->getBalance();
//        $toDebug['$balance'] = $balance;


        //get a sent SMS
//        $sms = $Sms->getSms('EF94830D-8E3D-A17C-F9E2-6B49AFED4934');
//        $toDebug['$sms'] = $sms;

//        $list = (new SmsGatewayFactory())->getSmsGatewayClasses();
//        $toDebug['$list'] = $list;
//
//        $gateway = (new SmsGatewayFactory())->getSmsGateway();
//        $toDebug['$gateway'] = $gateway;

        $this->set('toDebug', $toDebug);
    }

    public function randomString()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $toDebug['random-string'] = \Cake\Utility\Security::randomString(256);

        $this->set('toDebug', $toDebug);
    }

    public function gravatar()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $Gravatar = new Gravatar();

        $email = 'arajcany@mac.com';
        $gravatarUrl = $Gravatar->buildGravatarURL($email);
        $toDebug[$email] = $gravatarUrl;

        $this->set('gravatarUrlBuild', $gravatarUrl);
        $this->set('gravatarUrlAuth', $this->AuthUser->user('gravatar_url'));

        $this->set('toDebug', $toDebug);
    }

    public function callas()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $Callas = new CallasCommands();

        $isALive = $Callas->isalive();
        $toDebug[$isALive] = $isALive;


        $this->set('toDebug', $toDebug);
        $this->set('toDebug', $toDebug);
    }

    public function packageReader()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $PackageReader = new PackageReader();

        $path = "C:\\WebAppsDev\\andrew\\ApplicationWorks\\tests\\TestCase\\OrderManagement\\IK-Server\\J07CEB37512701";
        //$path = "C:\\WebAppsDev\\andrew\\ApplicationWorks\\tests\\TestCase\\OrderManagement\\PhotoPackages\\FCIM_Pic_Pro_2";

        $masterFormat = $PackageReader->readToMasterFormat($path);
        //$masterFormat->setOrder_ID($path);

        $toDebug['$masterFormat'] = $masterFormat->getOrder_ID();

        $this->set('toDebug', $toDebug);
    }

    public function dateArray()
    {
        $this->viewBuilder()->setTemplate('to_debug');
        $toDebug = [];

        $dates = [
            'International' => [
                'j F Y' => '9 July 2024',
                'jS \o\f F Y' => '1st of December 2024',
            ],
            'Australian' => [
                'd/m/Y' => '19/07/2024 (day/month/year)',
                'd/m/y' => '19/07/24 (day/month/year)',
            ],
            'United States' => [
                'm/d/Y' => '09/27/2024 (month/day/year)',
                'm/d/y' => '09/27/24 (month/day/year)',
            ],
        ];

        $times = [
            '24 Hour' => [
                'H:i:s' => '13:30:45',
                'H:i' => '13:30',
            ],
            '12 Hour' => [
                'g:i a' => '1:30 pm',
                'h:i a' => '01:30 pm',
            ],
        ];

        $dt = [
            'International' => [
                'Y-m-d H:i:s' => '2024-07-09 13:30:00',
                'j F Y, g:i a' => '9 July 2024, 1:30 pm',
            ],
            'Australian' => [
                'd/m/Y h:i a' => '19/07/2024 01:30 pm (day/month/year)',
                'd/m/y g:i a' => '19/07/24 1:30 pm (day/month/year)',
            ],
            'United States' => [
                'm/d/Y h:i a' => '09/27/2024 01:30 pm (month/day/year)',
                'm/d/y g:i a' => '09/27/24 1:30 pm (month/day/year)',
            ],
        ];

//        echo date('Y-m-d H:i:s');     // Example output: 2024-07-09 13:30:00
//        echo date('j F Y, g:i a');     // Example output: 9 July 2024, 1:30 pm
//        echo date('d/m/Y H:i:s');     // Example output: 09/07/2024 13:30:00
//        echo date('d/m/Y h:i:s a');   // Example output: 09/07/2024 01:30:00 pm

        dd(json_encode($dt, JSON_PRETTY_PRINT));

        $this->set('toDebug', $toDebug);
    }


}
