<?php

namespace App\MessageGateways;

use App\Model\Table\SettingsTable;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use Cake\ORM\TableRegistry;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use ReflectionClass;

/**
 * Use this factory to get an SMS gateway.
 */
class SmsGatewayFactory
{
    private null|string $username;
    private null|string $password;
    private null|string $appKey;
    private null|string $appId;
    private null|string $provider;

    public function __construct($username = null, $password = null, $appId = null, $appKey = null, $provider = null)
    {
        /** @var SettingsTable $Settings */
        $Settings = TableRegistry::getTableLocator()->get('Settings');

        if (empty($username)) {
            $username = $Settings->getSetting('sms_gateway_username') ?? null;
        }

        if (empty($password)) {
            $password = $Settings->getSetting('sms_gateway_password') ?? null;
        }

        if (empty($appId)) {
            $appId = $Settings->getSetting('sms_gateway_api_id') ?? null;
        }

        if (empty($appKey)) {
            $appKey = $Settings->getSetting('sms_gateway_api_key') ?? null;
        }

        if (empty($provider)) {
            $provider = $Settings->getSetting('sms_gateway_provider') ?? '\\App\\MessageGateways\\DummySmsGateway';
        }


        $this->username = $username;
        $this->password = $password;
        $this->appId = $appId;
        $this->appKey = $appKey;
        $this->provider = $provider;

    }

    /**
     * Use whatever logic is needed to return the preferred SMS gateway for this installation/customer.
     *
     * @return SmsGatewayInterface|CellcastSmsGateway
     */
    public function getSmsGateway(): SmsGatewayInterface|CellcastSmsGateway
    {
        return new $this->provider($this->username, $this->password, $this->appKey, $this->appId);
    }

    /**
     * Get a list of all the MessageGateways classes
     *
     * @return array
     */
    public function getSmsGatewayClasses(): array
    {
        $storagePath = APP . 'MessageGateways\\';

        $files = [];
        $folders = [];
        if (is_dir($storagePath)) {
            $adapter = new LocalFilesystemAdapter($storagePath);
            $fs = new Filesystem($adapter);
            try {
                $listing = $fs->listContents('', false);
                foreach ($listing as $item) {
                    $path = $item->path();
                    if ($item instanceof FileAttributes) {
                        $files[] = $item->path();
                    } elseif ($item instanceof DirectoryAttributes) {
                        $folders[] = $item->path();
                    }
                }
            } catch (\Throwable $exception) {
            }
        }

        $smsGatewayList = [];
        foreach ($files as $file) {
            $file = pathinfo($file, PATHINFO_FILENAME);
            $className = "\\App\\MessageGateways\\{$file}";
            if (str_contains($className, 'Interface')) {
                continue;
            }

            $class = new $className();

            try {
                $class = new ReflectionClass($class);
                $methods = $class->getMethods();
                foreach ($methods as $method) {
                    if ($method->getName() === 'sendSms') {
                        $smsGatewayList[$className] = $file;
                    }
                }
            } catch (\Throwable $exception) {

            }
        }

        asort($smsGatewayList);

        return $smsGatewayList;
    }

}
