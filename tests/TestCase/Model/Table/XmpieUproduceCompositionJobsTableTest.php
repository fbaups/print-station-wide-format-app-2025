<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\XmpieUproduceCompositionJobsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\XmpieUproduceCompositionJobsTable Test Case
 */
class XmpieUproduceCompositionJobsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\XmpieUproduceCompositionJobsTable
     */
    protected $XmpieUproduceCompositionJobs;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.XmpieUproduceCompositionJobs',
        'app.XmpieUproduceCompositions',
        'app.XmpieUproduceCompositionJobCallbacks',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('XmpieUproduceCompositionJobs') ? [] : ['className' => XmpieUproduceCompositionJobsTable::class];
        $this->XmpieUproduceCompositionJobs = $this->getTableLocator()->get('XmpieUproduceCompositionJobs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->XmpieUproduceCompositionJobs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionJobsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionJobsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionJobsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
