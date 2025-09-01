<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\Releases\BuildTasks;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Releases command.
 *
 */
class ReleaseBuilderCommand extends AppCommand
{
    private BuildTasks $BuildTasks;

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
        $this->BuildTasks = new BuildTasks();
        $this->BuildTasks->setArgs($args);
        $this->BuildTasks->setIo($io);

        if (empty($args->getArgumentAt(0)) || strtolower($args->getArgumentAt(0)) == 'build') {
            $result = $this->build($args, $io);

            if ($result) {
                return 0; //ok
            } else {
                return 1; //problem
            }
        }

        if (strtolower($args->getArgumentAt(0)) == 'debug_off') {
            $result = $this->debugOff($args, $io);

            if ($result) {
                return 0; //ok
            } else {
                return 1; //problem
            }
        }

        if (strtolower($args->getArgumentAt(0)) == 'debug_on') {
            $result = $this->debugOn($args, $io);

            if ($result) {
                return 0; //ok
            } else {
                return 1; //problem
            }
        }

        return 1;

    }

    private function build()
    {
        return $this->BuildTasks->build();
    }

    /**
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int
     */
    private function debugOff()
    {
        return $this->BuildTasks->debugOff();
    }

    /**
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int
     */
    private function debugOn()
    {
        return $this->BuildTasks->debugOn();
    }

}
