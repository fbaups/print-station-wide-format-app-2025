<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\OutputProcessorsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\OutputProcessorsTable Test Case
 */
class OutputProcessorsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\OutputProcessorsTable
     */
    protected $OutputProcessors;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.OutputProcessors',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('OutputProcessors') ? [] : ['className' => OutputProcessorsTable::class];
        $this->OutputProcessors = $this->getTableLocator()->get('OutputProcessors', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->OutputProcessors);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\OutputProcessorsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\OutputProcessorsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
