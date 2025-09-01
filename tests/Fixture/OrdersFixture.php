<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OrdersFixture
 */
class OrdersFixture extends TestFixture
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
                'guid' => 'Lorem ipsum dolor sit amet',
                'order_status_id' => 1,
                'created' => '2024-03-20 08:56:48',
                'modified' => '2024-03-20 08:56:48',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'external_order_number' => 'Lorem ipsum dolor sit amet',
                'external_creation_date' => '2024-03-20 08:56:48',
                'priority' => 1,
            ],
        ];
        parent::init();
    }
}
