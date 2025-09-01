<?php

namespace App\Command;

use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Log\Log;

/**
 * ScheduledTasksCommand command.
 * Used to process entries in Scheduled Tasks. An Errand is created to process the Scheduled Task entry.
 *
 */
class ServiceScheduledTasksCommand extends ServiceCommand
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
            'name' => 'Started Scheduled Task Service',
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


        $roundCounter = 1;
        while ($currentTime->lessThanOrEquals($retirementDate)) {
            $this->io->out(__("Scheduled Task Loop - Round {0}.", $roundCounter));
            $activityCounter = 0;

            $allScheduledTasksKeyed = $this->ScheduledTasks->getEnabledScheduledTasksKeyedById();
            foreach ($allScheduledTasksKeyed as $scheduledTask) {
                $scheduledTask = $this->ScheduledTasks->updateNextRunTime($scheduledTask);
                $this->io->out();
                $this->io->out(__('Processing Scheduled Task "{0}"', $scheduledTask->name), 3);

                $class = $scheduledTask->workflow;
                $classShorthand = array_reverse(explode("\\", $class))[0];
                $method = 'execute';

                $paramsPassed = json_decode($scheduledTask->parameters, JSON_OBJECT_AS_ARRAY);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $paramsPassed = [];
                }

                //because cron can run every minute, activation and expiration are only 59 seconds apart
                $options = [
                    'activation' => $scheduledTask->next_run_time,
                    'expiration' => (clone $scheduledTask->next_run_time)->addSeconds(59),
                    'priority' => 1,
                    'name' => "Scheduled Task Execution - {$classShorthand}",
                    'class' => $class,
                    'method' => $method,
                    'parameters' => [$paramsPassed],
                ];
                $errand = $this->Errands->createErrand($options, true);
                if ($errand) {
                    $this->io->out(__('--Created Errand ID:{0}.', $errand->id), 2);
                } else {
                    $this->io->out(__('--Skipping Errand creation due to duplication.'), 2);
                }

            }
            $this->io->out(__("Processed {0} Scheduled Task entries.", $activityCounter));

            //refresh the $backgroundService so can check if there have been outside changes
            $backgroundService = $this->BackgroundServices->getBackgroundServiceByName($heartbeatContext);

            //check if Background Service has been ordered to recycle or terminate
            if ($backgroundService->force_recycle) {
                $this->io->out(__("Scheduled Task has been asked to recycle so quitting now."));
                $this->BackgroundServices->cleanup($backgroundService);
                return 11;
            } elseif ($backgroundService->force_shutdown) {
                $this->io->out(__("Scheduled Task has been asked to shutdown so quitting now amd stopping services."));
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
            $this->io->out(__("Scheduled Task TTL is {0} minutes {1} seconds, sleeping for {2} seconds before next loop.", $ttlMinutes, $ttlSeconds, $timeoutSleep));
            usleep($timeoutSleep * 1000000);
            $this->io->out('', 1);

            $currentTime = new DateTime();
            $roundCounter++;
        }

        $this->io->out(__("Scheduled Task life expectancy of {0} minutes exceeded so quitting now.", $backgroundServiceLifeExpectancy));

        $this->Heartbeats->purgePulses();
        return 0;
    }

}
