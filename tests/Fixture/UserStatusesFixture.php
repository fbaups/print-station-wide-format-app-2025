<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UserStatusesFixture
 */
class UserStatusesFixture extends TestFixture
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
                'rank' => 1,
                'created' => '2022-12-05 06:19:18',
                'modified' => '2022-12-05 06:19:18',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'alias' => 'Lorem ipsum dolor sit amet',
                'name_status_icon' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
