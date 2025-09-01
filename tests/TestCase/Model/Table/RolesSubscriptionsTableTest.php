<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RolesSubscriptionsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RolesSubscriptionsTable Test Case
 */
class RolesSubscriptionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RolesSubscriptionsTable
     */
    protected $RolesSubscriptions;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.RolesSubscriptions',
        'app.Subscriptions',
        'app.Roles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('RolesSubscriptions') ? [] : ['className' => RolesSubscriptionsTable::class];
        $this->RolesSubscriptions = $this->getTableLocator()->get('RolesSubscriptions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->RolesSubscriptions);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\RolesSubscriptionsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\RolesSubscriptionsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\RolesSubscriptionsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
