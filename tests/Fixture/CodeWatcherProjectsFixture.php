<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CodeWatcherProjectsFixture
 */
class CodeWatcherProjectsFixture extends TestFixture
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
                'created' => '2024-12-12 03:28:40',
                'modified' => '2024-12-12 03:28:40',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'activation' => '2024-12-12 03:28:40',
                'expiration' => '2024-12-12 03:28:40',
                'auto_delete' => 1,
                'enable_tracking' => 1,
            ],
        ];
        parent::init();
    }
}
