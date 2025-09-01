<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\HeartbeatsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\HeartbeatsTable Test Case
 */
class HeartbeatsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\HeartbeatsTable
     */
    protected $Heartbeats;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Heartbeats',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Heartbeats') ? [] : ['className' => HeartbeatsTable::class];
        $this->Heartbeats = $this->getTableLocator()->get('Heartbeats', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Heartbeats);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\HeartbeatsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\HeartbeatsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
