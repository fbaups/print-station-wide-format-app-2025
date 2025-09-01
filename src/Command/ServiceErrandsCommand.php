<?php

namespace App\Command;

use App\Model\Entity\Errand;
use App\HotFolderWorkflows\Base\WorkflowBase;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Throwable;

/**
 * HotFoldersCommand command.
 * Used to process Errands in the database.
 *
 */
class ServiceErrandsCommand extends ServiceCommand
{

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

        $memLimit = ini_get('memory_limit');
        $this->io->info(__("Memory limit set to {$memLimit}"), 2);

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
            'name' => 'Started Errand Service',
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

        //determine thread number
        $threadNumber = explode("_", $this->serviceName);
        $threadNumber = array_pop($threadNumber);
        $threadNumber = intval(preg_replace('/[^0-9]/', '', $threadNumber));
        $this->io->out(__("Thread Number: {0}.", $threadNumber));

        $roundCounter = 1;
        while ($currentTime->lessThanOrEquals($retirementDate)) {
            $this->io->out(__("Errand Loop - Round {0}.", $roundCounter));

            //----START core task----------------------------------------------
            $activityCounter = $this->runNextCountErrand($threadNumber);
            $this->io->out(__("Found {0} Errands to run.", $activityCounter));
            if ($activityCounter > 0) {
                /**
                 * @var array|bool|null|Errand $task
                 */
                $task = $this->runNextErrand($threadNumber);
                if ($task) {
                    $msg = __("Completed Errand {0}:{1}.", $task->id, $task->name);
                    $this->io->info($msg);
                    $hbOptions = [
                        'context' => $heartbeatContext,
                        'name' => $msg,
                    ];
                    $this->Heartbeats->createPulse($hbOptions);
                }
            }
            //----END core task------------------------------------------------


            //refresh the $backgroundService so can check if there have been outside changes
            $backgroundService = $this->BackgroundServices->getBackgroundServiceByName($heartbeatContext);

            //check if Background Service has been ordered to recycle or terminate
            if ($backgroundService->force_recycle) {
                $this->io->out(__("Errand has been asked to recycle so quitting now."));
                $this->BackgroundServices->cleanup($backgroundService);
                return 11;
            } elseif ($backgroundService->force_shutdown) {
                $this->io->out(__("Errand has been asked to shutdown so quitting now amd stopping services."));
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
            $this->io->out(__("Errand TTL is {0} minutes {1} seconds, sleeping for {2} seconds before next loop.", $ttlMinutes, $ttlSeconds, $timeoutSleep));
            usleep($timeoutSleep * 1000000);
            $this->io->out('', 1);

            $currentTime = new DateTime();
            $roundCounter++;
        }

        $this->io->out(__("Errand life expectancy of {0} minutes exceeded so quitting now.", $backgroundServiceLifeExpectancy));

        $this->Heartbeats->purgePulses();
        return 0;
    }


    /**
     * Run count Errand
     *
     * @param null|int|int[] $threadNumber
     * @return int
     */
    private function runNextCountErrand($threadNumber = null): int
    {
        return $this->Errands->getReadyToRunCount($threadNumber);
    }


    /**
     * Run the Errand
     *
     * @param int|int[]|null $threadNumber
     * @return array|bool|Errand|null
     */
    private function runNextErrand(array|int $threadNumber = null): Errand|bool|array|null
    {
        /**
         * @var array $parameters
         * @var Errand $errand
         */

        $errand = $this->Errands->getNextErrand($threadNumber);

        if (!$errand) {
            return false;
        }

        $errand->status = 'Started';
        $errand->progress_bar = 0;
        $errand->background_service_link = getmypid();
        $errand->background_service_name = $this->serviceName;
        $errand->server = gethostname();

        try {
            $this->Errands->save($errand);
        } catch (Throwable $exception) {
            Log::write('error', __("Error updating the Errand.\r\nFile {0}\r\nLine {1}\r\n", __FILE__, __LINE__, $errand->errors_thrown));
        }

        $className = $errand->class;
        $method = $errand->method;
        $parameters = $errand->parameters;

        try {
            $returnValue = null;
            $returnMessage = null;
            $returnAlerts = [];

            //switch between a Model and Fully Qualified class
            if (str_ends_with($className, "Table")) {
                $className = str_replace("Table", "", $className);
                $Target = TableRegistry::getTableLocator()->get($className);
            } else {
                $Target = new $className();
            }

            //execute the Method in the $Target
            if (empty($parameters)) {
                $result = $Target->$method();
            } else {
                $result = $Target->$method(...$parameters);
            }

            //get return values/messages
            if (method_exists($Target, 'getReturnValue')) {
                $returnValue = $Target->getReturnValue();
            }
            if (method_exists($Target, 'getReturnMessage')) {
                $returnMessage = $Target->getReturnMessage();
            }
            if (method_exists($Target, 'getAllAlertsLogSequence')) {
                $returnAlerts = $Target->getAllAlertsLogSequence();
            }
            if (method_exists($Target, 'clearAllReturnAlerts')) {
                $Target->clearAllReturnAlerts();
            }
            $compiledMessageAndAlerts = array_merge(['message' => $returnMessage], $returnAlerts);

            $errand->return_value = $returnValue;
            $errand->return_message = $compiledMessageAndAlerts;
            $errand->completed = new DateTime('now');
            $errand->status = 'Completed';
            $errand->progress_bar = 100;


            //sometimes there is a flag to reset the Errand (e.g. something was not ready for the Errand to run and we need to back out)
            if (method_exists($Target, 'getResetErrandParams')) {
                $resetErrandParams = $Target->getResetErrandParams();
                $reset = $resetErrandParams['reset'] ?? false;
                $offset = $resetErrandParams['offset'] ?? 0;
                $include = $resetErrandParams['include'] ?? false;
                if ($reset) {
                    $this->Errands->resetErrand($errand, $offset, $include);
                }
            }

        } catch (Throwable $e) {
            $errorsThrown = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ];
            $errorsThrown = json_decode(json_encode($errorsThrown), true);

            $errand->completed = null;
            $errand->status = 'Errored';
            $errand->progress_bar = 0;
            $errand->errors_thrown = $errorsThrown;

            if ($errand->errors_retry < $errand->errors_retry_limit) {
                $errand->errors_retry = $errand->errors_retry + 1;
                $errand->started = null;
                $errand->completed = null;
                $errand->status = null;
                $errand->progress_bar = 0;
            }

        }

        try {
            $this->Errands->save($errand);
        } catch (Throwable $exception) {
            Log::write('error', __("Error updating the Errand.\r\nFile {0}\r\nLine {1}\r\n", __FILE__, __LINE__, $errand->errors_thrown));
        }

        return $errand;
    }

}
