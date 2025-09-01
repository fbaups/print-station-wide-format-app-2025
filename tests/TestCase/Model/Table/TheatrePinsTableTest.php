<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TheatrePinsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TheatrePinsTable Test Case
 */
class TheatrePinsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TheatrePinsTable
     */
    protected $TheatrePins;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.TheatrePins',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('TheatrePins') ? [] : ['className' => TheatrePinsTable::class];
        $this->TheatrePins = $this->getTableLocator()->get('TheatrePins', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->TheatrePins);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\TheatrePinsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\TheatrePinsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
