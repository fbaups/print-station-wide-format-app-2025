<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * JobStatusMovementsFixture
 */
class JobStatusMovementsFixture extends TestFixture
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
                'job_id' => 1,
                'created' => '2024-03-20 08:56:23',
                'user_id' => 1,
                'job_status_from' => 1,
                'job_status_to' => 1,
                'note' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
