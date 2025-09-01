<?php

namespace App\Command;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Model\Entity\BackgroundService;
use App\Model\Table\ApplicationLogsTable;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\AuditsTable;
use App\Model\Table\BackgroundServicesTable;
use App\Model\Table\ErrandsTable;
use App\Model\Table\HeartbeatsTable;
use App\Model\Table\HotFoldersTable;
use App\Model\Table\IntegrationCredentialsTable;
use App\Model\Table\MessagesTable;
use App\Model\Table\ScheduledTasksTable;
use App\Model\Table\SettingsTable;
use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class ServiceCommand extends AppCommand
{
    protected BackgroundServicesAssistant $BackgroundServicesAssistant;
    protected Table|BackgroundServicesTable $BackgroundServices;
    protected Table|ArtifactsTable $Artifacts;
    protected Table|HeartbeatsTable $Heartbeats;
    protected Table|HotFoldersTable $HotFolders;
    protected Table|ScheduledTasksTable $ScheduledTasks;
    protected Table|ErrandsTable $Errands;
    protected Table|SettingsTable $Settings;
    protected Table|AuditsTable $Audits;
    protected Table|ApplicationLogsTable $ApplicationLogs;
    protected Table|MessagesTable $Messages;
    protected Table|IntegrationCredentialsTable $IntegrationCredentials;

    protected Arguments $args;
    protected ConsoleIo $io;
    protected string $serviceName;

    public function initialize(): void
    {
        parent::initialize();

        $this->BackgroundServicesAssistant = new BackgroundServicesAssistant();
        $this->BackgroundServices = TableRegistry::getTableLocator()->get('BackgroundServices');
        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        $this->Heartbeats = TableRegistry::getTableLocator()->get('Heartbeats');
        $this->HotFolders = TableRegistry::getTableLocator()->get('HotFolders');
        $this->ScheduledTasks = TableRegistry::getTableLocator()->get('ScheduledTasks');
        $this->Errands = TableRegistry::getTableLocator()->get('Errands');
        $this->Settings = TableRegistry::getTableLocator()->get('Settings');
        $this->Audits = TableRegistry::getTableLocator()->get('Audits');
        $this->ApplicationLogs = TableRegistry::getTableLocator()->get('ApplicationLogs');
        $this->Messages = TableRegistry::getTableLocator()->get('Messages');
        $this->IntegrationCredentials = TableRegistry::getTableLocator()->get('IntegrationCredentials');
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->addOption('heartbeat-context', [
                'short' => 'h',
                'help' => 'Context when logging a Heartbeat',
                'default' => 'HotFolders',
            ])
            ->addOption('delay', [
                'short' => 'd',
                'help' => 'Delay the start by X seconds - handy if there are multiple instances',
                'default' => '0',
            ]);

        return $parser;
    }

    /**
     * Get an ever-increasing $sleepLength till $cap is reached
     *
     * @param float|int $currentSleepLength
     * @param int $cap
     * @param float|int $rate
     * @return int|float
     */
    protected function getSleepLength(float|int $currentSleepLength = 1, int $cap = 8, float|int $rate = 1.1): float|int
    {
        if ($currentSleepLength <= 0) {
            $currentSleepLength = 1;
        }

        //make sure the $rate >1 otherwise would never sleep
        $rate = max($rate, 1.1);

        $newSleepLength = $currentSleepLength * $rate;
        $newSleepLength = min($newSleepLength, $cap);
        $newSleepLength = round($newSleepLength, 1);

        return $newSleepLength;
    }
}
