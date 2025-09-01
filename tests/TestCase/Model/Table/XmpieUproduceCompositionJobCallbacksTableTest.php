<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\XmpieUproduceCompositionJobCallbacksTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\XmpieUproduceCompositionJobCallbacksTable Test Case
 */
class XmpieUproduceCompositionJobCallbacksTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\XmpieUproduceCompositionJobCallbacksTable
     */
    protected $XmpieUproduceCompositionJobCallbacks;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.XmpieUproduceCompositionJobCallbacks',
        'app.XmpieUproduceCompositionJobs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('XmpieUproduceCompositionJobCallbacks') ? [] : ['className' => XmpieUproduceCompositionJobCallbacksTable::class];
        $this->XmpieUproduceCompositionJobCallbacks = $this->getTableLocator()->get('XmpieUproduceCompositionJobCallbacks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->XmpieUproduceCompositionJobCallbacks);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionJobCallbacksTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionJobCallbacksTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionJobCallbacksTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
