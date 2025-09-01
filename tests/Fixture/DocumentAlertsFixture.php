<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DocumentAlertsFixture
 */
class DocumentAlertsFixture extends TestFixture
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
                'document_id' => 1,
                'created' => '2024-03-20 08:55:46',
                'level' => 'Lorem ip',
                'message' => 'Lorem ipsum dolor sit amet',
                'code' => 1,
            ],
        ];
        parent::init();
    }
}
