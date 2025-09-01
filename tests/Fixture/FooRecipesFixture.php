<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FooRecipesFixture
 */
class FooRecipesFixture extends TestFixture
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
                'created' => '2023-07-25 10:22:28',
                'modified' => '2023-07-25 10:22:28',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'publish_date' => '2023-07-25 10:22:28',
                'ingredient_count' => 1,
                'method_count' => 1,
                'is_active' => 1,
                'meta' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
