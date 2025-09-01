<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * XmpieUproduceCompositionJobCallbacksFixture
 */
class XmpieUproduceCompositionJobCallbacksFixture extends TestFixture
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
                'xmpie_uproduce_composition_job_id' => 1,
                'created' => '2025-08-20 23:22:26',
                'modified' => '2025-08-20 23:22:26',
                'name' => 'Lorem ipsum dolor sit amet',
                'job_number' => 1,
                'job_guid' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ipsum dolor sit amet',
                'start' => '2025-08-20 23:22:26',
                'end' => '2025-08-20 23:22:26',
                'process_count' => 1,
            ],
        ];
        parent::init();
    }
}
