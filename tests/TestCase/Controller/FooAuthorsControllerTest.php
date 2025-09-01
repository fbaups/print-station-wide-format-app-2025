<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\FooAuthorsController Test Case
 *
 * @uses \App\Controller\Administrators\FooAuthorsController
 */
class FooAuthorsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.FooAuthors',
        'app.FooRecipes',
        'app.FooAuthorsFooRecipes',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooAuthorsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooAuthorsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooAuthorsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooAuthorsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooAuthorsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test preview method
     *
     * @return void
     * @uses \App\Controller\Administrators\FooAuthorsController::preview()
     */
    public function testPreview(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
