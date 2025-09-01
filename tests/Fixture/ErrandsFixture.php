<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ErrandsFixture
 */
class ErrandsFixture extends TestFixture
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
                'created' => '2023-07-27 00:20:35',
                'modified' => '2023-07-27 00:20:35',
                'activation' => '2023-07-27 00:20:35',
                'expiration' => '2023-07-27 00:20:35',
                'auto_delete' => 1,
                'wait_for_link' => 1,
                'server' => 'Lorem ipsum dolor sit amet',
                'domain' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'background_service_link' => 1,
                'background_service_name' => 'Lorem ipsum dolor sit amet',
                'class' => 'Lorem ipsum dolor sit amet',
                'method' => 'Lorem ipsum dolor sit amet',
                'parameters' => '',
                'status' => 'Lorem ipsum dolor sit amet',
                'started' => '2023-07-27 00:20:35',
                'completed' => '2023-07-27 00:20:35',
                'progress_bar' => 1,
                'priority' => 1,
                'return_value' => 1,
                'return_message' => '',
                'errors_thrown' => '',
                'errors_retry' => 1,
                'errors_retry_limit' => 1,
                'hash_sum' => 'Lorem ipsum dolor sit amet',
                'lock_to_thread' => 1,
            ],
        ];
        parent::init();
    }
}
