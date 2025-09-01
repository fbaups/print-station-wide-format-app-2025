<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MessagesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MessagesTable Test Case
 */
class MessagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MessagesTable
     */
    protected $Messages;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Messages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Messages') ? [] : ['className' => MessagesTable::class];
        $this->Messages = $this->getTableLocator()->get('Messages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Messages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getDefaultMessageProperties method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::getDefaultMessageProperties()
     */
    public function testGetDefaultMessageProperties(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test createMessage method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::createMessage()
     */
    public function testCreateMessage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getReadyToRunCount method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::getReadyToRunCount()
     */
    public function testGetReadyToRunCount(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getNextMessage method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::getNextMessage()
     */
    public function testGetNextMessage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildQueryForMessagesRowLock method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::buildQueryForMessagesRowLock()
     */
    public function testBuildQueryForMessagesRowLock(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildQueryForMessages method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::buildQueryForMessages()
     */
    public function testBuildQueryForMessages(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test expandEntities method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::expandEntities()
     */
    public function testExpandEntities(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test sendMessage method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::sendMessage()
     */
    public function testSendMessage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test resendMessage method
     *
     * @return void
     * @uses \App\Model\Table\MessagesTable::resendMessage()
     */
    public function testResendMessage(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
