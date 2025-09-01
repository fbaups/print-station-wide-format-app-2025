<?php

namespace App\Utility\Feedback;

use App\Model\Table\ApplicationLogsTable;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Error\Debugger;
use Cake\Http\Session;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Security;
use League\CLImate\CLImate;
use function PHPUnit\Framework\stringContains;

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
     * Ultra-fine micro time
     *
     * @return string
     */
    private function getMicrotime(): string
    {
        $mt = explode(' ', microtime());
        return $mt[1] . "." . substr(explode(".", $mt[0])[1], 0, 6);
    }


    /**
     * @param int $returnValue
     */
    public function setReturnValue(int $returnValue)
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
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . "." . $ms,
                $levelFieldName => 'success',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->dangerAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . "." . $ms,
                $levelFieldName => 'danger',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->warningAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . "." . $ms,
                $levelFieldName => 'warning',
                $messageFieldName => $message,
            ];
        }

        foreach ($this->infoAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = [
                'created' => date("Y-m-d H:i:s", intval($timestamp)) . "." . $ms,
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

        foreach ($this->successAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} SUCCESS: {$message}";
        }

        foreach ($this->dangerAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} DANGER:  {$message}";
        }

        foreach ($this->warningAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} WARNING: {$message}";
        }

        foreach ($this->infoAlerts as $timestamp => $message) {
            $ms = explode(".", $timestamp)[1];
            $compiled[$timestamp] = date("Y-m-d H:i:s", intval($timestamp)) . ".{$ms} INFO:    {$message}";
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
        $alerts = $otherObject->getAllAlertsForMerge;
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
            if (str_contains(strtolower($item), 'error')) {
                $this->addDangerAlerts($item);
            } elseif (str_contains(strtolower($item), 'warning')) {
                $this->addWarningAlerts(__($item));
            } elseif (str_contains(strtolower($item), 'danger')) {
                $this->addDangerAlerts($item);
            } elseif (str_contains(strtolower($item), 'success')) {
                $this->addSuccessAlerts($item);
            } else {
                $this->addInfoAlerts(__($item));
            }
        }

        return $this->getAllAlerts();
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
            $microTime = $this->getMicrotime();
            $this->$type[$microTime] = $message;

            if (is_cli() && $this->ioCli) {
                $this->ioCli->out($messages);
            }

            $this->_addAlertToIoJson($message, $type, $microTime);

            $this->_addAlertToIoDatabase($message, $type, $microTime);
        }

        return $this->$type;
    }

    private function _addAlertToIoJson(string $message, string $type, string $microTime, $autoClearMinutes = 2): void
    {
        if (!$this->ioJson) {
            return;
        }

        if (!is_file($this->ioJson)) {
            return;
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

        $contents[$type][$microTime] = $message;

        //clear old entries
        foreach ($contents as $typeName => $typeValue) {
            foreach ($typeValue as $key => $value) {
                if ($key < $clearTime) {
                    unset($contents[$typeName][$key]);
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
            $session = new Session();
            $id = $session->read('Auth.User.id');
        } else {
            $id = 0;
        }

        $backtrace = debug_backtrace();
        array_shift($backtrace);
        array_shift($backtrace);
        $backtrace = $backtrace[0];
        $url = "[return-alert] Line:{$backtrace['line']} File:{$backtrace['file']}";

        if (str_contains($type, 'warning')) {
            $level = 'warning';
        } else if (str_contains($type, 'success')) {
            $level = 'success';
        } else if (str_contains($type, 'danger')) {
            $level = 'danger';
        } else if (str_contains($type, 'info')) {
            $level = 'info';
        } else {
            $level = 'info';
        }

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
}
