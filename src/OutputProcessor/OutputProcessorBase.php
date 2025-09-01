<?php

namespace App\OutputProcessor;

use App\Model\Table\ArtifactsTable;
use App\Model\Table\OrdersTable;
use App\Utility\Feedback\ReturnAlerts;
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
    protected array $orderStatusesList;
    protected array $jobStatusesList;
    protected array $documentStatusesList;

    protected int $counter = 1;
    protected int $counterPadded = 1;

    public function __construct()
    {
        $this->ioCli = new ConsoleIo();
        $this->Orders = TableRegistry::getTableLocator()->get('Orders');

        $this->orderStatusesList = $this->Orders->OrderStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
        $this->jobStatusesList = $this->Orders->Jobs->JobStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
        $this->documentStatusesList = $this->Orders->Jobs->Documents->DocumentStatuses->find('list', keyField: 'name', valueField: 'id')->toArray();
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
        ];

        if (!$this->getEpsonExecutablePath() && strtolower(Configure::read('mode')) !== 'dev') {
            unset($types['EpsonPrintAutomate']);
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

        return false;
    }

    public function getEpsonExecutablePath(): ?string
    {
        $epsonExec = "C:\\Program Files (x86)\\Epson Software\\Epson Print Automate\\EpsonPrintAutomateG.exe";

        return is_file($epsonExec) ? $epsonExec : null;
    }

    public function getPowerShellExecBasePath(): string
    {
        $basePath = "C:\\PsTools\\";

        if (!is_dir($basePath)) {
            @mkdir($basePath, 0777, true);
        }

        return $basePath;
    }

    public function getPowerShellExecPath($bit = 32): string
    {
        $basePath = $this->getPowerShellExecBasePath();

        $osBit = strlen(decbin(~0));
        if ($osBit === 64 && $bit === 64) {
            $powerShellExec = "{$basePath}PsExec64.exe";
        } else {
            $powerShellExec = "{$basePath}PsExec.exe";
        }

        return $powerShellExec;
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
     * Replaces double-braced variables
     * Note: The resulting path may still contain invalid option paths such as .filename (i.e. hidden) and level-up (i.e. /../)
     *
     * @param array $outputConfiguration
     * @return string
     */
    public function compileFilenameVariables(array $outputConfiguration = []): string
    {
        if (!isset($outputConfiguration['filenameBuilder'])) {
            return false;
        }

        if (empty($outputConfiguration['filenameBuilder'])) {
            return false;
        }

        $compiled = $outputConfiguration['filenameBuilder'];

        $replacementList = [
            '{{OrderID}}' => $outputConfiguration['filenameBuilderVars']['order']['id'] ?? '',
            '{{JobID}}' => $outputConfiguration['filenameBuilderVars']['job']['id'] ?? '',
            '{{DocumentID}}' => $outputConfiguration['filenameBuilderVars']['document']['id'] ?? '',

            '{{ExternalOrderNumber}}' => $outputConfiguration['filenameBuilderVars']['order']['external_order_number'] ?? '',
            '{{ExternalJobNumber}}' => $outputConfiguration['filenameBuilderVars']['job']['external_job_number'] ?? '',
            '{{ExternalDocumentNumber}}' => $outputConfiguration['filenameBuilderVars']['document']['external_document_number'] ?? '',

            '{{OrderName}}' => $outputConfiguration['filenameBuilderVars']['order']['name'] ?? '',
            '{{JobName}}' => $outputConfiguration['filenameBuilderVars']['job']['name'] ?? '',
            '{{DocumentName}}' => $outputConfiguration['filenameBuilderVars']['document']['name'] ?? '',
            '{{DocumentFileName}}' => $outputConfiguration['filenameBuilderVars']['document']['file_name'] ?? '',
            '{{DocumentFileExtension}}' => $outputConfiguration['filenameBuilderVars']['document']['file_extension'] ?? '',

            '{{OrderDescription}}' => $outputConfiguration['filenameBuilderVars']['order']['description'] ?? '',
            '{{JobDescription}}' => $outputConfiguration['filenameBuilderVars']['job']['description'] ?? '',
            '{{DocumentDescription}}' => $outputConfiguration['filenameBuilderVars']['document']['description'] ?? '',

            '{{Counter}}' => $outputConfiguration['filenameBuilderVars']['counter'] ?? $this->counterPadded,
            '{{DateStamp}}' => date("Y-m-d"),
            '{{TimeStamp}}' => date("H:i:s"),
            '{{DateTimeStamp}}' => (new DateTime())->setTimezone(LCL_TZ)->format("Y-m-d-H-i-s-u"),
            '{{YearNumber}}' => (new DateTime())->setTimezone(LCL_TZ)->format("Y"),
            '{{MonthNumber}}' => (new DateTime())->setTimezone(LCL_TZ)->format("m"),
            '{{DayNumber}}' => (new DateTime())->setTimezone(LCL_TZ)->format("d"),
            '{{MonthName}}' => (new DateTime())->setTimezone(LCL_TZ)->format("F"),
            '{{DayName}}' => (new DateTime())->setTimezone(LCL_TZ)->format("l"),
        ];

        //do all the replacements
        $compiled = str_replace(array_keys($replacementList), array_values($replacementList), $compiled);

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

        //replace GUIDs
        $pattern = '/\{\{GUID\}\}/';
        preg_match_all($pattern, $compiled, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $key => $subject) {
                $rndString = Security::guid();
                $compiled = preg_replace('/' . preg_quote($subject, '/') . '/', $rndString, $compiled, 1);
            }
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
        /** @var ArtifactsTable $ArtifactsTable */
        $ArtifactsTable = TableRegistry::getTableLocator()->get('Artifacts');
        $path = $ArtifactsTable->sanitizeFsoFilename($path);

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
