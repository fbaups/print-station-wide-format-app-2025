<?php

namespace App\ScheduledTaskWorkflows\Base;

use DateTimeZone;

class CronExpression extends \Poliander\Cron\CronExpression
{
    public function __construct(string $expression, DateTimeZone|string|null $timeZone = null)
    {
        if (empty($timeZone)) {
            $timeZone = new \DateTimeZone(LCL_TZ);
        } elseif (is_string($timeZone)) {
            try {
                $timeZone = new \DateTimeZone($timeZone);
            } catch (\Throwable $exception) {
                $timeZone = new \DateTimeZone(LCL_TZ);
            }
        } elseif (!($timeZone instanceof DateTimeZone)) {
            $timeZone = new \DateTimeZone(LCL_TZ);
        }

        parent::__construct($expression, $timeZone);
    }

    /**
     * @param $start
     * @return bool|int
     */
    public function getNext($start = null): bool|int
    {
        try {
            return parent::getNext($start);
        } catch (\Throwable $exception) {
            return false;
        }
    }

}
