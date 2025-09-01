<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CodeWatcherFilesFixture
 */
class CodeWatcherFilesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'code_watcher_folder_id' => 1,
                'created' => '2024-12-12 03:28:39',
                'local_timezone' => 'Lorem ipsum dolor sit amet',
                'local_year' => 1,
                'local_month' => 1,
                'local_day' => 1,
                'local_hour' => 1,
                'local_minute' => 1,
                'local_second' => 1,
                'time_grouping_key' => 'Lorem ipsum dolor ',
                'path_checksum' => 'Lorem ipsum dolor sit amet',
                'base_path' => 'Lorem ipsum dolor sit amet',
                'file_path' => 'Lorem ipsum dolor sit amet',
                'sha1' => 'Lorem ipsum dolor sit amet',
                'crc32' => 'Lorem ipsum dolor ',
                'mime' => 'Lorem ipsum dolor sit amet',
                'size' => 1,
            ],
        ];
        parent::init();
    }
}
