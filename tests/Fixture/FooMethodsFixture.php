<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FooMethodsFixture
 */
class FooMethodsFixture extends TestFixture
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
                'foo_recipe_id' => 1,
                'created' => '2023-07-25 10:22:30',
                'modified' => '2023-07-25 10:22:30',
                'rank' => 1,
                'text' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
