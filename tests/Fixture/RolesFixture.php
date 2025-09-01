<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RolesFixture
 */
class RolesFixture extends TestFixture
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
                'created' => '2022-12-05 06:19:20',
                'modified' => '2022-12-05 06:19:20',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'alias' => 'Lorem ipsum dolor sit amet',
                'session_timeout' => 1,
            ],
        ];
        parent::init();
    }
}
