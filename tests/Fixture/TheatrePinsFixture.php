<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TheatrePinsFixture
 */
class TheatrePinsFixture extends TestFixture
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
                'created' => '2025-06-11 05:08:28',
                'modified' => '2025-06-11 05:08:28',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'pin_code' => 'Lorem ip',
                'user_link' => 1,
            ],
        ];
        parent::init();
    }
}
