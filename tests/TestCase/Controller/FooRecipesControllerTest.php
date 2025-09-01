<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\FooRecipesController Test Case
 *
 * @uses \App\Controller\Administrators\FooRecipesController
 */
class FooRecipesControllerTest extends TestCase
{
    use IntegrationTestTrait;

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
        'app.FooAuthorsFooRecipes',
        'app.FooRecipesFooTags',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooRecipesController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooRecipesController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooRecipesController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooRecipesController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooRecipesController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test preview method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooRecipesController::preview()
     */
    public function testPreview(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
