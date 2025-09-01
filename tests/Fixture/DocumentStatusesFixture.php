<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DocumentStatusesFixture
 */
class DocumentStatusesFixture extends TestFixture
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
                'sort' => 1,
                'created' => '2024-03-20 08:55:49',
                'modified' => '2024-03-20 08:55:49',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'allow_from_status' => 'Lorem ipsum dolor sit amet',
                'allow_to_status' => 'Lorem ipsum dolor sit amet',
                'icon' => 'Lorem ipsum dolor sit amet',
                'hex_code' => 'Lorem ip',
            ],
        ];
        parent::init();
    }
}
