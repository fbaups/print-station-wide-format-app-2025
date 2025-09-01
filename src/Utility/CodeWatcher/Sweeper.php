<?php

namespace App\Utility\CodeWatcher;

use App\Model\Entity\CodeWatcherFile;
use App\Model\Entity\CodeWatcherFolder;
use App\Model\Entity\CodeWatcherProject;
use App\Model\Table\CodeWatcherFilesTable;
use App\Model\Table\CodeWatcherFoldersTable;
use App\Model\Table\CodeWatcherProjectsTable;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\I18n\FrozenTime;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class Sweeper
{
    use ReturnAlerts;

    private Table|CodeWatcherProjectsTable $CodeWatcherProjects;
    private Table|CodeWatcherFoldersTable $CodeWatcherFolders;
    private Table|CodeWatcherFilesTable $CodeWatcherFiles;

    public function __construct()
    {
        $this->CodeWatcherProjects = TableRegistry::getTableLocator()->get('CodeWatcherProjects');
        $this->CodeWatcherFolders = TableRegistry::getTableLocator()->get('CodeWatcherFolders');
        $this->CodeWatcherFiles = TableRegistry::getTableLocator()->get('CodeWatcherFiles');

    }

    /**
     * Files/Folders that get filtered out of all requests
     *
     * @return string[]
     */
    public function getBasicFileFolderFilter(): array
    {
        return [
            ".idea/",
            ".git/",
            "logs/",
            "vendor/",
            "vendors/",
            "tmp/",
            ".gitattributes",
            ".gitignore",
            ".env.example",
            ".phpunit.result.cache",
            "composer.lock",
            "phpunit.xml",
        ];
    }

    /**
     * Files/Folders that get filtered out of all requests
     *
     * @return string[]
     */
    public function getVendorFilter(): array
    {
        return [
            "Dockerfile",
            "docs.Dockerfile",
            "CREDITS",
            "composer.json",
            "composer.lock",
            "psalm.xml",
            "phpstan.neon.dist",
            ".editorconfig",
            ".pullapprove.yml",
            ".gitignore",
            ".gitkeep",
        ];
    }

    public function captureFso($projectIdOrEntity)
    {
        $rawFolders = $this->findProjectFolders($projectIdOrEntity);
        $folders = [];
        foreach ($rawFolders as $rawFolder) {
            if (is_dir($rawFolder['base_path'])) {
                $folders[$rawFolder['id']] = $rawFolder['base_path'];
            }
        }

        $rejectFilesFolders = $this->getBasicFileFolderFilter();
        $otherFileNamesInVendor = $this->getVendorFilter();

        $forcedToFilter = [
            "remote_update.json",
            "version.json",
            "version_history.json",
            "internal.sqlite",
            "app_datasources.php",
            "branding.json",
            "app_local.php",
            "schema-dump-default.lock",
        ];

        $ZP = new ZipPackager();

        $filesCompiled = [];
        foreach ($folders as $folder) {
            $rawFileList = $ZP->rawFileList($folder);
            $rawFileList = $ZP->filterOutVendorExtras($rawFileList);
            $rawFileList = $ZP->filterOutByFileName($rawFileList, $otherFileNamesInVendor);
            $rawFileList = $ZP->filterOutFoldersAndFiles($rawFileList, $rejectFilesFolders);
            $rawFileList = $ZP->filterOutFoldersAndFiles($rawFileList, $forcedToFilter);
            $options = [
                'crc32' => false,
            ];
            $fsoFiles = $ZP->fileStats($folder, $rawFileList, $options);
            $fsoFiles = $this->formatStats($fsoFiles);

            $filesCompiled = array_merge($filesCompiled, $fsoFiles);
        }

        return $filesCompiled;
    }

    public function captureFsoChanges($projectIdOrEntity)
    {
        $rawFolders = $this->findProjectFolders($projectIdOrEntity);
        $folders = [];
        foreach ($rawFolders as $rawFolder) {
            if (is_dir($rawFolder['base_path'])) {
                $folders[$rawFolder['id']] = $rawFolder['base_path'];
            }
        }

        $rejectFilesFolders = $this->getBasicFileFolderFilter();
        $otherFileNamesInVendor = $this->getVendorFilter();

        $ZP = new ZipPackager();

        $insertCount = 0;

        foreach ($folders as $folderId => $folder) {
            $this->addInfoAlerts(__("Processing {0}", $folder));

            $dbFiles = $this->findFolderFiles($folderId)
                ->disableHydration()
                ->toArray();

            $startDatetime = (new FrozenTime())->setTimezone("UTC");
            $currentDatetimeLocalised = (clone $startDatetime)->setTimezone(LCL_TZ);
            $groupingDatetime = (clone $currentDatetimeLocalised)->second(0)->minute($this->roundDownToMultipleOf($currentDatetimeLocalised->minute));

            $rawFileList = $ZP->rawFileList($folder);
            $rawFileList = $ZP->filterOutVendorExtras($rawFileList);
            $rawFileList = $ZP->filterOutByFileName($rawFileList, $otherFileNamesInVendor);
            $rawFileList = $ZP->filterOutFoldersAndFiles($rawFileList, $rejectFilesFolders);
            $options = [
                'crc32' => false,
            ];
            $fsoFiles = $ZP->fileStats($folder, $rawFileList, $options, false);
            $fsoFiles = $this->formatStats($fsoFiles);
            $fsoFiles = $this->filterOutUnchangedFiles($fsoFiles, $dbFiles);

            $endTime = new FrozenTime();
            $dataCapture = [
                'read_time' => $startDatetime->diffInSeconds($endTime),
                'created' => $startDatetime->format("Y-m-d H:i:s"),
                'local_timezone' => LCL_TZ,
                'local_year' => $currentDatetimeLocalised->year,
                'local_month' => $currentDatetimeLocalised->month,
                'local_day' => $currentDatetimeLocalised->day,
                'local_hour' => $currentDatetimeLocalised->hour,
                'local_minute' => $currentDatetimeLocalised->minute,
                'local_second' => $currentDatetimeLocalised->second,
                'time_grouping_key' => $groupingDatetime->format("Y-m-d-H-i-s"),
                'base_path' => $folder,
                'code_watcher_folder_id' => $folderId,
                'stats' => $fsoFiles,
            ];

            $records = $this->formatDataCaptureAsRecords($dataCapture);

            $insertCount += $this->CodeWatcherFiles->massInsert($records);
        }

        return $insertCount;
    }

    /**
     * @param array $fsoFiles
     * @param array $dbFiles
     * @return array
     */
    private function filterOutUnchangedFiles(array $fsoFiles, array $dbFiles): array
    {
        $dbListing = [];
        foreach ($dbFiles as $dbFile) {
            $dbListing[$dbFile['path_checksum']] = $dbFile['sha1'];
        }

        $fsoListing = [];
        foreach ($fsoFiles as $fsoFile) {
            $fsoListing [$fsoFile['path_checksum']] = $fsoFile['sha1'];
        }

        //do a quick comparison of the arrays, if they are equal, no files have changed so exit
        if (serialize($dbListing) === serialize($fsoListing)) {
            return [];
        }

        $fsoKeepers = [];
        foreach ($fsoFiles as $fsoFile) {
            if (isset($dbListing[$fsoFile['path_checksum']])) {
                if ($dbListing[$fsoFile['path_checksum']] !== $fsoFile['sha1']) {
                    $fsoKeepers[] = $fsoFile;
                }
            } else {
                $fsoKeepers[] = $fsoFile;
            }
        }

        return $fsoKeepers;
    }

    /**
     * @param array $fsoFiles
     * @return array
     */
    private function formatStats(array $fsoFiles): array
    {
        $formatted = [];
        foreach ($fsoFiles as $fsoFile) {
            $basePath = TextFormatter::makeDirectoryTrailingSmartSlash($fsoFile['directory']);

            $formatted[] = [
                'path_checksum' => sha1($basePath . $fsoFile['file']),
                'base_path' => $basePath,
                'file_path' => $fsoFile['file'],
                'sha1' => $fsoFile['sha1'],
                'crc32' => $fsoFile['crc32'],
                'mime' => $fsoFile['mime'],
                'size' => $fsoFile['size'],
            ];
        }

        return $formatted;
    }

    /**
     * @param int|CodeWatcherProject $idOrEntity
     * @return SelectQuery|CodeWatcherFolder[]
     */
    private function findProjectFolders(int|CodeWatcherProject $idOrEntity): array|SelectQuery
    {
        $id = $this->CodeWatcherProjects->asId($idOrEntity);

        return $this->CodeWatcherFolders->find('all')->where(['code_watcher_project_id' => $id]);
    }

    /**
     * @param int|CodeWatcherFolder $idOrEntity
     * @return SelectQuery|CodeWatcherFile[]
     */
    private function findFolderFiles(int|CodeWatcherFolder $idOrEntity): array|SelectQuery
    {
        $id = $this->CodeWatcherFolders->asId($idOrEntity);

        //subquery to find the max created date for each path_checksum
        $subquery = $this->CodeWatcherFiles->find()
            ->from(['CodeWatcherFiles2' => 'code_watcher_files'])
            ->select(['max_created' => $this->CodeWatcherFiles->find()->func()->max('CodeWatcherFiles2.created')])
            ->where([
                'CodeWatcherFiles2.path_checksum = CodeWatcherFiles.path_checksum',
                'CodeWatcherFiles2.code_watcher_folder_id = ' . $id
            ]);
        //sqld($subquery);

        //query to fetch records where created matches the max created in the subquery
        $query = $this->CodeWatcherFiles->find()
            ->where([
                'created' => $subquery
            ])
            ->orderBy([
                'base_path' => 'ASC',
                'file_path' => 'ASC'
            ]);
        //sqld($query);

        return $query;
    }

    /**
     * @param float|int $number
     * @param int $multipleOf
     * @return int
     */
    private function roundToMultipleOf(float|int $number, int $multipleOf = 5): int
    {
        $val = round($number / $multipleOf) * $multipleOf;
        return intval($val);
    }

    /**
     * @param float|int $number
     * @param int $multipleOf
     * @return int
     */
    private function roundDownToMultipleOf(float|int $number, int $multipleOf = 5): int
    {
        $val = floor($number / $multipleOf) * $multipleOf;
        return intval($val);
    }

    /**
     * @param float|int $number
     * @param int $multipleOf
     * @return int
     */
    private function roundUpToMultipleOf(float|int $number, int $multipleOf = 5): int
    {
        $val = ceil($number / $multipleOf) * $multipleOf;
        return intval($val);
    }

    private function formatDataCaptureAsRecords(array $dataCapture): array
    {
        if (empty($dataCapture['stats'])) {
            return [];
        }

        $common = $dataCapture;
        unset($common['stats'], $common['read_time']);

        $formatted = [];
        foreach ($dataCapture['stats'] as $stats) {
            $formatted[] = array_merge($common, $stats);
        }

        return $formatted;
    }

}
