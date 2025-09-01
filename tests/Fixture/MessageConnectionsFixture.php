<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MessageConnectionsFixture
 */
class MessageConnectionsFixture extends TestFixture
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
                'created' => '2022-12-08 23:47:49',
                'message_link' => 1,
                'direction' => 'Lorem ip',
                'user_link' => 1,
            ],
        ];
        parent::init();
    }
}
