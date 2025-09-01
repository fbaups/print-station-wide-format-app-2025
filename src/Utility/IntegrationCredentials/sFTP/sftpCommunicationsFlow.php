<?php

namespace App\Utility\IntegrationCredentials\sFTP;

use App\Model\Entity\IntegrationCredential;
use App\Model\Table\IntegrationCredentialsTable;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\Utility\Storage\SftpInspector;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use phpseclib3\Net\SFTP;
use Throwable;

class sftpCommunicationsFlow extends BaseIntegrationCredentials
{
    private IntegrationCredential $integrationCredential;
    private array $sftpReport = [];

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

        $parameters = $this->integrationCredential->sftp_getParametersDecrypted();
        if ($parameters && isset($parameters['sftp_host'])) {
            $this->checkViaRawConnection($parameters);
        }

        if (isset($this->sftpReport['connection'])) {
            if (!$this->sftpReport['connection']) {
                $this->integrationCredential->last_status_text = 'broken';
                $this->integrationCredential->last_status_html = '<span class="text-danger">Broken Connection</span>';
            } elseif ($this->sftpReport['connection'] && $this->sftpReport['chdir_basePath']) {
                $this->integrationCredential->last_status_text = 'connection';
                $this->integrationCredential->last_status_html = '<span class="text-success">Connected</span>';
            } else {
                $this->integrationCredential->last_status_text = 'invalid path';
                $this->integrationCredential->last_status_html = '<span class="text-warning">Invalid File Path</span>';
            }
        } else {
            $this->integrationCredential->last_status_text = 'error';
            $this->integrationCredential->last_status_html = '<span class="text-danger">Connection Error</span>';
        }

        $this->integrationCredential->tracking_data = [
            'report' => $this->sftpReport,
            'alerts' => $this->getAllAlertsLogSequence(),
        ];
    }


    public function checkViaRawConnection(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultSettings(), $settings);

        $SftpInspector = new SftpInspector();
        $result = $SftpInspector->inspectSftpServer($settings);

        $this->sftpReport = $SftpInspector->getInspectionReport();
        $this->mergeAlertsFromObject($SftpInspector);

        return $result;
    }


    public function checkViaFlysystemConnection(array $settings = [])
    {

    }

    public function getDefaultSettings(): array
    {
        /** @var IntegrationCredentialsTable $IntegrationCredentialsTable */
        $source = $this->integrationCredential->getSource();
        $IntegrationCredentialsTable = TableRegistry::getTableLocator()->get($source);

        return $IntegrationCredentialsTable->getSftpDefaultParameters();
    }


}
