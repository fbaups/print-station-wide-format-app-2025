<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MessageBeaconsFixture
 */
class MessageBeaconsFixture extends TestFixture
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
                'created' => '2022-10-18 05:19:57',
                'modified' => '2022-10-18 05:19:57',
                'beacon_hash' => 'Lorem ipsum dolor sit amet',
                'beacon_url' => 'Lorem ipsum dolor sit amet',
                'beacon_data' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
