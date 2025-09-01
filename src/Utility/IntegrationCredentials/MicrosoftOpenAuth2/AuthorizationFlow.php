<?php

namespace App\Utility\IntegrationCredentials\MicrosoftOpenAuth2;

use App\Model\Entity\IntegrationCredential;
use App\Model\Table\IntegrationCredentialsTable;
use App\Utility\Feedback\ReturnAlerts;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\Utility\Network\CACert;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use GuzzleHttp\Client;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

class AuthorizationFlow extends BaseIntegrationCredentials
{

    private int $expirySafetyMargin = 120; //number of seconds
    private IntegrationCredential $integrationCredential;
    private Microsoft|false $cache_OpenAuth2Provider = false;
    private string|false $cache_OpenAuth2AuthorizationUrl = false;

    /**
     * @param IntegrationCredential $integrationCredential
     */
    public function __construct(IntegrationCredential $integrationCredential)
    {
        parent::__construct();

        $this->integrationCredential = $integrationCredential;
    }

    /**
     * Try and connect to the integration and see that the credentials work.
     * Update $this->integrationCredential->
     *      last_status_datetime
     *      last_status_text
     *      last_status_html
     *
     * The Entity is mutated, and ready for saving
     *
     * @return void
     */
    public function updateLastStatus(): void
    {
        $currentDatetime = new DateTime(null, 'UTC');
        $this->integrationCredential->last_status_datetime = $currentDatetime;
        $this->integrationCredential->last_status_text = $this->getAuthorizationStatus();

        //if expired, try and do a refresh of the token
        if ($this->integrationCredential->last_status_text === 'expired') {
            $result = $this->authoriseWithRefresh();
            if ($result) {
                $this->integrationCredential->last_status_text = 'authorized';
                $this->integrationCredential->last_status_html = '<span class="text-success">Authorized</span>';
            } else {
                $this->integrationCredential->last_status_text = 'unauthorized';
                $this->integrationCredential->last_status_html = '<span class="text-danger">Unauthorized</span>';
            }
        }

    }


    /**
     * Get an instance of the OAuth2 Provider
     *
     * @return false|Microsoft
     */
    public function getProvider(): false|Microsoft
    {
        if ($this->cache_OpenAuth2Provider) {
            return $this->cache_OpenAuth2Provider;
        }

        // Force Microsoft to prompt user to select account
        $this->integrationCredential->parameters['auth_url_options']['prompt'] = 'select_account';

        // Create Guzzle client with custom cert path
        $guzzleConfig = [
            'verify' => (new CACert())->getCertPath()
        ];
        $guzzleClient = new Client($guzzleConfig);

        // Create provider instance
        $providerOptions = $this->integrationCredential->microsoftOpenAuth2_getProviderOptionsDecrypted();
        $Provider = new Microsoft($providerOptions,);

        // Use the created Guzzle client
        $Provider->setHttpClient($guzzleClient);

        $this->cache_OpenAuth2Provider = $Provider;

        return $Provider;
    }


    /**
     * Get the Authorisation URL from the OAuth2 Provider
     *
     * @return false|string
     */
    public function getAuthorizationUrl(): false|string
    {
        $IntegrationCredentials = TableRegistry::getTableLocator()->get('IntegrationCredentials');

        if ($this->cache_OpenAuth2AuthorizationUrl) {
            return $this->cache_OpenAuth2AuthorizationUrl;
        }

        $provider = $this->getProvider();
        $authUrlOptions = $this->integrationCredential->parameters['auth_url_options'];;
        $authUrl = $provider->getAuthorizationUrl($authUrlOptions);
        $this->cache_OpenAuth2AuthorizationUrl = $authUrl;

        //save the 'state' so it can be tracked later after the redirection
        $trackingHash = $provider->getState();
        $this->integrationCredential->tracking_hash = $trackingHash;
        $IntegrationCredentials->save($this->integrationCredential);

        return $authUrl;
    }

    /**
     * Return a keyword of the current Authorization status.
     * Keywords can then be used to take appropriate action.
     *
     * The status is determined by looking at the tracking data and making a decision.
     *
     * unauthorized =   authorization process has not been started
     * expired      =   need a refresh
     * authorized   =   everything is OK
     *
     * @return string
     */
    public function getAuthorizationStatus(): string
    {
        $accessToken = $this->integrationCredential->microsoftOpenAuth2_getAccessToken() ?? null;
        $refreshToken = $this->integrationCredential->microsoftOpenAuth2_getRefreshToken() ?? null;
        $expires = $this->integrationCredential->tracking_data['expires'] ?? 0 - $this->expirySafetyMargin;
        $currentTime = time();

        if (!$this->integrationCredential->tracking_data) {
            $keyword = 'unauthorized';
        } elseif ($accessToken && $refreshToken && ($expires < $currentTime)) {
            $keyword = 'expired';
        } else {
            $keyword = 'authorized';
        }

        return $keyword;
    }


    /**
     * Will use a 'code' to get the initial 'access_token' and 'refresh_token'
     *
     * @param $code
     * @return bool
     */
    public function authorizeWithCode($code): bool
    {
        $provider = $this->getProvider();

        try {
            $tokens = $provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (\Throwable $exception) {
            $this->addDangerAlerts("Failed to Authorize With Code: " . $exception->getMessage());
            return false;
        }

        if (!$tokens) {
            $this->addDangerAlerts("No tokens returned in the Authorize With Code flow.");
            return false;
        }

        return $this->saveTokens($tokens);
    }

    /**
     * Will use a 'refresh_token' to get subsequent 'access_token' and 'refresh_token'
     * Can safely call this function as it only refreshes if necessary.
     * Will make sure the access_token stored in the DB are valid.
     *
     * @return bool
     */
    public function authoriseWithRefresh(): bool
    {
        $accessToken = $this->integrationCredential->microsoftOpenAuth2_getAccessToken() ?? null;
        $refreshToken = $this->integrationCredential->microsoftOpenAuth2_getRefreshToken() ?? null;
        $expires = $this->integrationCredential->tracking_data['expires'] ?? 0 - $this->expirySafetyMargin;
        $currentTime = time();

        //don't refresh if you have tokens and they are not expired
        if ($accessToken && $refreshToken && ($expires > $currentTime)) {
            $this->addInfoAlerts("Refresh not required as current Tokens are valid.");
            return true;
        }

        $provider = $this->getProvider();

        try {
            $tokens = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken
            ]);
        } catch (\Throwable $exception) {
            $this->addDangerAlerts("Failed to Authorize With Refresh: " . $exception->getMessage());
            return false;
        }

        if (!$tokens) {
            $this->addDangerAlerts("No tokens returned in the Authorize With Refresh flow.");
            return false;
        }

        return $this->saveTokens($tokens);
    }


    /**
     * Save the tokens from the authorisation/refresh process
     *
     * @param AccessTokenInterface $tokens
     * @return bool
     */
    private function saveTokens(AccessTokenInterface $tokens): bool
    {
        /** @var IntegrationCredentialsTable $IntegrationCredentialsTable */
        $IntegrationCredentialsTable = TableRegistry::getTableLocator()->get('IntegrationCredentials');

        $tokensToSave = $tokens->jsonSerialize();

        $dto = (new DateTime($tokensToSave['expires']))->setTimezone(LCL_TZ);
        $tokensToSave['expiration_datetime'] = $dto->format("Y-m-d H:i:s");
        $tokensToSave['expiration_timezone'] = $dto->timezoneName;

        //encrypt tokens
        $tokensToSave['access_token'] = Security::encrypt64($tokensToSave['access_token']);
        $tokensToSave['refresh_token'] = Security::encrypt64($tokensToSave['refresh_token']);

        $this->integrationCredential->tracking_hash = null;
        $this->integrationCredential->tracking_data = $tokensToSave;

        if ($IntegrationCredentialsTable->save($this->integrationCredential)) {
            $this->addInfoAlerts("Tokens saved successfully.");
            return true;
        } else {
            $this->addDangerAlerts("Tokens failed to save.");
            return false;
        }
    }

}
