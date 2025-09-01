<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CodeWatcherFoldersFixture
 */
class CodeWatcherFoldersFixture extends TestFixture
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
                'code_watcher_project_id' => 1,
                'created' => '2024-12-12 03:28:39',
                'modified' => '2024-12-12 03:28:39',
                'activation' => '2024-12-12 03:28:39',
                'expiration' => '2024-12-12 03:28:39',
                'auto_delete' => 1,
                'base_path' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
