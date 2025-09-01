<?php

namespace App\OutputProcessor;

use App\Model\Entity\Artifact;
use App\Model\Entity\Document;
use App\Model\Entity\Job;
use App\Model\Entity\Order;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\ErrandsTable;
use App\Model\Table\OrdersTable;
use App\Utility\Feedback\ReturnAlerts;
use App\VendorIntegrations\Fujifilm\PressReady;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use phpseclib3\Net\SFTP;

class OutputProcessorBase
{
    use ReturnAlerts;

    protected Table|OrdersTable $Orders;
    protected Table|ArtifactsTable $Artifacts;
    protected Table|ErrandsTable $Errands;

    protected array $orderStatusesList;
    protected array $jobStatusesList;
    protected array $documentStatusesList;

    protected array $orderEntityCache = [];
    protected array $jobEntityCache = [];
    protected array $documentEntityCache = [];
    protected array $artifactEntityCache = [];

    protected bool $outputIsErrand = true;
    protected string $errandMode = 'sequential';
    protected null|int $errandIdToWaitFor = null;
    protected array $errandIds = [];
    protected string $errandGrouping = '';
    protected int $errandSuccessCounter = 0;
    protected int $errandFailCounter = 0;

    protected int|null $counter = null;
    protected int|null $counterPadded = null;

    public function __construct()
    {
        $this->ioCli = new ConsoleIo();
        $this->Orders = TableRegistry::getTableLocator()->get('Orders');
        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        $this->Errands = TableRegistry::getTableLocator()->get('Errands');

        $this->orderStatusesList = $this->Orders->OrderStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
        $this->jobStatusesList = $this->Orders->Jobs->JobStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
        $this->documentStatusesList = $this->Orders->Jobs->Documents->DocumentStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();

        $this->errandGrouping = sha1(Security::randomBytes(4096));
    }

    /**
     * @param bool $outputIsErrand
     * @return void
     */
    public function setOutputIsErrand(bool $outputIsErrand): void
    {
        $this->outputIsErrand = $outputIsErrand;
    }

    /**
     *
     * Errand can either be 'sequential' || 'parallel'
     * If sequential, the 'wait_for_link' option will be set
     * If parallel, all errands will process in parallel as the 'wait_for_link' is not set
     *
     * @param string $errandMode
     */
    public function setErrandMode(string $errandMode): void
    {
        if (in_array($errandMode, ['sequential', 's'])) {
            $this->errandMode = 'sequential';
        }
        if (in_array($errandMode, ['parallel', 'p'])) {
            $this->errandMode = 'parallel';
        }
    }

    /*
     * Used to reset Errands when Output Processors fails for legitimate reasons (e.g. FIFO needs to wait)
     */
    private bool $resetErrand = false;
    private int $resetErrandFutureOffsetSeconds = 5;
    private bool $resetErrandIncludeNotStartedInGroup = true;

    public function setResetErrandParams(bool $resetErrand, $resetErrandFutureOffsetSeconds = 5, $resetErrandIncludeNotStartedInGroup = true): void
    {
        $this->resetErrand = $resetErrand;
        $this->resetErrandFutureOffsetSeconds = $resetErrandFutureOffsetSeconds;
        $this->resetErrandIncludeNotStartedInGroup = $resetErrandIncludeNotStartedInGroup;
    }

    public function getResetErrandParams(): array
    {
        return [
            'reset' => $this->resetErrand,
            'offset' => $this->resetErrandFutureOffsetSeconds,
            'include' => $this->resetErrandIncludeNotStartedInGroup,
        ];
    }

    /**
     * @return array
     */
    public function getErrandIds(): array
    {
        return $this->errandIds;
    }

    /**
     * @return int
     */
    public function getErrandSuccessCounter(): int
    {
        return $this->errandSuccessCounter;
    }

    /**
     * @return int
     */
    public function getErrandFailCounter(): int
    {
        return $this->errandFailCounter;
    }

    /**
     * @return string
     */
    public function getErrandGrouping(): string
    {
        return $this->errandGrouping;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getOrderStatusIdByName($name): mixed
    {
        return $this->orderStatusesList[$name] ?? null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getJobStatusIdByName($name): mixed
    {
        return $this->jobStatusesList[$name] ?? null;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getDocumentStatusIdByName($name): mixed
    {
        return $this->documentStatusesList[$name] ?? null;
    }

    /**
     * @param int $id
     * @return Order|false
     */
    public function getOrderEntity(int $id): Order|false
    {
        if (isset($this->orderEntityCache[$id])) {
            return $this->orderEntityCache[$id];
        }

        return $this->Orders->asEntity($id);
    }

    /**
     * @param int $id
     * @return Job|false
     */
    public function getJobEntity(int $id): Job|false
    {
        if (isset($this->jobEntityCache[$id])) {
            return $this->jobEntityCache[$id];
        }

        return $this->Orders->Jobs->asEntity($id);
    }

    /**
     * @param int $id
     * @return Document|false
     */
    public function getDocumentEntity(int $id): Document|false
    {
        if (isset($this->documentEntityCache[$id])) {
            return $this->documentEntityCache[$id];
        }

        return $this->Orders->Jobs->Documents->asEntity($id);
    }

    /**
     * @param int $id
     * @return Artifact|false
     */
    public function getArtifactEntity(int $id): Artifact|false
    {
        if (isset($this->artifactEntityCache[$id])) {
            return $this->artifactEntityCache[$id];
        }

        return $this->Artifacts->asEntity($id);
    }

    /**
     * @return string[]
     */
    public function getOutputProcessorTypes(): array
    {
        //once the keys have been created, do not change as they are stored in the DB against an OutputProcessor Entity
        $types = [
            'Folder' => 'Folder',
            'sFTP' => 'Secure FTP',
            'BackblazeBucket' => 'Backblaze Bucket',
            'EpsonPrintAutomate' => 'Epson Print Automate',
            'FujifilmXmfPressReadyPdfHotFolder' => 'Fujifilm XMF Press Ready PDF Hot Folder',
            'FujifilmXmfPressReadyCsvHotFolder' => 'Fujifilm XMF Press Ready CSV Hot Folder',
            //'FujifilmXmfPressReadyJdf' => 'Fujifilm XMF Press Ready JDF Submission (TBA)',
        ];

        if (!$this->getEpsonExecutablePath() && strtolower(Configure::read('mode')) !== 'dev') {
            unset($types['EpsonPrintAutomate']);
        }

        $PressReady = new PressReady();
        $pressReadyPdfHotFolders = $PressReady->getPdfHotFolders();
        $pressReadyCsvHotFolders = $PressReady->getCsvHotFolders();
        if (empty($pressReadyPdfHotFolders)) {
            unset($types['FujifilmXmfPressReadyPdfHotFolder']);
        }
        if (empty($pressReadyCsvHotFolders)) {
            unset($types['FujifilmXmfPressReadyCsvHotFolder']);
        }

        return $types;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    /**
     * @param $name
     * @return false|string
     */
    public function getOutputProcessorTypeClassPathByName($name): false|string
    {
        if (strtolower($name) === 'folder') {
            return '\App\OutputProcessor\FolderOutputProcessor';
        }
        if (strtolower($name) === 'sftp') {
            return '\App\OutputProcessor\sFTPOutputProcessor';
        }
        if (strtolower($name) === 'backblazebucket') {
            return '\App\OutputProcessor\BackblazeBucketOutputProcessor';
        }
        if (strtolower($name) === 'epsonprintautomate') {
            return '\App\OutputProcessor\EpsonPrintAutomateOutputProcessor';
        }
        if (strtolower($name) === 'fujifilmxmfpressreadypdfhotfolder') {
            return '\App\OutputProcessor\FujifilmXmfPressReadyPdfHotFolderProcessor';
        }
        if (strtolower($name) === 'fujifilmxmfpressreadycsvhotfolder') {
            return '\App\OutputProcessor\FujifilmXmfPressReadyCsvHotFolderProcessor';
        }
        if (strtolower($name) === 'fujifilmxmfpressreadyjdf') {
            return '\App\OutputProcessor\FujifilmXmfPressReadyJdfProcessor';
        }

        return false;
    }

    public function getEpsonExecutablePath(): ?string
    {
        $epsonExec = "C:\\Program Files (x86)\\Epson Software\\Epson Print Automate\\EpsonPrintAutomateG.exe";

        return is_file($epsonExec) ? $epsonExec : null;
    }

    public function getDefaultOutputConfiguration(): array
    {
        return [
            'filenameBuilder' => false,                  //if supplied, will be used as the filename
            'filenameOptions' => 'original',
            'prefixOrderId' => false,                    //if supplied, will be used in the prefix of the filename
            'prefixJobId' => false,                      //if supplied, will be used in the prefix of the filename
            'prefixDocumentId' => false,                 //if supplied, will be used in the prefix of the filename
            'prefixExternalOrderNumber' => false,        //if supplied, will be used in the prefix of the filename
            'prefixExternalJobNumber' => false,          //if supplied, will be used in the prefix of the filename
            'prefixExternalDocumentNumber' => false,     //if supplied, will be used in the prefix of the filename
        ];
    }

    public function compileOjdPrefix($outputConfiguration = []): string
    {
        $ojdPrefix = '';

        if (!empty($outputConfiguration['prefixOrderId']) && isStringOrNumber($outputConfiguration['prefixOrderId'])) {
            $ojdPrefix .= trim($outputConfiguration['prefixOrderId']) . "-";
        }

        if (!empty($outputConfiguration['prefixJobId']) && isStringOrNumber($outputConfiguration['prefixJobId'])) {
            $ojdPrefix .= trim($outputConfiguration['prefixJobId']) . "-";
        }

        if (!empty($outputConfiguration['prefixDocumentId']) && isStringOrNumber($outputConfiguration['prefixDocumentId'])) {
            $ojdPrefix .= trim($outputConfiguration['prefixDocumentId']) . "-";
        }

        if (!empty($outputConfiguration['prefixExternalOrderNumber']) && isStringOrNumber($outputConfiguration['prefixExternalOrderNumber'])) {
            $ojdPrefix .= trim($outputConfiguration['prefixExternalOrderNumber']) . "-";
        }

        if (!empty($outputConfiguration['prefixExternalJobNumber']) && isStringOrNumber($outputConfiguration['prefixExternalJobNumber'])) {
            $ojdPrefix .= trim($outputConfiguration['prefixExternalJobNumber']) . "-";
        }

        if (!empty($outputConfiguration['prefixExternalDocumentNumber']) && isStringOrNumber($outputConfiguration['prefixExternalDocumentNumber'])) {
            $ojdPrefix .= trim($outputConfiguration['prefixExternalDocumentNumber']) . "-";
        }

        if (strlen($ojdPrefix) > 0) {
            $ojdPrefix = trim($ojdPrefix, "- ");
        }

        return "[$ojdPrefix] ";
    }

    /**
     * Convenience function
     *
     * @param array $outputConfiguration
     * @return string
     */
    public function compileFilenameVariable(array $outputConfiguration = []): string
    {
        if (!isset($outputConfiguration['filenameBuilder'])) {
            return false;
        }

        if (empty($outputConfiguration['filenameBuilder'])) {
            return false;
        }

        return $this->compileVariable($outputConfiguration['filenameBuilder'], $outputConfiguration);
    }

    /**
     *
     * Replaces double-braced variables in the given string
     * Note: The resulting path may still contain invalid option paths such as .filename (i.e. hidden) and level-up (i.e. /../)
     *
     * @param string $doubleBracedString
     * @param array $outputConfiguration
     * @param string $fallback
     * @return string
     */
    public function compileVariable(string $doubleBracedString, array $outputConfiguration = [], string $fallback = ''): string
    {
        $compiled = $doubleBracedString;
        $currenDT = (new DateTime())->setTimezone(LCL_TZ);

        $replacementList = [
            '{{OrderID}}' => $outputConfiguration['filenameBuilderVars']['order']['id'] ?? '',
            '{{ExternalOrderNumber}}' => $outputConfiguration['filenameBuilderVars']['order']['external_order_number'] ?? '',
            '{{OrderName}}' => $outputConfiguration['filenameBuilderVars']['order']['name'] ?? '',
            '{{OrderDescription}}' => $outputConfiguration['filenameBuilderVars']['order']['description'] ?? '',
            '{{OrderQuantity}}' => $outputConfiguration['filenameBuilderVars']['order']['order_quantity'] ?? '',

            '{{JobID}}' => $outputConfiguration['filenameBuilderVars']['job']['id'] ?? '',
            '{{ExternalJobNumber}}' => $outputConfiguration['filenameBuilderVars']['job']['external_job_number'] ?? '',
            '{{JobName}}' => $outputConfiguration['filenameBuilderVars']['job']['name'] ?? '',
            '{{JobDescription}}' => $outputConfiguration['filenameBuilderVars']['job']['description'] ?? '',
            '{{JobQuantity}}' => $outputConfiguration['filenameBuilderVars']['job']['job_quantity'] ?? '',

            '{{DocumentID}}' => $outputConfiguration['filenameBuilderVars']['document']['id'] ?? '',
            '{{ExternalDocumentNumber}}' => $outputConfiguration['filenameBuilderVars']['document']['external_document_number'] ?? '',
            '{{DocumentName}}' => $outputConfiguration['filenameBuilderVars']['document']['name'] ?? '',
            '{{DocumentDescription}}' => $outputConfiguration['filenameBuilderVars']['document']['description'] ?? '',
            '{{DocumentQuantity}}' => $outputConfiguration['filenameBuilderVars']['document']['document_quantity'] ?? '',

            '{{DocumentFileName}}' => $outputConfiguration['filenameBuilderVars']['document']['file_name'] ?? '',
            '{{DocumentFileExtension}}' => $outputConfiguration['filenameBuilderVars']['document']['file_extension'] ?? '',

            '{{ArtifactID}}' => $outputConfiguration['filenameBuilderVars']['artifact']['id'] ?? '',
            '{{ArtifactName}}' => $outputConfiguration['filenameBuilderVars']['artifact']['name'] ?? '',
            '{{ArtifactDescription}}' => $outputConfiguration['filenameBuilderVars']['artifact']['description'] ?? '',
            '{{UNC}}' => $outputConfiguration['filenameBuilderVars']['artifact']['unc'] ?? '',
            '{{URL}}' => $outputConfiguration['filenameBuilderVars']['artifact']['url'] ?? '',
            '{{MimeType}}' => $outputConfiguration['filenameBuilderVars']['artifact']['mime_type'] ?? '',

            '{{TotalQuantity}}' => $outputConfiguration['filenameBuilderVars']['document']['total_quantity'] ?? '',

            '{{Counter}}' => $outputConfiguration['filenameBuilderVars']['counter'] ?? $this->counterPadded,
            '{{DateStamp}}' => date("Y-m-d"),
            '{{TimeStamp}}' => date("H:i:s"),
            '{{DateTimeStamp}}' => $currenDT->format("Y-m-d-H-i-s-u"),
            '{{YearNumber}}' => $currenDT->format("Y"),
            '{{MonthNumber}}' => $currenDT->format("m"),
            '{{DayNumber}}' => $currenDT->format("d"),
            '{{MonthName}}' => $currenDT->format("F"),
            '{{DayName}}' => $currenDT->format("l"),
        ];

        //do all the replacements
        $replacementIn = array_keys($replacementList);
        $replacementOut = array_values($replacementList);
        $compiled = str_replace($replacementIn, $replacementOut, $compiled);

        //return fallback if empty
        if (empty($compiled)) {
            return $fallback;
        }

        //limit of 16 digits in random number
        $pattern = '/\{\{RandomNumber(\d+)\}\}/';
        preg_match_all($pattern, $compiled, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $key => $subject) {
                $rndLength = intval($matches[1][$key]);
                $start = str_pad('', $rndLength, '1');
                $start = min($start, 1111111111111111);
                $end = str_pad('', $rndLength, '9');
                $end = min($end, 9999999999999999);
                $rndNumber = mt_rand($start, $end);
                $compiled = preg_replace('/' . preg_quote($subject, '/') . '/', $rndNumber, $compiled, 1);
            }
        }

        //return fallback if empty
        if (empty($compiled)) {
            return $fallback;
        }

        //limit of 16 characters in random string
        $pattern = '/\{\{RandomString(\d+)\}\}/';
        preg_match_all($pattern, $compiled, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $key => $subject) {
                $rndLength = intval($matches[1][$key]);
                $rndLength = min($rndLength, 16);
                $rndString = Security::purl($rndLength);
                $compiled = preg_replace('/' . preg_quote($subject, '/') . '/', $rndString, $compiled, 1);
            }
        }

        //return fallback if empty
        if (empty($compiled)) {
            return $fallback;
        }

        //replace GUIDs
        $pattern = '/\{\{GUID\}\}/';
        preg_match_all($pattern, $compiled, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $key => $subject) {
                $rndString = Security::guid();
                $compiled = preg_replace('/' . preg_quote($subject, '/') . '/', $rndString, $compiled, 1);
            }
        }

        //return fallback if empty
        if (empty($compiled)) {
            return $fallback;
        }

        return $compiled;
    }

    /**
     * For a fully qualified path:
     *  - Remove level-up syntax /../
     *  - Stop .hidden files (remove the dot)
     *
     * @param $path
     * @return string
     */
    public function tidyPath($path): string
    {
        $isWindowsPath = preg_match('/^[a-zA-Z]:/', $path, $matches);

        if ($isWindowsPath) {
            $pathStarter = $matches[0];
            $pathStarterSafe = "~~~starter~~~";
            $ds = "\\";
            $dsWrong = "/";
        } else {
            $pathStarter = "//";
            $pathStarterSafe = "~~~starter~~~";
            $ds = "/";
            $dsWrong = "\\";
        }
        $path = str_replace($dsWrong, $ds, $path);
        $path = preg_replace('/' . preg_quote($pathStarter, '/') . '/', $pathStarterSafe, $path, 1);

        //remove 'up level' directories
        $dirtyDS = [
            "{$ds}..{$ds}",
            "..{$ds}",
            "{$ds}{$ds}",
            "{$ds}.",
            ".{$ds}",
        ];
        $cleanDS = [
            "{$ds}",
            "{$ds}",
            "{$ds}",
            "{$ds}",
            "{$ds}",
        ];

        $isDone = false;
        while (!$isDone) {
            $before = $path;
            $path = str_replace($dirtyDS, $cleanDS, $path);
            $after = $path;
            $isDone = ($before === $after);
        }

        //replace valid directory separators
        $inDS = $ds;
        $outDS = '~~~DS~~~';
        $path = str_replace($inDS, $outDS, $path);

        //sanitise invalid characters
        $path = $this->Artifacts->sanitizeFsoFilename($path);

        //un-replace valid directory separators
        $path = str_replace($outDS, $inDS, $path);

        //trim of any excess characters
        $path = trim($path, ". \t\n\r\0\x0B");

        $path = str_replace($pathStarterSafe, $pathStarter, $path);

        return $path;
    }


    /**
     * Create a directory for a fully qualified file name
     *
     * @param string $pathWithFilename
     * @return bool
     */
    protected function mkdirLocalForFilename(string $pathWithFilename): bool
    {
        $pathOnly = pathinfo($pathWithFilename, PATHINFO_DIRNAME);

        if (is_dir($pathOnly)) {
            return true;
        }

        return mkdir($pathOnly, recursive: true);
    }


    /**
     * @param string $pathWithFilename
     * @param SFTP $sftp
     * @return bool
     */
    protected function mkdirSftpForFilename(string $pathWithFilename, SFTP $sftp): bool
    {
        $pathOnly = pathinfo($pathWithFilename, PATHINFO_DIRNAME);

        try {
            if ($sftp->is_dir($pathOnly)) {
                return true;
            }

            return $sftp->mkdir($pathOnly, recursive: true);
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
