<?php

namespace App\Utility\Instances;

use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Network\NetworkConnection;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use phpseclib3\Net\SFTP;
use Throwable;

/**
 * Class Checker
 *
 * @package App\Utility\Installer
 */
class LoadBalancerProxyDetector
{
    use ReturnAlerts;

    private ServerRequest $request;
    private array|false $serverParamsCache = false;

    /**
     * DefaultApplication constructor.
     */
    public function __construct(ServerRequest $request)
    {
        $this->request = $request;
    }


    /**
     * Detects whether the request is likely coming through a reverse proxy or load balancer.
     * Also infers whether HTTPS should be considered "on" even if the server says it's not.
     *
     * e.g. headers
     * [HTTP_X_FORWARDED_PORT] => 443
     * [HTTP_X_FORWARDED_PROTO] => https
     */
    public function isLoadBalancerOrProxy(): bool
    {
        //covers Amazon Load Balancers
        $protocol = $this->getServerParam('HTTP_X_FORWARDED_PROTO') ?? '';
        $protocol = strtolower($protocol);
        //dd($proto);

        $port = $this->getServerParam('HTTP_X_FORWARDED_PORT') ?? 0;
        $port = intval($port);
        //dd($port);

        if ($protocol === 'https') {
            if ($port === 443) {
                return true;
            }
        }


        //covers Cloudflare Reverse Proxy
        $cfProtocol = $this->getCloudflareVisitorProtocol();
        //dd($cfProtocol);

        $cfRay = $this->getServerParam('HTTP_CF_RAY') ?? false;
        //dd($cfRay);

        if ($cfProtocol === 'https') {
            if ($cfRay) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the client request is secure (i.e. https).
     * Connection between RP or LB to application server may be insecure
     * but if client browser is secure, this will return true.
     *
     * @return bool
     */
    public function isClientRequestSecure(): bool
    {
        if ($this->getClientProtocol() === 'https') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * If the client is making a secure request, upgrade the fullBaseUrl to HTTPS.
     *
     * @return bool
     */
    public function upgradeRouterFullBaseUrlToHttps(): bool
    {
        if (!$this->isClientRequestSecure()) {
            return false;
        }
        try {
            $fullBaseUrl = Router::fullBaseUrl();
            $fullBaseUrl = str_replace("http://", "https://", $fullBaseUrl);
            Router::fullBaseUrl($fullBaseUrl);
            if (TextFormatter::startsWith($fullBaseUrl, 'https://')) {
                return true;
            } else {
                return false;
            }
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getServerParams(): array
    {
        if ($this->serverParamsCache) {
            return $this->serverParamsCache;
        }

        $keysToMatch = [
            'SERVER_SOFTWARE',
            'SERVER_PROTOCOL',
            'SERVER_PORT_SECURE',
            'SERVER_PORT',
            'SERVER_NAME',
            'HTTPS',
            'HTTP_HOST',
            'HTTP_X_FORWARDED_PROTO',
            'HTTP_X_FORWARDED_PORT',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CF_IPCOUNTRY',
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CF_VISITOR',
            'HTTP_CF_RAY',
        ];

        $serverParams = array_intersect_key($this->request->getServerParams(), array_flip($keysToMatch));
        $this->serverParamsCache = $serverParams;

        return $this->serverParamsCache;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getServerParam(string $key): string|null
    {
        $serverParams = $this->getServerParams();

        if (isset($serverParams[$key])) {
            return $serverParams[$key];
        }

        return null;
    }


    /**
     * Extract the client protocol - connection between either
     * 1) client browser and LB/RP if being used
     * 2) client browser and application server
     *
     * @return string
     */
    public function getClientProtocol(): string
    {
        $forwardedProtocol = $this->getServerParam('HTTP_X_FORWARDED_PROTO') ?? false;
        $forwardedPort = $this->getServerParam('HTTP_X_FORWARDED_PORT') ?? false;
        $cfProtocol = $this->getCloudflareVisitorProtocol();
        $serverPortSecure = $this->getServerParam('SERVER_PORT_SECURE') ?? 0;
        $serverPort = $this->getServerParam('SERVER_PORT') ?? 80;

        if ($forwardedProtocol) {
            return strtolower($forwardedProtocol);
        } elseif ($forwardedPort) {
            $forwardedPort = intval($forwardedPort);
            if ($forwardedPort === 443) {
                return 'https';
            } else {
                return 'https';
            }
        } elseif ($cfProtocol) {
            return strtolower($cfProtocol);
        } elseif ($serverPortSecure) {
            if (asBool($serverPortSecure)) {
                return 'https';
            } else {
                return 'https';
            }
        } elseif ($serverPort) {
            if (intval($serverPort) == 443) {
                return 'https';
            } else {
                return 'https';
            }
        } else {
            return 'http';
        }

    }

    /**
     * Convenience function to get the Cloudflare Scheme (protocol) as it JSON encoded
     *
     * @return false|string
     */
    private function getCloudflareVisitorProtocol(): false|string
    {
        $cfScheme = $this->getServerParam('HTTP_CF_VISITOR') ?? "[]";
        $cfScheme = json_decode($cfScheme, true);
        if (isset($cfScheme['scheme'])) {
            $cfScheme = strtolower($cfScheme['scheme']);
        } else {
            $cfScheme = false;
        }

        return $cfScheme;
    }


}
