<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BackgroundServicesFixture
 */
class BackgroundServicesFixture extends TestFixture
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
                'created' => '2024-03-06 03:45:50',
                'modified' => '2024-03-06 03:45:50',
                'server' => 'Lorem ipsum dolor sit amet',
                'domain' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'pid' => 1,
                'appointment_date' => '2024-03-06 03:45:50',
                'retirement_date' => '2024-03-06 03:45:50',
                'termination_date' => '2024-03-06 03:45:50',
                'force_retirement' => 1,
                'force_shutdown' => 1,
                'errand_link' => 1,
                'errand_name' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
