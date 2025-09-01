<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FooRecipesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FooRecipesTable Test Case
 */
class FooRecipesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FooRecipesTable
     */
    protected $FooRecipes;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.FooRecipes',
        'app.FooIngredients',
        'app.FooMethods',
        'app.FooRatings',
        'app.FooAuthors',
        'app.FooTags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FooRecipes') ? [] : ['className' => FooRecipesTable::class];
        $this->FooRecipes = $this->getTableLocator()->get('FooRecipes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->FooRecipes);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\FooRecipesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\FooRecipesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
