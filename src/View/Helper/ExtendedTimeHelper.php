<?php

namespace App\View\Helper;

use Cake\Chronos\ChronosDate;
use Cake\I18n\DateTime;
use Cake\View\Helper\TimeHelper as CakeTimeHelper;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * Flash helper
 */
class ExtendedTimeHelper extends CakeTimeHelper
{
    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    /**
     * Overwrite parent::format()
     * This one uses the constant LCL_TZ and LCL_DTF for default formatting
     *
     * @param DateTimeInterface|ChronosDate|int|string|null $date
     * @param array|int|string|null $format
     * @param false|string $invalid
     * @param DateTimeZone|string|null $timezone
     * @return string
     */
    public function format(DateTimeInterface|ChronosDate|DateTime|int|string|null $date, array|int|string|null $format = LCL_DTF, false|string $invalid = '', DateTimeZone|string|null $timezone = LCL_TZ): false|int|string
    {
        if (!$date) {
            return $invalid;
        }

        try {
            $time = new DateTime($date);
            return $time->setTimezone($timezone)->format($format);
        } catch (\Throwable $e) {
            return $invalid;
        }

    }


    /**
     * Overwrite parent::i18nFormat()
     * This one uses the constants LCL_TZ and LCL_LOCALE for default formatting
     *
     * @inheritDoc
     */
    public function i18nFormat($date, $format = null, $invalid = '', $timezone = LCL_TZ, $locale = LCL_LOCALE): false|int|string
    {
        if (!$date) {
            return $invalid;
        }

        try {
            $time = new DateTime($date);
            return $time->i18nFormat($format, $timezone, $locale);
        } catch (\Throwable $e) {
            return $invalid;
        }
    }


}
