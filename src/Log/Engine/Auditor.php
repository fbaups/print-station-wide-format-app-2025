<?php

namespace App\Log\Engine;

use App\Model\Table\ApplicationLogsTable;
use App\Model\Table\AuditsTable;
use App\Utility\Feedback\DebugCapture;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Session;
use Cake\I18n\DateTime;
use Cake\Log\Engine\BaseLog;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Exception;
use Throwable;

/**
 * Auditor class for logging events into the DB.
 *
 * Try to use this Class (as opposed to the Models) to write Auditable events.
 * This is because this Class auto-detects the user and automatically logs the Event against the User.
 *
 * There are 2 tables that are used to hold Auditing Information:
 * 1) ApplicationLogs Table (used error logging, 1:1 of Logs folder)
 *      Emergency: system is unusable
 *      Alert: action must be taken immediately
 *      Critical: critical conditions
 *      Error: error conditions
 *      Warning: warning conditions
 *      Notice: normal but significant condition
 *      Info: informational messages
 *      Debug: debug-level messages
 *
 * 2) Audits Table (event logging and use of the Application)
 *
 * @property ApplicationLogsTable $ApplicationLogs
 * @property AuditsTable $Audits
 * @property Session $Session
 */
class Auditor extends BaseLog
{
    protected ConnectionInterface $Connection;
    protected string|null $connectionDriver = null;

    public Table|ApplicationLogsTable $ApplicationLogs;
    public Table|AuditsTable $Audits;
    public bool $isDbReady = true;
    private ?Session $Session;

    /**
     * Auditor constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->Connection = ConnectionManager::get('default');
        $this->connectionDriver = $this->Connection->config()['driver'];

        if ($this->connectionDriver !== 'Dummy') {
            $this->ApplicationLogs = TableRegistry::getTableLocator()->get('ApplicationLogs');
            $this->Audits = TableRegistry::getTableLocator()->get('Audits');
        } else {
            $this->isDbReady = false;
        }

        if (!is_cli()) {
            try {
                $this->Session = Router::getRequest()->getSession();
            } catch (Throwable $exception) {
            }
        } else {
            $this->Session = null;
        }
    }

    /**
     * Mandatory implementation
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = []): void
    {
        $this->writeLog($level, $message, $context);
    }


    /**
     * This section deals with writing to the application_logs table
     */


    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logEmergency($message = null, array $context = [])
    {
        $this->writeLog('emergency', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logAlert($message = null, array $context = [])
    {
        $this->writeLog('alert', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logCritical($message = null, array $context = [])
    {
        $this->writeLog('critical', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logError($message = null, array $context = [])
    {
        $this->writeLog('error', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logWarning($message = null, array $context = [])
    {
        $this->writeLog('warning', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logNotice($message = null, array $context = [])
    {
        $this->writeLog('notice', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logInfo($message = null, array $context = [])
    {
        $this->writeLog('info', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function logDebug($message = null, array $context = [])
    {
        $this->writeLog('debug', $message, $context);
    }

    /**
     * Wrapper Function.
     *
     * @param null $level
     * @param null $message
     * @param array $context
     * @param null $expiration
     * @param int $user_link
     * @param null $url
     * @return bool
     */
    public function writeLog($level = null, $message = null, array $context = [], $expiration = null, int $user_link = 0, $url = null): bool
    {
        return $this->_write('application_logs', $level, $message, $context, $expiration, $user_link, $url);
    }


    /**
     * This section deals with writing to the audits table
     */


    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditEmergency($message = null, array $context = [])
    {
        $this->writeAudit('emergency', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditAlert($message = null, array $context = [])
    {
        $this->writeAudit('alert', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditCritical($message = null, array $context = [])
    {
        $this->writeAudit('critical', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditError($message = null, array $context = [])
    {
        $this->writeAudit('error', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditWarning($message = null, array $context = [])
    {
        $this->writeAudit('warning', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditNotice($message = null, array $context = [])
    {
        $this->writeAudit('notice', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditInfo($message = null, array $context = [])
    {
        $this->writeAudit('info', $message, $context);
    }

    /**
     * Convenience method
     *
     * @param null $message
     * @param array $context
     */
    public function auditDebug($message = null, array $context = [])
    {
        $this->writeAudit('debug', $message, $context);
    }

    /**
     * Wrapper Function.
     *
     * @param null $level
     * @param null $message
     * @param array $context
     * @param null $expiration
     * @param int $user_link
     * @param null $url
     * @return bool
     */
    public function writeAudit($level = null, $message = null, array $context = [], $expiration = null, int $user_link = 0, $url = null): bool
    {
        return $this->_write('audits', $level, $message, $context, $expiration, $user_link, $url);
    }


    /**
     * Main method of writing to the tables.
     * Use wrapper methods for more convenient logging
     *
     * @param string $table
     * @param null $level
     * @param null $message
     * @param array $context
     * @param null $expiration
     * @param int $user_link
     * @param null $url
     * @return bool
     */
    private function _write(string $table, $level = null, $message = null, array $context = [], $expiration = null, int $user_link = 0, $url = null): bool
    {
        if (!$this->isDbReady) {
            return false;
        }

        try {
            $inputDefault = $this->getDefaultData();

            //clean up input data
            $inputData = [];

            if ($level) {
                $inputData['level'] = trim($level);
            }

            if ($message) {
                if (!is_string($message)) {
                    $message = DebugCapture::captureDump($message);
                }
                $message = $this->interpolate($message, $context);
                $message = str_replace("\r\n", "\n", $message);
                $message = str_replace("\r", "\n", $message);

                //check if the message is too long
                $tooLong = 850;
                if (strlen($message) > $tooLong) {
                    //save the full message into the additional field
                    $inputData['message_overflow'] = $message;

                    //take the first line and check if still too long
                    $messageLines = explode("\n", $message);
                    if (strlen($messageLines[0]) > $tooLong) {
                        $messageShortened = substr($messageLines[0], 0, $tooLong);
                    } else {
                        $messageShortened = $messageLines[0];
                    }
                    $inputData['message'] = $messageShortened;
                } else {
                    $inputData['message'] = $message;
                }
            }

            if ($expiration) {
                $expiration = new DateTime($expiration);
                $inputData['expiration'] = $expiration;
            }

            if ($user_link) {
                $inputData['user_link'] = $user_link;
            }

            if ($url) {
                $inputData['url'] = trim($url);
            }

            //merge the data
            $inputData = array_merge($inputDefault, $inputData);

            //save the data
            if ($table === 'application_logs') {
                $entry = $this->ApplicationLogs->newEntity($inputData);
                $saveResult = $this->ApplicationLogs->save($entry);
            } elseif ($table === 'audits') {
                $entry = $this->Audits->newEntity($inputData);
                $saveResult = $this->Audits->save($entry);
            } else {
                return false;
            }

            if ($saveResult) {
                return true;
            } else {
                return false;
            }
        } catch (Throwable $exception) {
            return false;
        }
    }


    /**
     * Common default data.
     *
     * @return array
     */
    private function getDefaultData(): array
    {
        //no Session or User in CLI
        if (!is_cli()) {
            //web
            if ($this->Session) {
                $defaultUserId = $this->Session->read('Auth.User.id');
                $defaultUsername = $this->Session->read('Auth.User.username');
            } else {
                $defaultUserId = 0;
                $defaultUsername = '';
            }
            $defaultUrl = Router::url(null, true);
        } else {
            //cli
            $defaultUserId = 0;
            $defaultUsername = '';
            $defaultUrl = ''; //todo get the cmd that was called
        }

        try {
            $expiration = new DateTime('+ ' . Configure::read('Settings.audit_purge') . ' months');
        } catch (Exception $e) {
            $expiration = new DateTime('+ 1 months');
        }

        $inputDefault = [
            'level' => 'info',
            'expiration' => $expiration,
            'user_link' => $defaultUserId,
            'username' => $defaultUsername,
            'url' => $defaultUrl,
            'message' => '',
            'stack_trace' => '',
        ];

        return $inputDefault;
    }

}
