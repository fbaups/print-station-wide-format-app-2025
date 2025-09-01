<?php

namespace App\ScheduledTaskWorkflows;

use App\ScheduledTaskWorkflows\Base\ScheduledTaskBase;

/**
 * Example Scheduled Task that can be copied and modified to suit.
 */
class FooBar extends ScheduledTaskBase
{
    /**
     * Every Scheduled Task class must have an execute() method.
     * This is automatically called when the Scheduled Task is run.
     *
     * The Parent class has an execute() method to prevent failure.
     *
     * @param array $options
     * @return bool
     */
    public function execute(array $options = []): bool
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }

        //do tasks here

        return true;
    }
}
