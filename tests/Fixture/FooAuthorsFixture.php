<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FooAuthorsFixture
 */
class FooAuthorsFixture extends TestFixture
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
                'created' => '2023-07-25 10:22:27',
                'modified' => '2023-07-25 10:22:27',
                'name' => 'Lorem ipsum dolor sit amet',
                'is_active' => 1,
            ],
        ];
        parent::init();
    }
}
