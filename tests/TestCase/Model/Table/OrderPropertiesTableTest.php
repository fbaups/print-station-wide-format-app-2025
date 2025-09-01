<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\OrderPropertiesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\OrderPropertiesTable Test Case
 */
class OrderPropertiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\OrderPropertiesTable
     */
    protected $OrderProperties;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.OrderProperties',
        'app.Orders',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('OrderProperties') ? [] : ['className' => OrderPropertiesTable::class];
        $this->OrderProperties = $this->getTableLocator()->get('OrderProperties', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->OrderProperties);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\OrderPropertiesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\OrderPropertiesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\OrderPropertiesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
