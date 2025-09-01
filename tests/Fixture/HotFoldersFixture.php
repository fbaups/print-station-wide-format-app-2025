<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * HotFoldersFixture
 */
class HotFoldersFixture extends TestFixture
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
                'created' => '2022-12-19 22:40:39',
                'modified' => '2022-12-19 22:40:39',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'path' => 'Lorem ipsum dolor sit amet',
                'is_enabled' => 1,
                'workflow' => 'Lorem ipsum dolor sit amet',
                'polling_interval' => 1,
                'stable_interval' => 1,
            ],
        ];
        parent::init();
    }
}
