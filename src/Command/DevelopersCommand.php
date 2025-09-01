<?php
declare(strict_types=1);

namespace App\Command;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Network\CACert;
use App\Utility\Releases\BuildTasks;
use App\Utility\Releases\RemoteUpdateServer;
use arajcany\ToolBox\ZipPackager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Developers command.
 */
class DevelopersCommand extends AppCommand
{
    use ReturnAlerts;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
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
        $this->alerts();
    }

    /**
     * Function tests the merging of Alerts from an instantiated class
     * @return void
     */
    private function alerts()
    {
        $this->addInfoAlerts("Starting the CA Cert checking process.");
        $CA = new CACert();

        $path = $CA->getCertPath();
        $this->addInfoAlerts("CA Cert path: {$path}.");
        $this->mergeAlerts($CA->getAllAlertsForMerge());

        $this->addInfoAlerts("Ending the CA Cert checking process.");

        dump($this->getAllAlertsLogSequence());

    }

    private function out(ConsoleIo $io)
    {
        $io->out("This is the 'OUT' message.");
        $io->error("This is the 'ERROR' message.");
        $io->warning("This is the 'WARNING' message.");
        $io->success("This is the 'SUCCESS' message.");
        $io->info("This is the 'INFO' message.");
        $io->err("This is the 'ERR' message.");
        $io->comment("This is the 'COMMENT' message.");
        $io->abort("This is the 'ABORT' message.");
    }

    private function cacert()
    {
        $CACERT = new CACert();

    }

    private function check()
    {
        print_r("Check 123");
    }

    private function services()
    {
        $BSA = new BackgroundServicesAssistant();
        $services = $BSA->_getServices(true);
        foreach ($services as $service) {
            if (!in_array($service['state'], ['RUNNING', 'STOPPED'])) {
                $msg = date("Y-m-d H:i:s") . " {$service['name']} {$service['state']}\r\n";
                print_r($msg);
            }
        }
    }

    private function zip()
    {
        $ZP = new ZipPackager();

        $folder = APP;
        $ZP->setVerbose(true);

        $options = [
            'crc32' => false,
        ];
        $stats = $ZP->fileStats($folder, null, $options, false);

        file_put_contents(TMP . "output.json", json_encode($stats, JSON_PRETTY_PRINT));
    }
}
