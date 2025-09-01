<?php

namespace App\Command;

use App\HotFolderWorkflows\Base\WorkflowBase;
use App\Model\Entity\Errand;
use App\Model\Entity\HotFolderEntry;
use App\Model\Table\HotFolderEntriesTable;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

/**
 * HotFoldersCommand command.
 * Used to process entries in Hot Folders. An Errand is created to process the Hot Folder entry.
 *
 */
class ServiceHotFoldersCommand extends ServiceCommand
{

    private array $skipDuplicatesHashSums = [];

    public function initialize(): void
    {
        parent::initialize();
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

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->io = $io;
        $this->args = $args;
        $this->serviceName = $args->getOption('heartbeat-context');


        try {
            return $this->doLoop();
        } catch (\Throwable $exception) {
            $this->io->error('=======================ERROR=======================');
            $this->io->error($exception->getTraceAsString());
            $this->io->error($exception->getMessage());
            $this->io->error("FILE: " . $exception->getFile());
            $this->io->error("LINE: " . $exception->getLine());

            Log::write('error', __("{0}\r\nFile: {1}\r\nLine: {2}\r\n{3}\r\n", $exception->getMessage(), __FILE__, __LINE__, $exception->getTraceAsString()));

            return 1;
        }
    }

    /**
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|int The exit code or null for success
     */
    private function doLoop()
    {
        $heartbeatContext = $this->args->getOption('heartbeat-context');

        $backgroundService = $this->BackgroundServices->getBackgroundServiceByName($heartbeatContext);
        if (!$backgroundService) {
            Log::write('error', __("Background Service DB entry missing.\r\nFile {0}\r\nLine {1}", __FILE__, __LINE__));
            return 1;
        }

        //delay if there are multiple threads.
        $delay = intval($this->args->getOption('delay'));
        if ($delay) {
            $this->io->out(__("Delaying start by {0} seconds...", $delay));
            $backgroundService->current_state = 'started-delay-' . $delay;
            $this->BackgroundServices->save($backgroundService);
            sleep($delay);
        }

        $backgroundServiceType = $backgroundService->type;
        $backgroundServiceLimit = Configure::read("Settings.{$backgroundServiceType}_background_service_limit");
        $backgroundServiceLifeExpectancy = Configure::read("Settings.{$backgroundServiceType}_background_service_life_expectancy");
        $backgroundServiceGracePeriod = Configure::read("Settings.{$backgroundServiceType}_background_service_grace_period");
        $backgroundServiceRetryLimit = Configure::read("Settings.{$backgroundServiceType}_retry_limit");
        $backgroundServiceSleep = Configure::read("Settings.{$backgroundServiceType}_background_service_sleep");
        $timeoutSleep = 1; //default sleep length - will be increased if there is nothing to do

        $this->io->out(__('Starting {0} Service.', $heartbeatContext));

        $hbOptions = [
            'context' => $heartbeatContext,
            'name' => 'Started Hot Folder Service',
        ];
        $this->Heartbeats->createHeartbeat($hbOptions);

        //setup life expectancy
        $currentTime = new DateTime();
        $appointmentDate = new DateTime();
        $retirementDate = (clone $appointmentDate)->addMinutes($backgroundServiceLifeExpectancy);
        $terminationDate = (clone $retirementDate)->addMinutes($backgroundServiceGracePeriod);

        //persist new $backgroundService info
        $backgroundService->appointment_date = $appointmentDate;
        $backgroundService->retirement_date = $retirementDate;
        $backgroundService->termination_date = $terminationDate;
        $backgroundService->pid = getmypid();
        $backgroundService->current_state = 'started';
        $backgroundService->force_recycle = null;
        $backgroundService->force_shutdown = null;
        $this->BackgroundServices->save($backgroundService);

        //fix null values
        $this->HotFolders->fixPollingAndStableIntervals();

        //setup Zip Packager
        $ZP = new ZipPackager();

        $roundCounter = 1;
        while ($currentTime->lessThanOrEquals($retirementDate)) {
            $this->io->out(__("Hot Folder Loop - Round {0}.", $roundCounter));
            $activityCounter = 0;


            /*
             * --------------------------------------------------------------------------------
             * This section creates the Hot Folder Entries in the DB for all the Hot Folders
             * --------------------------------------------------------------------------------
             */
            $allHotFoldersKeyed = $this->HotFolders->getEnabledHotFoldersKeyedById();
            foreach ($allHotFoldersKeyed as $hotFolder) {

                //clear completed/orphaned entries
                if ($hotFolder->auto_clear_entries) {
                    $clearResult = 0;
                    $clearResult += $this->HotFolders->HotFolderEntries->clearSuccessEntries($hotFolder->id);
                    $clearResult += $this->HotFolders->HotFolderEntries->clearOrphaned($hotFolder->id);
                    $this->io->out(__('Cleared {0} Entries for {1} Hot Folder', $clearResult, $hotFolder->name));
                }

                $currentTime = new DateTime();

                if (empty($hotFolder->next_polling_time)) {
                    $hotFolder->next_polling_time = clone $currentTime;
                    $this->HotFolders->save($hotFolder);
                }

                if ($currentTime->lessThan($hotFolder->next_polling_time)) {
                    $this->io->out();
                    $this->io->out(__('Skipped Hot Folder "{0}" as polling time has not been reached', $hotFolder->name), 3);
                    continue;
                }

                $this->io->out();
                $this->io->out(__('Processing Hot Folder "{0}"', $hotFolder->name), 3);

                $hotFolder->next_polling_time = $currentTime->addSeconds($hotFolder->polling_interval);
                $this->HotFolders->save($hotFolder);

                $hotFolderPath = TextFormatter::makeDirectoryTrailingBackwardSlash($hotFolder->path);
                $entries = $ZP->rawFileAndFolderList($hotFolderPath, false);
                $entries = array_merge($entries['folders'], $entries['files']);

                $WorkflowBase = new WorkflowBase();

                //find all duplicate entries in DB
                $hashSumList = ['xyz'];
                foreach ($entries as $entry) {
                    $fullPath = $hotFolderPath . $entry;
                    $pathHashSum = sha1($fullPath);
                    $hashSumList[] = $pathHashSum;
                }
                $hashSumListChunked = array_chunk($hashSumList, 200);
                $inDbCompiled = [];
                foreach ($hashSumListChunked as $hashSumList) {
                    $inDb = $this->HotFolders->HotFolderEntries->find('list', ['keyField' => 'id', 'valueField' => 'path_hash_sum'])
                        ->select(['id', 'path_hash_sum'])
                        ->where(['path_hash_sum IN' => $hashSumList])
                        ->where(['status IS NULL'])
                        ->disableHydration()
                        ->toArray();
                    $inDbCompiled = array_merge($inDbCompiled, $inDb);
                }

                //log every entry in the DB
                foreach ($entries as $entry) {
                    $currentTime = new DateTime();
                    $fullPath = $hotFolderPath . $entry;
                    $hashSums = $WorkflowBase->getPathChecksums($fullPath);

                    //skip as already in DB
                    if (in_array($hashSums['path_hash_sum'], $inDbCompiled)) {
                        $this->io->out(__('--Skipped DB logging of Hot Folder Entry "{0}"', $entry));
                        continue;
                    }

                    $hotFolderEntry = $this->HotFolders->HotFolderEntries->newEmptyEntity();
                    $hotFolderEntry->hot_folder_id = $hotFolder->id;
                    $hotFolderEntry->path = $fullPath;
                    $hotFolderEntry->path_hash_sum = $hashSums['path_hash_sum'];
                    $hotFolderEntry->listing_hash_sum = $hashSums['listing_hash_sum'];
                    $hotFolderEntry->contents_hash_sum = $hashSums['contents_hash_sum'];
                    $hotFolderEntry->last_check_time = $currentTime;
                    $hotFolderEntry->next_check_time = (clone $currentTime)->addSeconds($hotFolder->stable_interval);

                    $saveResult = $this->HotFolders->HotFolderEntries->save($hotFolderEntry);

                    if ($saveResult) {
                        $this->io->out(__('--Actioned DB logging of Hot Folder Entry "{0}"', $entry));
                    }
                }

            }


            /*
             * --------------------------------------------------------
             * This section processes the Hot Folder Entries in the DB
             * --------------------------------------------------------
             */
            //estimate how many entries there are to process in the DB
            $toProcessCount = $this->HotFolders->HotFolderEntries->find('all')->where(['lock_code IS NULL'])->count();
            foreach (range(1, $toProcessCount) as $k => $toProcessCounter) {
                //get the next HotFolderEntry by locking and retrieving
                $currentTime = new DateTime();
                $currentTimeString = $currentTime->format("Y-m-d H:i:s");
                $rndLock = mt_rand();
                $limitQuery = $this->HotFolders->HotFolderEntries->find('all')
                    ->select(['id'], true)
                    ->where(['lock_code IS NULL', 'next_check_time <' => $currentTimeString])
                    ->orderByAsc('id')
                    ->limit(1);
                $lockedCount = $this->HotFolders->HotFolderEntries->updateAll(['lock_code' => $rndLock], ['id IN' => $limitQuery]);
                if (empty($lockedCount)) {
                    continue;
                }

                /** @var HotFolderEntry $hotFolderEntry */
                $hotFolderEntry = $this->HotFolders->HotFolderEntries->find('all')
                    ->contain(['HotFolders'])
                    ->where(['lock_code' => $rndLock])
                    ->orderByDesc('HotFolderEntries.id')
                    ->first();
                if (!$hotFolderEntry) {
                    continue;
                }

                $currentTime = new DateTime();
                $fullPath = $hotFolderEntry->path;
                $pathHashSum = sha1($fullPath);
                if (is_dir($fullPath)) {
                    $listing = $ZP->rawFileAndFolderList($fullPath);
                    $listingHashSum = sha1(json_encode($listing));
                } else {
                    $listingHashSum = $pathHashSum;
                }

                $options = [
                    'directory' => true,
                    'file' => true,
                    'sha1' => true,
                    'crc32' => false,
                    'mime' => true,
                    'size' => true,
                    'contents' => false,
                ];
                $stats = $ZP->fileStats($fullPath, null, $options);
                $contentsHashSum = sha1(json_encode($stats));

                if ($listingHashSum !== $hotFolderEntry->listing_hash_sum || $contentsHashSum !== $hotFolderEntry->contents_hash_sum) {
                    // directory listing or file contents is still changing so update last checked time and exit loop
                    $hotFolderEntry->path_hash_sum = $pathHashSum;
                    $hotFolderEntry->listing_hash_sum = $listingHashSum;
                    $hotFolderEntry->contents_hash_sum = $contentsHashSum;
                    $hotFolderEntry->last_check_time = $currentTime;
                    $hotFolderEntry->next_check_time = (clone $currentTime)->addSeconds($hotFolderEntry->hot_folder->stable_interval);
                    $hotFolderEntry->lock_code = null;
                    $this->HotFolders->HotFolderEntries->save($hotFolderEntry);
                    $this->io->warning(__("Hot Folder Entry ID:{0} is not ready for processing.", $hotFolderEntry->id));
                    continue;
                } else {
                    $this->io->success(__("Hot Folder Entry ID:{0} is ready for processing.", $hotFolderEntry->id));
                }

                //create the Errand to process the Hot Folder Entry
                $errand = $this->createErrandFromEntry($hotFolderEntry);

                if ($errand) {
                    $hotFolderEntry->errand_link = $errand->id;
                    $this->HotFolders->HotFolderEntries->save($hotFolderEntry);
                    $activityCounter++;
                }
            }

            $this->io->out(__("Processed {0} Hot Folder entries.", $activityCounter));

            //refresh the $backgroundService so can check if there have been outside changes
            $backgroundService = $this->BackgroundServices->getBackgroundServiceByName($heartbeatContext);

            //check if Background Service has been ordered to recycle or terminate
            if ($backgroundService->force_recycle) {
                $this->io->out(__("Hot Folder has been asked to recycle so quitting now."));
                $this->BackgroundServices->cleanup($backgroundService);
                return 11;
            } elseif ($backgroundService->force_shutdown) {
                $this->io->out(__("Hot Folder has been asked to shutdown so quitting now amd stopping services."));
                $this->BackgroundServices->cleanup($backgroundService);
                $this->BackgroundServicesAssistant->kill($heartbeatContext);
                return 12;
            }

            //sleep so as not to hammer FSO
            $ttlMinutes = $currentTime->diffInMinutes($retirementDate);
            $ttlSeconds = $currentTime->diffInSeconds($retirementDate);
            $ttlSeconds = $ttlSeconds - ($ttlMinutes * 60);
            if ($activityCounter === 0) {
                $timeoutSleep = $this->getSleepLength($timeoutSleep, $backgroundServiceSleep, 1.5);
                $timeoutSleep = max(1, $timeoutSleep); //at least a 1-second delay
            } else {
                $timeoutSleep = .1; //100ms delay if there are more things to do
            }
            $this->io->out(__("Hot Folder TTL is {0} minutes {1} seconds, sleeping for {2} seconds before next loop.", $ttlMinutes, $ttlSeconds, $timeoutSleep));
            usleep($timeoutSleep * 1000000);
            $this->io->out('', 1);

            $currentTime = new DateTime();
            $roundCounter++;
        }

        $this->io->out(__("Hot Folder life expectancy of {0} minutes exceeded so quitting now.", $backgroundServiceLifeExpectancy));

        $this->Heartbeats->purgePulses();
        return 0;
    }

    /**
     * @param HotFolderEntry $hotFolderEntry
     * @return bool|Errand
     */
    private function createErrandFromEntry(HotFolderEntry $hotFolderEntry): bool|Errand
    {
        $currentTime = new DateTime();
        $class = $hotFolderEntry->hot_folder->workflow;
        $classShorthand = array_reverse(explode("\\", $class))[0];
        $method = 'execute';
        $params = [
            $hotFolderEntry->id,
        ];

        $options = [
            'activation' => $currentTime,
            'expiration' => $currentTime->addMinutes(20),
            'priority' => 4,
            'name' => "Hot Folder Execution - {$classShorthand}",
            'class' => $class,
            'method' => $method,
            'parameters' => $params,
        ];

        //don't check for duplicate Errands as the Hot Folder Entries table controls what needs to be created
        $preventDuplicates = false;

        $errand = $this->Errands->createErrand($options, $preventDuplicates);
        if ($errand) {
            $this->io->success(__('--Created Errand ID:{0}.', $errand->id), 2);
        } else {
            $this->io->warning(__('--Failed to created Errand.'), 2);
        }

        return $errand;
    }

}
