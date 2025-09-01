<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Table\IntegrationCredentialsTable;
use arajcany\ToolBox\Utility\Security\Security;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use GuzzleHttp\Client;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

/**
 * IntegrationCredential Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $type
 * @property string|null $name
 * @property string|null $description
 * @property bool|null $is_enabled
 * @property array|null $parameters
 * @property string|null $tracking_hash
 * @property array|null $tracking_data
 *
 * @property string|null $last_status_text
 * @property string|null $last_status_html
 * @property \Cake\I18n\DateTime|null $last_status_datetime
 *
 */
class IntegrationCredential extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'modified' => true,
        'type' => true,
        'name' => true,
        'description' => true,
        'is_enabled' => true,
        'parameters' => true,
        'tracking_hash' => true,
        'tracking_data' => true,
        'last_status_text' => true,
        'last_status_html' => true,
        'last_status_datetime' => true,
    ];

    /*
     * Decrypted Microsoft OpenAuth credentials
     */
    public function microsoftOpenAuth2_getProviderOptionsDecrypted()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        $providerOptions = $this->parameters['provider_options'];
        $providerOptions['tenantId'] = Security::decrypt64($this->parameters['provider_options']['tenantId']);
        $providerOptions['clientId'] = Security::decrypt64($this->parameters['provider_options']['clientId']);
        $providerOptions['clientSecret'] = Security::decrypt64($this->parameters['provider_options']['clientSecret']);

        return $providerOptions;
    }

    public function microsoftOpenAuth2_getTenantId()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        return $this->microsoftOpenAuth2_getProviderOptionsDecrypted()['tenantId'];
    }

    public function microsoftOpenAuth2_getClientId()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        return $this->microsoftOpenAuth2_getProviderOptionsDecrypted()['clientId'];
    }

    public function microsoftOpenAuth2_getClientSecret()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        return $this->microsoftOpenAuth2_getProviderOptionsDecrypted()['clientSecret'];
    }

    public function microsoftOpenAuth2_getAccessToken()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        return Security::decrypt64($this->tracking_data['access_token'] ?? null);
    }

    public function microsoftOpenAuth2_getRefreshToken()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        return Security::decrypt64($this->tracking_data['refresh_token'] ?? null);
    }

    public function microsoftOpenAuth2_getExpires()
    {
        if ($this->type !== 'MicrosoftOpenAuth2') {
            return false;
        }

        return $this->tracking_data['expires'] ?? null;
    }


    /*
     * Decrypted Backblaze B2 credentials
     */
    public function backblazeB2_getParametersDecrypted(): false|array
    {
        $params = $this->parameters;
        $params['b2_key'] = Security::decrypt64($this->parameters['b2_key']);

        return $params;
    }


    /*
     * Decrypted sFTP credentials
     */
    public function sftp_getParametersDecrypted(): false|array
    {
        if ($this->type !== 'sftp') {
            return false;
        }

        $params = $this->parameters;
        $params['sftp_password'] = Security::decrypt64($this->parameters['sftp_password']);
        $params['sftp_privateKey'] = Security::decrypt64($this->parameters['sftp_privateKey']);
        $params['sftp_publicKey'] = Security::decrypt64($this->parameters['sftp_publicKey']);

        return $params;
    }


    /*
     * Decrypted uProduce credentials
     */
    public function uProduce_getParametersDecrypted(): false|array
    {
        if ($this->type !== 'XMPie-uProduce') {
            return false;
        }

        $params = $this->parameters;
        $params['uproduce_password'] = Security::decrypt64($this->parameters['uproduce_password']);
        $params['uproduce_admin_password'] = Security::decrypt64($this->parameters['uproduce_admin_password']);

        return $params;
    }

    /**
     * Get the USER Credentials ready to use for the XMPie CompositionMaker
     *
     * @return array|false
     */
    public function uProduce_getUserCredentials(): false|array
    {
        if ($this->type !== 'XMPie-uProduce') {
            return false;
        }

        $params['url'] = $this->parameters['uproduce_host'];
        $params['username'] = $this->parameters['uproduce_username'];
        $params['password'] = Security::decrypt64($this->parameters['uproduce_password']);

        return $params;
    }

    /**
     * Get the ADMIN Credentials ready to use for the XMPie CompositionMaker
     *
     * @return array|false
     */
    public function uProduce_getAdminCredentials(): false|array
    {
        if ($this->type !== 'XMPie-uProduce') {
            return false;
        }

        $params['url'] = $this->parameters['uproduce_host'];
        $params['username'] = $this->parameters['uproduce_admin_username'];
        $params['password'] = Security::decrypt64($this->parameters['uproduce_admin_password']);

        return $params;
    }

    public function uProduce_getSoapOptions(): false|array
    {
        if ($this->type !== 'XMPie-uProduce') {
            return false;
        }

        return [];
    }

    public function uProduce_getConfigOptions(): false|array
    {
        if ($this->type !== 'XMPie-uProduce') {
            return false;
        }

        $config['security'] = asBool($this->parameters['ssl_validation']);
        $config['timezone'] = 'utc';

        return $config;
    }

}
