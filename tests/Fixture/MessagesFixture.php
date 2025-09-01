<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MessagesFixture
 */
class MessagesFixture extends TestFixture
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
                'created' => '2023-04-25 22:38:09',
                'modified' => '2023-04-25 22:38:09',
                'type' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'activation' => '2023-04-25 22:38:09',
                'expiration' => '2023-04-25 22:38:09',
                'auto_delete' => 1,
                'started' => '2023-04-25 22:38:09',
                'completed' => '2023-04-25 22:38:09',
                'server' => 'Lorem ipsum dolor sit amet',
                'domain' => 'Lorem ipsum dolor sit amet',
                'transport' => 'Lorem ipsum dolor sit amet',
                'profile' => 'Lorem ipsum dolor sit amet',
                'layout' => 'Lorem ipsum dolor sit amet',
                'template' => 'Lorem ipsum dolor sit amet',
                'email_format' => 'Lorem ipsum dolor sit amet',
                'sender' => '',
                'email_from' => '',
                'email_to' => '',
                'email_cc' => '',
                'email_bcc' => '',
                'reply_to' => '',
                'read_receipt' => '',
                'subject' => 'Lorem ipsum dolor sit amet',
                'view_vars' => '',
                'priority' => 1,
                'headers' => '',
                'smtp_code' => 1,
                'smtp_message' => 'Lorem ipsum dolor sit amet',
                'lock_code' => 1,
                'errors_thrown' => '',
                'errors_retry' => 1,
                'errors_retry_limit' => 1,
                'beacon_hash' => 'Lorem ipsum dolor sit amet',
                'hash_sum' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
