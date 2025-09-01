<?php

namespace App\Utility\DateTime;

class DateTime extends \Cake\I18n\DateTime
{

    /**
     * Returns a UNIX timestamp in seconds.
     *
     * @return int UNIX timestamp
     */
    public function toUnixSeconds(): int
    {
        return intval($this->format('U'));
    }

    /**
     * Returns a UNIX timestamp with milliseconds (1,000).
     *
     * @return int UNIX timestamp
     */
    public function toUnixMilliseconds(): int
    {
        $micro = $this->micro;
        $milli = str_pad(round($micro / 1000), 3, 0, STR_PAD_LEFT);
        return intval($this->format('U') . $milli);
    }

    /**
     * Returns a UNIX timestamp with microseconds (1,000,000).
     *
     * @return int UNIX timestamp
     */
    public function toUnixMicroseconds(): int
    {
        $micro = $this->micro;
        return intval($this->format('U') . $micro);
    }

}
