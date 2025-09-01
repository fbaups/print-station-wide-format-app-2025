<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * OrderAlertsFixture
 */
class OrderAlertsFixture extends TestFixture
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
                'order_id' => 1,
                'created' => '2024-03-20 08:56:43',
                'level' => 'Lorem ip',
                'message' => 'Lorem ipsum dolor sit amet',
                'code' => 1,
            ],
        ];
        parent::init();
    }
}
