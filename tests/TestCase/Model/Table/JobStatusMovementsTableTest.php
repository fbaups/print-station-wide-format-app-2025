<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\JobStatusMovementsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\JobStatusMovementsTable Test Case
 */
class JobStatusMovementsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\JobStatusMovementsTable
     */
    protected $JobStatusMovements;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.JobStatusMovements',
        'app.Jobs',
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
        $config = $this->getTableLocator()->exists('JobStatusMovements') ? [] : ['className' => JobStatusMovementsTable::class];
        $this->JobStatusMovements = $this->getTableLocator()->get('JobStatusMovements', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->JobStatusMovements);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\JobStatusMovementsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\JobStatusMovementsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\JobStatusMovementsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
