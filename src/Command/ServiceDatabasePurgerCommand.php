<?php

namespace App\Command;

use App\Model\Table\ArtifactsTable;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\DateTime;
use Cake\Log\Log;

/**
 * DatabasePurgerCommand command.
 * Used to clean the database of expired data or data that meets a specific criteria for removal.
 *
 * @property ArtifactsTable $Artifacts
 *
 */
class ServiceDatabasePurgerCommand extends ServiceCommand
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
            'name' => 'Started Database Purger Service',
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
            $this->io->out(__("Deletion Loop - Round {0}.", $roundCounter));
            $activityCounter = 0;

            //===Expired Heartbeats==========================================================
            $count = $this->Heartbeats->deleteExpiredForce();
            $this->io->out(__("Deleted {0} expired Heartbeats.", $count));
            $activityCounter = $activityCounter + $count;
            //===Expired Heartbeats==========================================================


            //===Expired Logs================================================================
            $count = $this->ApplicationLogs->deleteExpired();
            $this->io->out(__("Deleted {0} expired ApplicationLogs.", $count));
            $activityCounter = $activityCounter + $count;
            //===============================================================================


            //===Expired Audits==============================================================
            $count = $this->Audits->deleteExpired();
            $this->io->out(__("Deleted {0} expired Audits.", $count));
            $activityCounter = $activityCounter + $count;
            //===============================================================================


            //===Expired Messages============================================================
            $count = $this->Messages->deleteExpired();
            $this->io->out(__("Deleted {0} expired Messages.", $count));
            $activityCounter = $activityCounter + $count;
            //===============================================================================


            //===Expired Errands=============================================================
            $count = $this->Errands->deleteExpired();
            $this->io->out(__("Deleted {0} expired Errands.", $count));
            $activityCounter = $activityCounter + $count;
            //===============================================================================


            //===Expired Artifacts===========================================================
            $timerStart = new DateTime();
            $expiredDeletionCount = 0;

            foreach (range(1, 1000) as $loop) {
                $expiredDeletionCount += $this->Artifacts->deleteTopExpired(200);
            }
            $activityCounter = $activityCounter + $expiredDeletionCount;

            $timerEnd = new DateTime();
            $timerDiff = $timerEnd->diffInSeconds($timerStart);
            $msg = __("Deleted {0} expired Artifacts in {1} seconds.", $expiredDeletionCount, $timerDiff);
            $this->io->out($msg);

            $hbOptions = [
                'context' => $heartbeatContext,
                'name' => __($msg)
            ];
            $this->Heartbeats->createPulse($hbOptions);
            //===============================================================================


            //===Missing Artifacts===========================================================
            $timerStart = new DateTime();
            $missingDeletionCount = 0;

            foreach (range(1, 1000) as $loop) {
                $missingDeletionCount += $this->Artifacts->deleteHasMissingArtifact(200);
            }
            $activityCounter = $activityCounter + $missingDeletionCount;

            $timerEnd = new DateTime();
            $timerDiff = $timerEnd->diffInSeconds($timerStart);
            $msg = __("Deleted {0} missing Artifacts in {1} seconds.", $missingDeletionCount, $timerDiff);
            $this->io->out($msg);

            $hbOptions = [
                'context' => $heartbeatContext,
                'name' => $msg
            ];
            $this->Heartbeats->createPulse($hbOptions);
            //===============================================================================

            //refresh the $backgroundService so can check if there have been outside changes
            $backgroundService = $this->BackgroundServices->getBackgroundServiceByName($heartbeatContext);

            //check if Background Service has been ordered to recycle or terminate
            if ($backgroundService->force_recycle) {
                $this->io->out(__("Database Purger has been asked to recycle so quitting now."));
                $this->BackgroundServices->cleanup($backgroundService);
                return 11;
            } elseif ($backgroundService->force_shutdown) {
                $this->io->out(__("Database Purger has been asked to shutdown so quitting now amd stopping services."));
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
            $this->io->out(__("Database Purger TTL is {0} minutes {1} seconds, sleeping for {2} seconds before next loop.", $ttlMinutes, $ttlSeconds, $timeoutSleep));
            usleep($timeoutSleep * 1000000);
            $this->io->out('', 1);

            $currentTime = new DateTime();
            $roundCounter++;
        }

        $this->io->out(__("Database Purger life expectancy of {0} minutes exceeded so quitting now.", $backgroundServiceLifeExpectancy));

        $this->Heartbeats->purgePulses();
        return 0;
    }

}
