<?php

namespace App\Utility\Network;

use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Storage\UncInspector;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use Cake\Routing\Router;
use Exception;
use GuzzleHttp\Client;
use Throwable;

/**
 * Updated version of the original Connection class.
 * Removed static functions so that class can use ReturnAlerts
 *
 */
class NetworkConnection
{
    use ReturnAlerts;

    /**
     * Check if there is Internet connection to the requested host:port
     * NOTE: If you need to check a fully qualified URL use checkUrlConnection() instead
     *
     * @param string|null $host
     * @param string|null $port
     * @param int $timeout
     * @return bool
     */
    public function checkInternetConnection(string $host = null, string $port = null, int $timeout = 2): bool
    {
        //see if the network address:port is responding
        try {
            $fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
        } catch (Throwable $e) {
            $this->addDangerAlerts(__("Socket Open Error: {0}", $e->getMessage()));
            return false;
        }

        if (!$fsock) {
            $this->addDangerAlerts(__("Socket connection failed to create a resource."));
            return false;
        }

        fclose($fsock);
        return true;
    }


    /**
     * Check if there is Internet connection to the requested SMTP server
     *
     * @param string|null $host
     * @param string|null $port
     * @param int $timeout
     * @return bool
     */
    public function checkSmtpConnection(string $host = null, string $port = null, int $timeout = 2): bool
    {
        //see if the network address:port is responding
        try {
            $fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
        } catch (Throwable $e) {
            $this->addDangerAlerts(__("Socket Open Error: {0}", $e->getMessage()));
            return false;
        }

        if (!$fsock) {
            $this->addDangerAlerts(__("Socket connection failed to create a resource."));
            return false;
        }

        $response = fread($fsock, 3);
        if ($response != '220') {
            $this->addDangerAlerts(__("Socket connection did not return a 220 response."));
            return false;
        }

        fclose($fsock);
        return true;
    }

}
