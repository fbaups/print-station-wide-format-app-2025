<?php

namespace App\Utility\IntegrationCredentials\Backblaze;

use App\Model\Entity\IntegrationCredential;
use App\Model\Table\IntegrationCredentialsTable;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\Utility\Storage\BackblazeB2Inspector;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

class B2CommunicationsFlow extends BaseIntegrationCredentials
{
    private IntegrationCredential $integrationCredential;
    private array|false $b2Report = [];
    private array|false $b2AccountAuthorisation = [];

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

        $parameters = $this->integrationCredential->backblazeB2_getParametersDecrypted();
        if ($parameters && isset($parameters['b2_key_id']) && isset($parameters['b2_key'])) {
            $this->checkViaAccountAuthorisation($parameters);
        }

        if ($this->b2AccountAuthorisation) {
            $this->checkViaFlysystemConnection($parameters);
        }

        if (!$this->b2AccountAuthorisation) {
            $this->integrationCredential->last_status_text = 'unauthorised';
            $this->integrationCredential->last_status_html = '<span class="text-danger">Invalid API Credentials</span>';
        } elseif (isset($this->b2Report['connection'])) {
            if (!$this->b2Report['connection']) {
                $this->integrationCredential->last_status_text = 'broken';
                $this->integrationCredential->last_status_html = '<span class="text-danger">Broken Connection</span>';
            } elseif ($this->b2Report['connection'] && $this->b2Report['chdir_basePath']) {
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

        $this->integrationCredential->tracking_data = $this->b2AccountAuthorisation;
        $this->integrationCredential->tracking_data['report'] = $this->b2Report;
        $this->integrationCredential->tracking_data['alerts'] = $this->getAllAlertsLogSequence();
    }

    public function checkViaAccountAuthorisation(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultSettings(), $settings);
        $B2Inspector = new BackblazeB2Inspector();
        $accountAuthorisation = $B2Inspector->getAccountAuthorisation($settings);

        $accountAuthorisationResult = isset($accountAuthorisation['account_authorisation']);

        $this->b2AccountAuthorisation = $accountAuthorisation;
        $this->mergeAlertsFromObject($B2Inspector);

        return $accountAuthorisationResult;
    }

    public function checkViaFlysystemConnection(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultSettings(), $settings);

        $B2Inspector = new BackblazeB2Inspector();
        $inspectBackBlazeB2ServerResult = $B2Inspector->inspectBackBlazeB2Server($settings);

        $this->b2Report = $B2Inspector->getInspectionReport();
        $this->mergeAlertsFromObject($B2Inspector);

        return $inspectBackBlazeB2ServerResult;
    }

    public function getDefaultSettings(): array
    {
        /** @var IntegrationCredentialsTable $IntegrationCredentialsTable */
        $source = $this->integrationCredential->getSource();
        $IntegrationCredentialsTable = TableRegistry::getTableLocator()->get($source);

        return $IntegrationCredentialsTable->getBackblazeB2DefaultParameters();
    }


}
