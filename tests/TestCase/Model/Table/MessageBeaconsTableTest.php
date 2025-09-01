<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MessageBeaconsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MessageBeaconsTable Test Case
 */
class MessageBeaconsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MessageBeaconsTable
     */
    protected $MessageBeacons;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.MessageBeacons',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('MessageBeacons') ? [] : ['className' => MessageBeaconsTable::class];
        $this->MessageBeacons = $this->getTableLocator()->get('MessageBeacons', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->MessageBeacons);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MessageBeaconsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\MessageBeaconsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
