<?php

use App\Utility\Network\CACert;
use Cake\Core\Configure;
use Cake\Routing\Router;
use GuzzleHttp\Client;

/**
 * Check if an array is numerically indexed (from 0) at the first level
 *
 * @param array $arr
 * @return bool
 */
function isSeqArr(array $arr): bool
{
    //empty array
    if ([] === $arr) {
        return false;
    }

    //check keys
    if (array_keys($arr) === range(0, count($arr) - 1)) {
        $return = true;
    } else {
        $return = false;
    }

    return $return;
}

/**
 * Check if the input is a number or string
 *
 * @param mixed $unknown
 * @return bool
 */
function isStringOrNumber(mixed $unknown): bool
{
    if (is_string($unknown)) {
        return true;
    }

    if (is_numeric($unknown)) {
        return true;
    }

    return false;
}

/**
 * Convert value to boolean.
 *
 * @param $val
 * @return bool
 */
function asBool($val): bool
{
    if (is_string($val)) {
        $val = strtolower($val);
    }

    $true = [true, 'true', 'True', 1, '1', 't', 'yes', 'y', 'on', 'in', '+', 'plus', 'positive'];
    $false = [false, 'false', 'False', 0, '0', 'f', 'no', 'n', 'off', 'out', '-', 'minus', 'negative', null];

    if (in_array($val, $true, true)) {
        return true;
    }

    if (in_array($val, $false, true)) {
        return false;
    }

    //fall back to php conversion
    return boolval($val);
}

/**
 * Convert value to boolean.
 *
 * @param $val
 * @return bool|null
 */
function asBoolOrNull($val): ?bool
{
    if ($val === null) {
        return null;
    }

    return asBool($val);
}


/**
 * Convert value to a string.
 *
 * @param $val
 * @return bool
 */
function asString($val): bool|string
{
    if (is_string($val)) {
        return $val;
    }

    if ($val === null) {
        return 'null';
    }

    if ($val === true) {
        return 'true';
    }

    if ($val === false) {
        return 'false';
    }

    return false;
}

/**
 * Better function to determine if in CLI mode
 *
 * @return bool
 */
function is_cli(): bool
{
    if (defined('STDIN')) {
        return true;
    }

    if (php_sapi_name() === 'cli') {
        return true;
    }

    if (array_key_exists('SHELL', $_ENV)) {
        return true;
    }

    if (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0) {
        return true;
    }

    if (!array_key_exists('REQUEST_METHOD', $_SERVER)) {
        return true;
    }

    return false;
}

/**
 * Better than file_get_contents() for URL's as we can use cacert.pem file for http connections.
 * Automatic check to see if $URL is a local file and switches to file_get_contents().
 *
 * @param string $url
 * @param array $options
 * @return false|string
 */
function file_get_contents_guzzle(string $url, array $options = []): false|string
{
    if (strtolower(substr($url, 0, 4)) !== 'http') {
        if (!is_file($url)) {
            return false;
        }

        return file_get_contents($url);
    }

    $caPath = ((new CACert())->getCertPath());
    if ($caPath) {
        $verify = $caPath;
    } else {
        $verify = true;
    }

    //relax validation in DEV mode
    $mode = strtolower(Configure::read('mode'));
    if (in_array($mode, ['dev', 'uat'])) {
        $relaxLocal = Router::fullBaseUrl();
        $relaxLocal = str_replace('https://', '', $relaxLocal);
        $relaxLocal = str_replace('http://', '', $relaxLocal);
        $relaxLocal = [$relaxLocal];
    } else {
        $relaxLocal = [];
    }

    //relax $verify if localhost address
    $localhostAddresses = array_merge(['127.0.0.1', 'localhost', 'local'], $relaxLocal);
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

        return $response->getBody()->getContents();
    } catch (\Throwable $exception) {
        return false;
    }
}

function urldecode_multi($string)
{
    while (urldecode($string) !== $string) {
        $string = urldecode($string);
    }
    return $string;
}

function microtimestamp($separator = '')
{
    $mt = microtime();
    $mt = explode(" ", $mt);
    $unixTS = $mt[1];
    $microParts = explode(".", $mt[0]);

    return "{$unixTS}{$separator}{$microParts[1]}";
}
