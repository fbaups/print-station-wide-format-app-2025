<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * HotFolderEntriesFixture
 */
class HotFolderEntriesFixture extends TestFixture
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
                'created' => '2024-08-20 04:35:47',
                'modified' => '2024-08-20 04:35:47',
                'hot_folder_id' => 1,
                'path' => 'Lorem ipsum dolor sit amet',
                'path_hash_sum' => 'Lorem ipsum dolor sit amet',
                'listing_hash_sum' => 'Lorem ipsum dolor sit amet',
                'contents_hash_sum' => 'Lorem ipsum dolor sit amet',
                'last_check_time' => '2024-08-20 04:35:47',
                'next_check_time' => '2024-08-20 04:35:47',
                'lock_code' => 1,
            ],
        ];
        parent::init();
    }
}
