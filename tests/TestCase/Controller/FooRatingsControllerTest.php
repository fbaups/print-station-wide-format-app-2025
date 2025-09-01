<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\FooRatingsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\FooRatingsController Test Case
 *
 * @uses \App\Controller\FooRatingsController
 */
class FooRatingsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.FooRatings',
        'app.FooRecipes',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\FooRatingsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\FooRatingsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\FooRatingsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\FooRatingsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\FooRatingsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test preview method
     *
     * @return void
     * @uses \App\Controller\FooRatingsController::preview()
     */
    public function testPreview(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
