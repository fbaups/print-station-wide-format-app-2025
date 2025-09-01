<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DocumentsFixture
 */
class DocumentsFixture extends TestFixture
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
                'job_id' => 1,
                'document_status_id' => 1,
                'created' => '2024-03-20 08:55:51',
                'modified' => '2024-03-20 08:55:51',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'artifact_token' => 'Lorem ipsum dolor sit amet',
                'external_document_number' => 'Lorem ipsum dolor sit amet',
                'external_creation_date' => '2024-03-20 08:55:51',
                'priority' => 1,
            ],
        ];
        parent::init();
    }
}
