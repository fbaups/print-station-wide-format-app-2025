<?php

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Exception;

/**
 * Base Command for CLI Application.
 * Extend this as it sets up things common to all CLI Apps
 */
class AppCommand extends Command
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

        $this->setupLocalisationConstants();

        return $parser;
    }

    /**
     * Defines constants that can be used throughput the APP for localisation.
     * See https://toggen.com.au/it-tips/cakephp-4-time-zones/ for some tips
     *
     * This function is loosely mirrored between AppCommand <--> AppController
     *
     * @throws Exception
     */
    private function setupLocalisationConstants(): void
    {
        //localizations from bootstrap
        $defaultLocalisations =
            [
                'location' => '',
                'locale' => Configure::read("App.defaultLocale"),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'HH:mm:ss',
                'datetime_format' => 'yyyy-MM-dd HH:mm:ss',
                'week_start' => 'Sunday',
                'timezone' => Configure::read("App.defaultTimezone"),
            ];

        //localizations from DB
        if (Configure::check('SettingsGrouped.localization')) {
            $appLocalizations = Configure::read('SettingsGrouped.localization');
        } else {
            $appLocalizations = [];
        }

        //todo we don't have users at this stage but maybe grab the first SuperAdmin?
        $userLocalizations = [];

        $compiledLocalisations = array_merge($defaultLocalisations, $appLocalizations, $userLocalizations);

        //set the constants
        if (!defined('LCL')) {
            define('LCL', $compiledLocalisations);
        }
        if (!defined('LCL_LOCATION')) {
            define('LCL_LOCATION', $compiledLocalisations['location']);
        }
        if (!defined('LCL_LOCALE')) {
            define('LCL_LOCALE', $compiledLocalisations['locale']);
        }
        if (!defined('LCL_DF')) {
            define('LCL_DF', $compiledLocalisations['date_format']);
        }
        if (!defined('LCL_TF')) {
            define('LCL_TF', $compiledLocalisations['time_format']);
        }
        if (!defined('LCL_DTF')) {
            define('LCL_DTF', $compiledLocalisations['datetime_format']);
        }
        if (!defined('LCL_WS')) {
            define('LCL_WS', $compiledLocalisations['week_start']);
        }
        if (!defined('LCL_TZ')) {
            define('LCL_TZ', $compiledLocalisations['timezone']);
        }

        //write back a couple of values into Configure
        Configure::write("App.defaultLocale", LCL_LOCALE);

        //read company details into constants
        $company = Configure::read('SettingsGrouped.company');
        if (!defined('COMPANY_NAME')) {
            define('COMPANY_NAME', $company['company_name'] ?? '');
            define('COMPANY_ADDRESS_1', $company['company_address_1'] ?? '');
            define('COMPANY_ADDRESS_2', $company['company_address_2'] ?? '');
            define('COMPANY_SUBURB', $company['company_suburb'] ?? '');
            define('COMPANY_STATE', $company['company_state'] ?? '');
            define('COMPANY_POSTCODE', $company['company_postcode'] ?? '');
            define('COMPANY_PHONE', $company['company_phone'] ?? '');
            define('COMPANY_EMAIL', $company['company_email'] ?? '');
            define('COMPANY_WEB_ADDRESS', $company['company_web_address'] ?? '');
        }
    }


}
