<?php

namespace App\Utility\IntegrationCredentials\XMPie;

use App\Model\Entity\IntegrationCredential;
use App\Model\Table\IntegrationCredentialsTable;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\Utility\Storage\UrlInspector;
use App\XMPie\uProduce\Clients\ClientFactory;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

class uProduceCommunicationsFlow extends BaseIntegrationCredentials
{
    private IntegrationCredential $integrationCredential;
    private array $uProduceReport = [];

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

        $parameters = $this->integrationCredential->uProduce_getParametersDecrypted();
        if ($parameters && isset($parameters['uproduce_host'])) {
            $this->checkViaRawConnection($parameters);
        }

        if (isset($this->uProduceReport['connection'])) {
            if (!$this->uProduceReport['connection']) {
                $this->integrationCredential->last_status_text = 'broken';
                $this->integrationCredential->last_status_html = '<span class="text-danger">Broken Connection</span>';
            } elseif ($this->uProduceReport['connection'] && $this->uProduceReport['http_code'] === 200) {
                $this->integrationCredential->last_status_text = 'connection';
                $this->integrationCredential->last_status_html = '<span class="text-success">Connected</span>';
            } else {
                $this->integrationCredential->last_status_text = 'invalid api path';
                $this->integrationCredential->last_status_html = '<span class="text-warning">Invalid Path to API</span>';
            }
        } else {
            $this->integrationCredential->last_status_text = 'error';
            $this->integrationCredential->last_status_html = '<span class="text-danger">Connection Error</span>';
        }

        $this->checkClicksBalance($parameters);
        $this->checkAdminCredentials($parameters);
        $this->checkUserCredentials($parameters);

        if (!$this->uProduceReport['user_credentials']) {
            $this->integrationCredential->last_status_text = 'invalid credentials';
            $this->integrationCredential->last_status_html = '<span class="text-warning">Invalid User Credentials</span>';
        }

        $this->integrationCredential->tracking_data = [
            'report' => $this->uProduceReport,
            'alerts' => $this->getAllAlertsLogSequence(),
        ];
    }


    public function checkViaRawConnection(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultSettings(), $settings);

        $urlSettings = [
            'http_host' => $settings['uproduce_host'] . "xmpiewsapi/Licensing_SSP.asmx?wsdl",
            'http_port' => null,
            'http_timeout' => 2,
            'http_method' => 'GET',
        ];

        $UrlInspector = new UrlInspector();
        $result = $UrlInspector->inspectUrlConnection($urlSettings);

        $this->uProduceReport = $UrlInspector->getInspectionReport();
        $this->mergeAlertsFromObject($UrlInspector);

        return $result;
    }


    public function checkClicksBalance(array $settings = []): float|int|false
    {
        $xmpOptions = [
            'url' => $settings['uproduce_host'],
            'admin_username' => $settings['uproduce_admin_username'],
            'admin_password' => $settings['uproduce_admin_password'],
            'username' => $settings['uproduce_username'],
            'password' => $settings['uproduce_password'],
        ];

        $soapOptions = [];

        $config = [
            'security' => false,
            'timezone' => 'utc',
        ];

        $ClientFactory = new ClientFactory($xmpOptions, $soapOptions, $config);
        $LicensingClient = $ClientFactory->LicensingClient();

        try {
            $availableClicks = $LicensingClient->getAvailableClicks();
            $isPerpetual = $LicensingClient->isPerpetual();
            $serverId = $LicensingClient->getServerId();

            if ($serverId) {
                if ($availableClicks > 0) {
                    $balance = floatval($availableClicks);
                    $this->addInfoAlerts(__("uProduce has a balance of {0} clicks.", $availableClicks));
                } elseif ($availableClicks == 0 && $isPerpetual) {
                    $this->addInfoAlerts(__("uProduce has unlimited clicks."));
                    $balance = -1;
                } elseif ($availableClicks == 0) {
                    $this->addInfoAlerts(__("uProduce has a balance of {0} clicks.", $availableClicks));
                    $balance = 0;
                } else {
                    $this->addInfoAlerts(__("Cannot determine a uProduce click balance."));
                    $balance = false;
                }
            } else {
                $this->addInfoAlerts(__("Cannot determine the uProduce Server ID."));
                $balance = false;
            }
        } catch (\Throwable $exception) {
            $this->addInfoAlerts(__("Error obtaining the click balance: {0}", $exception->getMessage()));
            $balance = false;
        }

        $this->uProduceReport['clicks_balance'] = $balance;

        return $balance;
    }


    public function checkUserCredentials(array $settings = []): bool
    {
        $xmpOptions = [
            'url' => $settings['uproduce_host'],
            'username' => $settings['uproduce_username'],
            'password' => $settings['uproduce_password'],
        ];

        $soapOptions = [];

        $config = [
            'security' => false,
            'timezone' => 'utc',
        ];

        $ClientFactory = new ClientFactory($xmpOptions, $soapOptions, $config);
        $CustomerClient = $ClientFactory->CustomerClient();

        try {
            $accounts = $CustomerClient->getAccounts();
        } catch (\Throwable $exception) {
            $this->uProduceReport['user_credentials_error'] = $exception->getMessage();
            $this->addInfoAlerts(__("Error check user credentials: {0}", $exception->getMessage()));
            $accounts = false;
        }

        if ($accounts) {
            $return = true;
            $this->addInfoAlerts(__("Found {0} accounts accessible via the user credentials.", count($accounts)));
        } else {
            $return = false;
        }

        $this->uProduceReport['user_credentials'] = $return;

        return $return;
    }


    public function checkAdminCredentials(array $settings = []): bool
    {
        //switch to admin username and password
        $xmpOptions = [
            'url' => $settings['uproduce_host'],
            'username' => $settings['uproduce_admin_username'],
            'password' => $settings['uproduce_admin_password'],
        ];

        $soapOptions = [];

        $config = [
            'security' => false,
            'timezone' => 'utc',
        ];

        $ClientFactory = new ClientFactory($xmpOptions, $soapOptions, $config);
        $CustomerClient = $ClientFactory->CustomerClient();

        try {
            $accounts = $CustomerClient->getAccounts();
        } catch (\Throwable $exception) {
            $this->uProduceReport['admin_credentials_error'] = $exception->getMessage();
            $this->addInfoAlerts(__("Error check admin credentials: {0}", $exception->getMessage()));
            $accounts = false;
        }

        if ($accounts) {
            $return = true;
            $this->addInfoAlerts(__("Found {0} accounts accessible via the admin credentials.", count($accounts)));
        } else {
            $return = false;
        }

        $this->uProduceReport['admin_credentials'] = $return;

        return $return;
    }


    public function getDefaultSettings(): array
    {
        /** @var IntegrationCredentialsTable $IntegrationCredentialsTable */
        $source = $this->integrationCredential->getSource();
        $IntegrationCredentialsTable = TableRegistry::getTableLocator()->get($source);

        return $IntegrationCredentialsTable->getUProduceDefaultParameters();
    }


}
