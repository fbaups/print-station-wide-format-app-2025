<?php

namespace App\Utility\IntegrationCredentials;

use App\Utility\Feedback\ReturnAlerts;

class BaseIntegrationCredentials
{
    use ReturnAlerts;


    public function __construct()
    {
    }

    public function getIntegrationTypes(): array
    {
        return [
            'MicrosoftOpenAuth2' => 'Microsoft',
            'BackblazeB2' => 'Backblaze B2',
            //'BackblazeS3' => 'Backblaze S3 Compatible',
            //'GoogleOpenAuth2' => 'Google',
            //'OpenAuth2' => 'OpenAuth2',
            'sftp' => 'Secure FTP',
            'XMPie-uProduce' => 'XMPie uProduce API',
            //'XMPie-uStore' => 'XMPie uStore API',
            //'XMPie-Circle' => 'XMPie Circle API',
        ];
    }


    /**
     * Stub function to avoid errors
     *
     * @return void
     */
    public function updateLastStatus(): void
    {

    }


}
