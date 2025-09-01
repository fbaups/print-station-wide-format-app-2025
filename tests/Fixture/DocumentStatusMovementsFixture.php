<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DocumentStatusMovementsFixture
 */
class DocumentStatusMovementsFixture extends TestFixture
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
                'created' => '2024-03-20 08:55:48',
                'user_id' => 1,
                'document_status_from' => 1,
                'document_status_to' => 1,
                'note' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
