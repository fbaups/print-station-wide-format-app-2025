<?php

namespace App\Utility\Microsoft\Graph;

use App\Model\Entity\IntegrationCredential;
use Cake\Utility\Hash;
use GuzzleHttp\Client;

class GraphApiClient
{

    private IntegrationCredential $integrationCredential;
    private string $accessToken;

    private string $rootUrl = 'https://graph.microsoft.com/v1.0/';

    /**
     * DefaultApplication constructor.
     *
     * @param IntegrationCredential $integrationCredential
     */
    public function __construct(IntegrationCredential $integrationCredential)
    {
        $this->integrationCredential = $integrationCredential;
        $this->accessToken = $this->integrationCredential->microsoftOpenAuth2_getAccessToken();
    }

    /**
     * Extract the document or folder ID from the given URL
     *
     * @param string $url
     * @return false|string
     */
    public function extractIdFromUrl(string $url): false|string
    {
        $url = urldecode($url);
        $urlParts = parse_url($url);

        if (!isset($urlParts['query']) || empty($urlParts['query'])) {
            return false;
        }

        parse_str($urlParts['query'], $query);

        if (isset($query['id'])) {
            return $query['id'];
        }

        if (isset($query['resid'])) {
            return $query['resid'];
        }

        return false;
    }


    public function msGraph_getDriveRootChildren(string $keyPath = null)
    {
        $url = $this->rootUrl . 'me/drive/root/children';
        return $this->msGraph_getInfo($url, $keyPath);
    }

    public function msGraph_getDriveInfo(string $keyPath = null)
    {
        $url = $this->rootUrl . 'me/drive';
        return $this->msGraph_getInfo($url, $keyPath);
    }

    public function msGraph_getDrivesInfo(string $keyPath = null)
    {
        $url = $this->rootUrl . 'me/drives';
        return $this->msGraph_getInfo($url, $keyPath);
    }

    public function msGraph_getMeInfo(string $keyPath = null)
    {
        $url = $this->rootUrl . 'me';
        return $this->msGraph_getInfo($url, $keyPath);
    }

    public function msGraph_getDriveItemInfo(string $itemId, string $keyPath = null)
    {
        $url = $this->rootUrl . 'me/drive/items/' . $itemId;
        return $this->msGraph_getInfo($url, $keyPath);
    }

    private function msGraph_getInfo(string $url, string $keyPath = null)
    {
        $Client = new Client();

        try {
            $response = $Client->request('GET', $url, [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Accept' => 'application/json',
                ]
            ]);
        } catch (\Throwable $exception) {
            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (!$body) {
            return false;
        }

        if ($keyPath) {
            return Hash::extract($body, $keyPath);
        } else {
            return $body;
        }
    }


    /*
     *


    public function check($id)
    {
        $integrationCredential = $this->IntegrationCredentials->get($id, [
            'contain' => [],
        ]);

        $accessToken = $integrationCredential->openAuth2_getAccessToken();

        $GraphClient = new GraphClient($integrationCredential);
        //$GraphClient->test();

        //dd($GraphClient);

        //$drivesInfo = $GraphClient->msGraph_getDrivesInfo($accessToken);
        //$driveInfo = $GraphClient->msGraph_getDriveInfo($accessToken);
        //$meInfo = $GraphClient->msGraph_getMeInfo($accessToken);
        //dump( $driveInfo['id']);

        //$rootChildrenInfo = $GraphClient->msGraph_getDriveRootChildren($accessToken);
        //dump($rootChildrenInfo);


        $urls = [
            // copy some URLs from your browser to test
        ];

        foreach ($urls as $url) {
            $itemId = $GraphClient->extractIdFromUrl($url);
            $itemInfo = $GraphClient->msGraph_getDriveItemInfo($accessToken, $itemId);
            dump($itemInfo);
        }


        die();

    }
     */

}
