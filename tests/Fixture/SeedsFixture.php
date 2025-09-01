<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SeedsFixture
 */
class SeedsFixture extends TestFixture
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
                'created' => '2022-10-18 05:51:22',
                'modified' => '2022-10-18 05:51:22',
                'activation' => '2022-10-18 05:51:22',
                'expiration' => '2022-10-18 05:51:22',
                'token' => 'Lorem ipsum dolor sit amet',
                'url' => 'Lorem ipsum dolor sit amet',
                'bids' => 1,
                'bid_limit' => 1,
                'user_link' => 1,
            ],
        ];
        parent::init();
    }
}
