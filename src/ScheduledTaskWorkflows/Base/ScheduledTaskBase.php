<?php

namespace App\ScheduledTaskWorkflows\Base;

use App\Utility\Feedback\ReturnAlerts;
use Cake\Console\ConsoleIo;

class ScheduledTaskBase
{
    use ReturnAlerts;

    protected ConsoleIo $io;

    public function __construct()
    {
        $this->io = new ConsoleIo();
    }

    /**
     * Default execute() so that Scheduled Tasks don't fail
     *
     * @param array $options
     * @return bool
     */
    public function execute(array $options = []): bool
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }

        return true;
    }


    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {

    }
}
