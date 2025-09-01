<?php

namespace App\Utility\Network;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;


class Connection
{
    /**
     * Check if there is Internet connection to the requested host:port
     * NOTE: If you need to check a fully qualified URL use checkUrlConnection() instead
     *
     * @param null $host
     * @param null $port
     * @param int $timeout
     * @return bool
     */
    public static function checkInternetConnection($host = null, $port = null, $timeout = 2)
    {
        //see if the network address:port is responding
        try {
            $fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
        } catch (Exception $e) {
            return false;
        }

        if (!$fsock) {
            return false;
        }

        fclose($fsock);
        return true;
    }


    /**
     * Check if there is Internet connection to the requested SMTP server
     *
     * @param null $host
     * @param null $port
     * @param int $timeout
     * @return bool
     */
    public static function checkSmtpConnection($host = null, $port = null, $timeout = 2)
    {
        //see if the network address:port is responding
        try {
            $fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
        } catch (Exception $e) {
            return false;
        }

        if (!$fsock) {
            return false;
        }

        $response = fread($fsock, 3);
        if ($response != '220') {
            return false;
        }

        fclose($fsock);
        return true;
    }


    /**
     * Check if you can write to and delete from a UNC path
     *
     * @param null $uncPathWithTrailingSlash
     * @return bool
     */
    public static function checkUncConnection($uncPathWithTrailingSlash)
    {
        $rndFile = $uncPathWithTrailingSlash . mt_rand() . ".txt";
        $contentToWrite = 'test';
        $contentLength = strlen($contentToWrite);

        $writeResult = @file_put_contents($rndFile, $contentToWrite);
        $deleteResult = @unlink($rndFile);

        if (($writeResult == $contentLength) && $deleteResult) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Check if there is an Internet connection to http/https server.
     * Will use the cacert.pem file if present
     *
     * @param string $url
     * @param array $options
     * @return bool
     */
    public static function checkUrlConnection(string $url, array $options = []): bool
    {
        //determine if cacert.pem file is present
        $caPath = (new CACert())->getCertPath();
        if ($caPath) {
            $verify = $caPath;
        } else {
            $verify = true;
        }

        //relax $verify if localhost address
        $localhostAddresses = ['127.0.0.1', 'localhost', 'repo.pswf', 'app.pswf'];
        $host = parse_url($url)['host'];
        if (in_array($host, $localhostAddresses)) {
            $verify = false;
        } else {
            $host = explode(".", $host);
            $host = array_pop($host);
            if (in_array($host, $localhostAddresses)) {
                $verify = false;
            }
        }

        $defaultOptions = [
            'timeout' => 2,
            'verify' => $verify,
        ];

        $options = array_merge($defaultOptions, $options);

        $guzzleOptions = [
            'base_uri' => $url,
            'timeout' => $options['timeout'],
            'verify' => $options['verify'],
        ];

        try {
            $Client = new Client($guzzleOptions);
            $response = $Client->get($url);

            $httpCode = $response->getStatusCode();
            if ($httpCode >= 100 && $httpCode <= 599) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $exception) {
            if (!method_exists($exception, 'getResponse')) {
                return false;
            }

            $response = $exception->getResponse();

            if (empty($response) || !method_exists($response, 'getStatusCode')) {
                return false;
            }

            $httpCode = $response->getStatusCode();
            if ($httpCode >= 100 && $httpCode <= 599) {
                return true;
            } else {
                return false;
            }
        }
    }

}
