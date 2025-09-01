<?php

namespace App\Utility\Storage;

use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Core\Configure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Throwable;

class UrlInspector
{
    use ReturnAlerts;

    private array $inspectionReport;

    public function __construct()
    {
    }

    /**
     * Test connectivity to a URL.
     *
     * A successful connection will return a true result, however, one should be careful
     * and check for the desired status code response in the $this->inspectionReport report.
     *
     * Example $settings =
     * [
     * 'http_host' => 'http://example.com',
     * 'http_port' => 443,
     * 'http_timeout' => 2,
     * 'http_method' => 'GET',
     * ]
     *
     * Note in the above settings, the http_host scheme and port conflict. In such cases the http_port overrides the
     * scheme in the URL.
     *
     * @param array $settings
     * @return bool
     */
    public function inspectUrlConnection(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultSftpSettings(), $settings);

        $this->inspectionReport = [
            'connection' => null,
            'http_code' => null
        ];

        $url = $settings['http_host'];
        if (isset($settings['http_port']) && !empty($settings['http_port'])) {
            $parts = parse_url($url);
            $scheme = $settings['http_port'] == 443 ? 'https' : 'http';
            $host = $parts['host'] ?? '';
            $path = $parts['path'] ?? '';
            $url = "$scheme://$host:$settings[http_port]$path";
        }

        $verify = true;
        $mode = strtolower(Configure::read('mode'));
        if (in_array($mode, ['dev', 'uat'])) {
            $this->addInfoAlerts(__("{0} is in {1} mode.", APP_NAME, strtoupper($mode)));
            $verify = false;
        }

        $clientOptions = [
            'timeout' => $settings['http_timeout'],
            'verify' => $verify,
        ];

        try {
            $client = new Client($clientOptions);
            $response = $client->request($settings['http_method'], $url);
            $statusCode = $response->getStatusCode();

            $this->inspectionReport['http_code'] = $statusCode;
            $this->inspectionReport['connection'] = ($statusCode >= 100 && $statusCode <= 599);

            if ($this->inspectionReport['connection']) {
                $this->addSuccessAlerts(__("Success, connected to {0} with HTTP status {1}.", $url, $statusCode));
            } else {
                $this->addWarningAlerts(__("Unexpected HTTP status code ({0}) from {1}.", $statusCode, $url));
            }

            return $this->inspectionReport['connection'];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($response && method_exists($response, 'getStatusCode')) {
                $statusCode = $response->getStatusCode();
                $this->inspectionReport['http_code'] = $statusCode;
                $this->inspectionReport['connection'] = true;
                $this->addWarningAlerts(__("Connection to {0} returned HTTP status {1}.", $url, $statusCode));
                return true;
            } else {
                $this->addDangerAlerts("Failed to get a valid HTTP response from {$url}.");
            }
        } catch (Throwable $e) {
            $this->addDangerAlerts("HTTP Inspection Error: {$e->getMessage()}");
        }

        $this->inspectionReport['connection'] = false;
        return false;
    }

    public function getDefaultSftpSettings(): array
    {
        return [
            'http_host' => null,
            'http_port' => null,
            'http_timeout' => 2,
            'http_method' => 'GET',
        ];
    }

    public function getInspectionReport(): array
    {
        return $this->inspectionReport;
    }
}
