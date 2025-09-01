<?php

namespace App\Utility\Feedback;

use App\Model\Table\ApplicationLogsTable;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Http\Session;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use League\CLImate\CLImate;

/**
 * This handy trait can be used for logging error inside an Object.
 * Often a Method needs to return a single value such as true/false.
 * However, you need to know why true/false was returned.
 * You can use this trait to log a message before you return the value,
 * then you can call getAllAlerts() to get an array of all the alerts
 * that were raised.
 *
 * Alerts match the Bootstrap CSS framework levels so that you can
 * appropriately style the alert in the GUI
 *
 */
trait ReturnAlerts
{
    private string $classOwner = '';
    private array $successAlerts = [];
    private array $dangerAlerts = [];
    private array $warningAlerts = [];
    private array $infoAlerts = [];

    //often when running in CLI, a single return value and message are needed.
    private int $returnValue = 0;
    private string $returnMessage = '';

    //outputting to various places
    private bool|ConsoleIo|CLImate $ioCli = false;
    private bool|string $ioJson = false;
    private bool|ApplicationLogsTable $ioDatabase = false;
    private bool|Session $ioSession = false;
    private int $ioAutoClear = 2;


    /**
     * Writes Return Alerts to the console if set
     *
     * @param bool|CLImate|ConsoleIo $ioCli
     * @return void
     */
    public function setIoCli(bool|CLImate|ConsoleIo $ioCli): void
    {
        $this->ioCli = $ioCli;
    }

    /**
     * Writes Return Alerts to a JSON file if set
     *
     * @param bool|string $ioJson
     * @return void
     */
    public function setIoJson(bool|string $ioJson): void
    {
        $defaultJsonFile = 'return_alerts.json';

        if ($ioJson === true) {
            $ioJson = TextFormatter::makeDirectoryTrailingSmartSlash(LOGS) . $defaultJsonFile;
            if (!is_file($ioJson)) {
                file_put_contents($ioJson, '{}');
            }
            $this->ioJson = $ioJson;
            return;
        } else {
            if (is_dir($ioJson)) {
                $ioJson = TextFormatter::makeDirectoryTrailingSmartSlash($ioJson) . $defaultJsonFile;
                if (!is_file($ioJson)) {
                    file_put_contents($ioJson, '{}');
                }
                $this->ioJson = $ioJson;
                return;
            } elseif (is_file($ioJson)) {
                $this->ioJson = $ioJson;
                return;
            } else {
                $dirPart = pathinfo($ioJson, PATHINFO_DIRNAME);
                if (is_dir($dirPart)) {
                    //directory exists
                    if (is_writable($dirPart)) {
                        //directory is writable but file does not exist so create it
                        file_put_contents($ioJson, '{}');
                        $this->ioJson = $ioJson;
                        return;
                    }
                }
            }
        }

        $this->ioJson = false;
    }

    /**
     * Writes Return Alerts to the database if set
     *
     * @param ApplicationLogsTable|bool $ioDatabase
     * @return void
     */
    public function setIoDatabase(ApplicationLogsTable|bool $ioDatabase): void
    {
        if ($ioDatabase === true) {
            /** @var ApplicationLogsTable $ApplicationLogs */
            $ioDatabase = TableRegistry::getTableLocator()->get('ApplicationLogs');
        }

        $this->ioDatabase = $ioDatabase;
    }

    /**
     * Writes Return Alerts to the Session if set
     * Force written if Debug::mode is true
     *
     * @param bool $ioSession
     * @return void
     */
    public function setIoSession(bool $ioSession): void
    {
        if ($ioSession === true && !$this->ioSession) {
            $this->ioSession = new Session();
        } elseif ($ioSession === true && $this->ioSession instanceof Session) {
            return;
        } else {
            $this->ioSession = false;
        }
    }

    /**
     * @param int $ioAutoClear
     */
    public function setIoAutoClear(int $ioAutoClear): void
    {
        $this->ioAutoClear = $ioAutoClear;
    }

    /**
     * Ultra-fine micro time
     *
     * @param string $separator
     * @return string
     */
    private function getMicrotime(string $separator = ''): string
    {
        $mt = microtime();
        $mt = explode(" ", $mt);
        $unixTS = $mt[1];
        $microParts = explode(".", $mt[0]);

        return "{$unixTS}{$separator}{$microParts[1]}";
    }


    /**
     * @param int $returnValue
     */
    public function setReturnValue(int $returnValue): void
    {
        $this->returnValue = $returnValue;
    }

    /**
     * @return int
     */
    public function getReturnValue(): int
    {
        return $this->returnValue;
    }

    /**
     * @param string $returnMessage
     */
    public function setReturnMessage(string $returnMessage): void
    {
        $this->returnMessage = $returnMessage;
    }

    /**
     * @return string
     */
    public function getReturnMessage(): string
    {
        return $this->returnMessage;
    }

    /**
     * Return to default state
     *
     * @return void
     */
    public function clearAllReturnAlerts(): void
    {
        $this->returnValue = 0;
        $this->returnMessage = '';
        $this->successAlerts = [];
        $this->dangerAlerts = [];
        $this->warningAlerts = [];
        $this->infoAlerts = [];
    }

    /**
     * Return Alerts in their base array format.
     *
     * NOTE: this delivers the alerts out of sequence - they are grouped by level.
     *
     * @return array
     */
    public function getAllAlerts(): array
    {
        return [
            'success' => array_values($this->successAlerts),
            'danger' => array_values($this->dangerAlerts),
            'warning' => array_values($this->warningAlerts),
            'info' => array_values($this->infoAlerts),
        ];
    }

    /**
     * @return string
     */
    public function getHighestAlertLevel(): string
    {
        if (!empty($this->dangerAlerts)) {
            $status = 'danger';
        } elseif (!empty($this->warningAlerts)) {
            $status = 'warning';
        } elseif (!empty($this->infoAlerts)) {
            $status = 'info';
        } else {
            $status = 'success';
        }

        return $status;
    }

    /**
     * Return Alerts ready for a mass into a log style table.
     *
     * @param string $levelFieldName
     * @param string $messageFieldName
     * @return array
     */
    public function getAllAlertsForMassInsert(string $levelFieldName = 'level', string $messageFieldName = 'message'): array
    {
        $compiled = [];

        foreach ($this->successAlerts as $timestamp => $message) {
            $parts = explode(".", $timestamp, 2);
            $ts = isset($parts[0]) ? intval($parts[0]) : 0;
            $ms = $parts[1] ?? '000000';
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", $ts) . "." . $ms,
                $levelFieldName => 'success',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->dangerAlerts as $timestamp => $message) {
            $parts = explode(".", $timestamp, 2);
            $ts = isset($parts[0]) ? intval($parts[0]) : 0;
            $ms = $parts[1] ?? '000000';
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", $ts) . "." . $ms,
                $levelFieldName => 'danger',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->warningAlerts as $timestamp => $message) {
            $parts = explode(".", $timestamp, 2);
            $ts = isset($parts[0]) ? intval($parts[0]) : 0;
            $ms = $parts[1] ?? '000000';
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", $ts) . "." . $ms,
                $levelFieldName => 'warning',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->infoAlerts as $timestamp => $message) {
            $parts = explode(".", $timestamp, 2);
            $ts = isset($parts[0]) ? intval($parts[0]) : 0;
            $ms = $parts[1] ?? '000000';
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", $ts) . "." . $ms,
                $levelFieldName => 'info',
                $messageFieldName => $message,
            ];
        }

        ksort($compiled);
        return $compiled;
    }

    /**
     * Return alerts in more like a standard log file format.
     * Still an array where every entry needs to be written as a line to file.
     *
     * @return array
     */
    public function getAllAlertsLogSequence(): array
    {
        $compiled = [];

        $formatLogLine = function ($timestamp, $message, $level) {
            $parts = explode(".", $timestamp, 2);
            $ts = isset($parts[0]) ? intval($parts[0]) : 0;
            $ms = $parts[1] ?? '000000';

            return [$timestamp, date("Y-m-d H:i:s", $ts) . ".{$ms} " . strtoupper($level) . ": " . $message];
        };

        foreach ($this->successAlerts as $timestamp => $message) {
            [$ts, $line] = $formatLogLine($timestamp, $message, 'success');
            $compiled[$ts] = $line;
        }

        foreach ($this->dangerAlerts as $timestamp => $message) {
            [$ts, $line] = $formatLogLine($timestamp, $message, 'danger');
            $compiled[$ts] = $line;
        }

        foreach ($this->warningAlerts as $timestamp => $message) {
            [$ts, $line] = $formatLogLine($timestamp, $message, 'warning');
            $compiled[$ts] = $line;
        }

        foreach ($this->infoAlerts as $timestamp => $message) {
            [$ts, $line] = $formatLogLine($timestamp, $message, 'info');
            $compiled[$ts] = $line;
        }

        ksort($compiled);
        return array_values($compiled);
    }

    /**
     * Get the Return Alerts for use in the $this->mergeAlerts()
     *
     * @return array
     */
    public function getAllAlertsForMerge(): array
    {
        return [
            'success' => $this->successAlerts,
            'danger' => $this->dangerAlerts,
            'warning' => $this->warningAlerts,
            'info' => $this->infoAlerts,
        ];
    }

    /**
     * Use this to merge alerts from two classes that have used Return Alerts.
     *
     * $this->mergeAlerts($OtherObject->getAllAlertForMerge());
     *
     * @param array $alerts
     * @return void
     */
    public function mergeAlerts(array $alerts): void
    {
        if ($alerts['success']) {
            $this->successAlerts = array_merge($this->successAlerts, $alerts['success']);
        }

        if ($alerts['danger']) {
            $this->dangerAlerts = array_merge($this->dangerAlerts, $alerts['danger']);
        }

        if ($alerts['warning']) {
            $this->warningAlerts = array_merge($this->warningAlerts, $alerts['warning']);
        }

        if ($alerts['info']) {
            $this->infoAlerts = array_merge($this->infoAlerts, $alerts['info']);
        }
    }

    /**
     * Merge in the Return Alerts from another object.
     * Saves a step as this method check if the other object uses Return Alerts.
     *
     * @param object $otherObject
     * @return void
     */
    public function mergeAlertsFromObject(object $otherObject): void
    {
        if (!method_exists($otherObject, 'getAllAlertsForMerge')) {
            return;
        }
        $alerts = $otherObject->getAllAlertsForMerge();
        $this->mergeAlerts($alerts);
    }

    /**
     * @return array
     */
    public function getSuccessAlerts(): array
    {
        return $this->successAlerts;
    }

    /**
     * @return array
     */
    public function getDangerAlerts(): array
    {
        return $this->dangerAlerts;
    }

    /**
     * @return array
     */
    public function getWarningAlerts(): array
    {
        return $this->warningAlerts;
    }

    /**
     * @return array
     */
    public function getInfoAlerts(): array
    {
        return $this->infoAlerts;
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addSuccessAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'successAlerts');
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addDangerAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'dangerAlerts');
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addWarningAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'warningAlerts');
    }

    /**
     * @param array|string $message
     * @return array
     */
    public function addInfoAlerts(array|string $message): array
    {
        return $this->_addAlert($message, 'infoAlerts');
    }

    /**
     * Try to add the right alert type based on the error string
     *
     * @param array|string $message
     * @return array
     */
    public function addSmartAlerts(array|string $message): array
    {
        if (is_string($message)) {
            $message = [$message];
        }

        foreach ($message as $item) {
            $level = $this->mapToBootstrapLevel($this->extractLevel($item));

            switch ($level) {
                case 'danger':
                    $this->addDangerAlerts($item);
                    break;
                case 'warning':
                    $this->addWarningAlerts($item);
                    break;
                case 'success':
                    $this->addSuccessAlerts($item);
                    break;
                case 'info':
                default:
                    $this->addInfoAlerts($item);
                    break;
            }
        }

        return $this->getAllAlerts();
    }

    /**
     * Map PSR-3 or descriptive level to Bootstrap alert level
     *
     * @param string $level
     * @return string
     */
    private function mapToBootstrapLevel(string $level): string
    {
        return match ($level) {
            'emergency', 'alert', 'critical', 'error', 'danger' => 'danger',
            'warning' => 'warning',
            'success' => 'success',
            default => 'info' //'notice', 'info', 'debug'
        };
    }

    /**
     * Set an alert with micro-timestamp as the key.
     *
     * @param array|string $messages
     * @param string $type
     * @return array
     */
    private function _addAlert(array|string $messages, string $type): array
    {
        /** @var array $this ->$type */

        if (is_string($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            //sometimes it might not be a string
            if (is_string($message)) {
                $stringMessage = $message;
            } else {
                $stringMessage = json_encode($message);
            }

            do {
                $microTime = $this->getMicrotime('.');
            } while (isset($this->$type[$microTime])); // retry until unique key found

            $this->$type[$microTime] = $stringMessage;

            if (is_cli() && $this->ioCli) {
                $this->ioCli->out($messages);
            }

            $this->_addAlertToIoJson($stringMessage, $type, $microTime);

            $this->_addAlertToIoDatabase($stringMessage, $type, $microTime);

            $this->_addAlertToIoSession($stringMessage, $type, $microTime);
        }

        return $this->$type;
    }

    private function _addAlertToIoJson(string $message, string $type, string $microTime, int $autoClearMinutes = null): void
    {
        if (!$this->ioJson) {
            return;
        }

        if (!is_file($this->ioJson)) {
            return;
        }

        if (!$autoClearMinutes) {
            $autoClearMinutes = $this->ioAutoClear;
        }

        $clearTime = $this->getMicrotime() - (60 * $autoClearMinutes); //clear entries in file older than X minutes

        $contents = file_get_contents($this->ioJson);
        $contents = json_decode($contents, JSON_OBJECT_AS_ARRAY);

        if (json_last_error() !== 0) {
            $contents = [];
        }

        if (!is_array($contents)) {
            $contents = [];
        }

        $level = $this->extractLevel($type);

        $contents[$level][$microTime] = $message;

        //clear old entries
        foreach ($contents as $levelName => $levelEntries) {
            foreach ($levelEntries as $key => $value) {
                if ($key < $clearTime) {
                    unset($contents[$levelName][$key]);
                }
            }
        }


        $contents = json_encode($contents, JSON_PRETTY_PRINT);
        file_put_contents($this->ioJson, $contents);
    }

    private function _addAlertToIoDatabase(string $message, string $type, string $microTime): void
    {
        if (!$this->ioDatabase) {
            return;
        }

        if (!$this->ioDatabase instanceof ApplicationLogsTable) {
            return;
        }

        $key = Security::randomString(16);

        if (!is_cli()) {
            $this->setIoSession(true);
            $id = $this->ioSession->read('Auth.User.id');
        } else {
            $id = 0;
        }

        $backtrace = debug_backtrace();
        array_shift($backtrace);
        array_shift($backtrace);
        $backtrace = $backtrace[0];
        $url = "[return-alert] Line:{$backtrace['line']} File:{$backtrace['file']}";

        $level = $this->extractLevel($type);

        $mtInt = explode(".", $microTime);

        $records[$key]['created'] = (new DateTime($mtInt[0]))->format("Y-m-d H:i:s");
        $records[$key]['expiration'] = (new DateTime())->addMonths(1)->format("Y-m-d H:i:s");
        $records[$key]['level'] = $level;
        $records[$key]['url'] = $url;
        $records[$key]['message'] = substr($message, 0, 850);
        $records[$key]['message_overflow'] = '';
        $records[$key]['user_link'] = $id;

        $this->ioDatabase->massInsert($records);
    }

    private function _addAlertToIoSession(string $message, string $type, string $microTime, int $autoClearMinutes = null): void
    {
        if (Configure::read('debug')) {
            $this->setIoSession(true);
        }

        if (!$this->ioSession) {
            return;
        }

        if (!$autoClearMinutes) {
            $autoClearMinutes = $this->ioAutoClear;
        }

        $clearTime = $this->getMicrotime() - (60 * $autoClearMinutes); //clear entries in Session older than X minutes

        $contents = $this->ioSession->read('ReturnAlerts.level', []);


        $level = $this->extractLevel($type);

        $contents[$level][$microTime] = $message;

        //clear old entries
        $contentsSequenced = [];
        foreach ($contents as $levelName => $levelEntries) {
            foreach ($levelEntries as $key => $value) {
                if ($key < $clearTime) {
                    unset($contents[$levelName][$key]);
                    continue;
                }
                $contentsSequenced[$key] = str_pad(strtoupper($levelName) . ": ", 9) . $value;
            }
        }

        ksort($contentsSequenced);

        $this->ioSession->write('ReturnAlerts.level', $contents);
        $this->ioSession->write('ReturnAlerts.sequence', $contentsSequenced);


    }

    /**
     * Extract the potential PSR-3 log level based on the contents of the string
     *
     * @param string $string
     * @return string One of: emergency, alert, critical, error, warning, notice, info, debug, success
     */
    private function extractLevel(string $string): string
    {
        $string = strtolower($string);

        if (str_contains($string, 'emergency')) {
            return 'emergency';
        } elseif (str_contains($string, 'alert')) {
            return 'alert';
        } elseif (str_contains($string, 'critical')) {
            return 'critical';
        } elseif (str_contains($string, 'error')) {
            return 'error';
        } elseif (str_contains($string, 'warning')) {
            return 'warning';
        } elseif (str_contains($string, 'notice')) {
            return 'notice';
        } elseif (str_contains($string, 'debug')) {
            return 'debug';
        } elseif (str_contains($string, 'info')) {
            return 'info';
        } elseif (str_contains($string, 'success')) {
            return 'success'; //not a PSR-3 log level but is used by Bootstrap
        }

        return 'info';
    }
}
