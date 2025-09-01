<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\CheckDatabaseDriversComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Component\CheckDatabaseDriversComponent Test Case
 */
class CheckDatabaseDriversComponentTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Controller\Component\CheckDatabaseDriversComponent
     */
    protected $CheckDatabaseDrivers;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->CheckDatabaseDrivers = new CheckDatabaseDriversComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->CheckDatabaseDrivers);

        parent::tearDown();
    }
}
