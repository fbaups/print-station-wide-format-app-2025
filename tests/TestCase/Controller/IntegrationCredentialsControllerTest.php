<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\IntegrationCredentialsController Test Case
 *
 * @uses \App\Controller\Administrators\IntegrationCredentialsController
 */
class IntegrationCredentialsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.IntegrationCredentials',
    ];

    /**
     * Test beforeFilter method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::beforeFilter()
     */
    public function testBeforeFilter(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test preview method
     *
     * @return void
     * @uses \App\Controller\Administrators\IntegrationCredentialsController::preview()
     */
    public function testPreview(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
