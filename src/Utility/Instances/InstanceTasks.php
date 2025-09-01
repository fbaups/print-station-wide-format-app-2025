<?php

namespace App\Utility\Instances;

use App\BackgroundServices\BackgroundServicesAssistant;
use App\Model\Entity\Errand;
use App\Model\Table\ErrandsTable;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Cache\Cache;
use Cake\Console\ConsoleIo;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\I18n\DateTime;
use Migrations\Migrations;

class InstanceTasks
{
    use ReturnAlerts;

    private array $log = [];
    private ConsoleIo|null $io = null;
    private BackgroundServicesAssistant $BackgroundServices;

    public function __construct()
    {
        $this->io = new ConsoleIo();
        $this->BackgroundServices = new BackgroundServicesAssistant();
    }

    /**
     * @param mixed $io
     */
    public function setIo(ConsoleIo $io)
    {
        $this->io = $io;
    }

    /**
     * Auto DB Migrations
     *
     * @return bool
     */
    public function performMigrations(): bool
    {
        $migrate = false;
        $performMigrationFlag = false;

        //connect to the DB
        $Conn = ConnectionManager::get('default');

        if (!$Conn) {
            return false;
        }

        $migrations = new Migrations();
        $status = $migrations->status();

        if (!empty($status)) {
            foreach ($status as $state) {
                if ($state['status'] == 'down') {
                    $performMigrationFlag = true;
                }
            }
        }

        if ($performMigrationFlag) {
            $migrate = $migrations->migrate();
        }

        return $migrate;
    }

    /**
     * @param $remoteUpdateServerZipFilePath
     * @return bool
     */
    public function performUpgrade($remoteUpdateServerZipFilePath): bool
    {
        $time_start = microtime(true);
        $this->addSuccessAlerts(__('Started the Upgrade process.'));

        $options = ['verify' => false, 'timeout' => 60];
        $zipFileContents = file_get_contents_guzzle($remoteUpdateServerZipFilePath, $options);

        $zipFilePathName = TMP . pathinfo($remoteUpdateServerZipFilePath, PATHINFO_BASENAME);
        if ($zipFileContents) {
            $this->addSuccessAlerts(__('Downloaded the upgrade file.'));
            file_put_contents($zipFilePathName, $zipFileContents);
        } else {
            $this->addDangerAlerts(__('Sorry, there was a problem with the upgrade file.',));
            return false;
        }

        $baseExtractDir = TextFormatter::makeDirectoryTrailingForwardSlash(ROOT);

        $count = $this->BackgroundServices->kill('all', false);
        if ($count > 0) {
            $this->addSuccessAlerts(__('Stopped {0} Background Services.', $count));
        }

        $zipPackager = new ZipPackager();

        //fso stats
        $fsoStats = $zipPackager->fileStats($baseExtractDir, null, $options, false);
        $this->addSuccessAlerts(__('Indexed the installation directory.'));

        //before extraction
        $diffReportBefore = $zipPackager->getZipFsoDifference($zipFilePathName, $baseExtractDir, true);
        $this->addSuccessAlerts(__('Created before state report.'));

        //extract FILES
        $unzipResult = $zipPackager->extractZipDifference($zipFilePathName, $baseExtractDir, true);
        $this->addSuccessAlerts(__('Extracted newer files from Zip update.'));

        //after extraction
        $diffReportAfter = $zipPackager->getZipFsoDifference($zipFilePathName, $baseExtractDir, true);
        $this->addSuccessAlerts(__('Created after state report.'));

        //remove unused files
        $removeList = $diffReportAfter['fsoExtra'];
        $removeList = str_replace($baseExtractDir, "", $removeList);
        $ignoreFilesFolders = [
            "config/app.php",
            "config/app_datasources.php",
            "config/app_local.php",
            "config/cacert.pem",
            "config/Stub_DB.sqlite",
            "bin/BackgroundServices/nssm.exe",
            "src/HotFolderWorkflows/FooBar.php",
            "logs/",
            "tmp/",
            "web.xml",
            "web.config",
        ];
        $removeList = $zipPackager->filterOutFoldersAndFiles($removeList, $ignoreFilesFolders);
        $removedCounter = 0;
        foreach ($removeList as $file) {
            if (unlink($baseExtractDir . $file)) {
                $removedCounter++;
            }
        }
        $this->addSuccessAlerts(__('Removed unnecessary files.'));

        if (!is_file($baseExtractDir . "web.config") && is_file($baseExtractDir . "web.xml")) {
            copy($baseExtractDir . "web.xml", $baseExtractDir . "web.config");
        }

        $msg = '';
        if ($unzipResult['status']) {
            $msg .= __('Zip update extracted successfully. ');
        } else {
            $msg .= __('Zip update extracted with errors. ');
        }

        $msg .= __('Upgraded {0} files. ', count($diffReportBefore['fsoChanged']));
        $msg .= __('Added {0} files. ', count($diffReportBefore['fsoMissing']));
        $msg .= __('Removed {0} files. ', $removedCounter);
        $msg = trim($msg);

        if ($unzipResult['status']) {
            $this->addSuccessAlerts($msg);
        } else {
            $this->addDangerAlerts($msg);
        }

        //perform DB Migrations
        $dbDriver = (ConnectionManager::get('default'))->config()['driver'];
        if ($dbDriver !== 'Dummy') {
            if ($this->performMigrations()) {
                $this->addInfoAlerts(__('The Database was upgraded to the latest version.'));
            }
        }

        //clear the Cache
        try {
            Cache::clearAll();
            $this->addSuccessAlerts(__('Cache cleared.'));
        } catch (\Throwable $exception) {
            $this->addWarningAlerts(__('Could not clear the cache.'));
        }

        $count = $this->BackgroundServices->start('were-running', false);
        if ($count > 0) {
            $this->addSuccessAlerts(__('Started {0} Background Services.', $count));
        }

        $time_end = microtime(true);
        $time_total = round($time_end - $time_start);
        $this->addSuccessAlerts(__('Upgrade took {0} seconds.', $time_total));

        //detailed logging
        $detailedLog = [
            'log' => $this->getAllAlertsLogSequence(),
            '$remoteUpdateServerZipFilePath' => $remoteUpdateServerZipFilePath,
            '$baseExtractDir' => $baseExtractDir,
            '$ignoreFilesFolders' => $ignoreFilesFolders,
            '$diffReportBefore' => $diffReportBefore,
            '$unzipResult' => $unzipResult,
            '$diffReportAfter' => $diffReportAfter,
            '$removeList' => $removeList,
        ];
        file_put_contents(LOGS . 'upgrade.log', json_encode($detailedLog, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Will return the current Errand for generating the fsoStats.
     * Interrogate the Errand to see if it has completed or is running.
     *
     * @return Errand|false
     */
    public function generateFsoStatsErrand(): Errand|false
    {
        /** @var ErrandsTable $Errands */
        $Errands = TableRegistry::getTableLocator()->get('Errands');

        $errandId = Cache::read('FSO.stats.errandId', 'one_hour');
        if ($errandId) {
            /** @var Errand $errand */
            $errand = $Errands->find('all')
                ->where(['id' => $errandId])
                ->first();
            if ($errand) {
                return $errand;
            }
        }

        $baseExtractDir = TextFormatter::makeDirectoryTrailingForwardSlash(ROOT);
        $class = "\\arajcany\\ToolBox\\ZipPackager";
        $fsoStatsOptions = [
            'directory' => true,
            'file' => true,
            'sha1' => false,
            'crc32' => true,
            'mime' => true,
            'size' => true,
            'contents' => false,
        ];
        $options = [
            'activation' => new DateTime(),
            'expiration' => (new DateTime())->addHours(1),
            'name' => 'Generate FSO Stats',
            'class' => $class,
            'method' => 'fileStats',
            'parameters' => [$baseExtractDir, null, $fsoStatsOptions, false],
        ];

        $errand = $Errands->createErrand($options);

        if ($errand) {
            Cache::write('FSO.stats.errandId', $errand->id, 'one_hour');
            return $errand;
        } else {
            $currentTimestamp = date("Y-m-d H:i:s");
            $errand = $Errands->find('all')
                ->where(['activation <=' => $currentTimestamp, 'expiration >=' => $currentTimestamp,])
                ->first();
            if ($errand) {
                Cache::write('FSO.stats.errandId', $errand->id, 'one_hour');
                return $errand;
            }
        }

        //something went wrong, could not find or generate an errand
        return false;
    }

    /**
     * Get the php.exe file based on the fast-cgi exe for IIS web execution
     *
     * @return false|string
     */
    public function getPhpBinary(): false|string
    {
        $phpFastCgiBinary = PHP_BINARY;
        $phpExeBinary = str_replace("php-cgi.exe", "php.exe", $phpFastCgiBinary);
        if (is_file($phpExeBinary)) {
            return $phpExeBinary;
        } else {
            return false;
        }
    }

    /**
     * @return false|string
     */
    public function getPhpBinaryVersion(): false|string
    {
        $phpFastCgiBinary = $this->getPhpBinary();
        $cmd = __("\"{$phpFastCgiBinary}\" -v");
        exec($cmd, $out, $ret);

        if (!isset($out[0])) {
            return false;
        }

        $version = explode(" (cli) ", $out[0])[0];
        $version = str_replace(['php', 'PHP'], "", $version);

        return trim($version);
    }

    /**
     * Get a list of PHP binaries based on the 'where' cmd
     *
     * @return false|array
     */
    public function getPhpBinaries(): false|array
    {
        exec('where php', $phpExe, $ret);

        if (isset($phpExe[0])) {
            if (str_contains($phpExe[0], 'INFO: Could not find files for the given pattern(s).')) {
                return false;
            }
        }

        asort($phpExe);

        return $phpExe;
    }


    /**
     * Get the latest PHP binary. May not match the version IIS is running.
     *
     * @param string $fallback
     * @return string
     */
    private function getMostRecentPhpExe(string $fallback = ''): string
    {
        $phpExe = $this->getPhpBinaries();

        if (!$phpExe) {
            return $fallback;
        }

        $phpExe = array_reverse($phpExe);

        $phpExe = ($phpExe[0]) ?? $fallback;

        return $phpExe;
    }


    /**
     * Get the Composer PHAR location
     *
     * @return false|string
     */
    public function getComposerBinary(): false|string
    {
        $cmd = __("where composer");
        exec($cmd, $out, $ret);

        if (!isset($out[0])) {
            return false;
        }
        $composerBinary = pathinfo($out[0], PATHINFO_DIRNAME) . "\composer.phar";

        if (is_file($composerBinary)) {
            return $composerBinary;
        } else {
            return false;
        }
    }
}
