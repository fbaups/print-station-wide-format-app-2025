<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * XmpieUproduceCompositionJobsFixture
 */
class XmpieUproduceCompositionJobsFixture extends TestFixture
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
                'xmpie_uproduce_composition_id' => 1,
                'created' => '2025-08-20 23:22:19',
                'modified' => '2025-08-20 23:22:19',
                'job_number' => 1,
            ],
        ];
        parent::init();
    }
}
