<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SubscriptionsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SubscriptionsTable Test Case
 */
class SubscriptionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SubscriptionsTable
     */
    protected $Subscriptions;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Subscriptions',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Subscriptions') ? [] : ['className' => SubscriptionsTable::class];
        $this->Subscriptions = $this->getTableLocator()->get('Subscriptions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Subscriptions);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\SubscriptionsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\SubscriptionsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findAllSubscriptions method
     *
     * @return void
     * @uses \App\Model\Table\SubscriptionsTable::findAllSubscriptions()
     */
    public function testFindAllSubscriptions(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findmyActiveSubscriptions method
     *
     * @return void
     * @uses \App\Model\Table\SubscriptionsTable::findmyActiveSubscriptions()
     */
    public function testFindmyActiveSubscriptions(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
