<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\JobStatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\JobStatusesTable Test Case
 */
class JobStatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\JobStatusesTable
     */
    protected $JobStatuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.JobStatuses',
        'app.Jobs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('JobStatuses') ? [] : ['className' => JobStatusesTable::class];
        $this->JobStatuses = $this->getTableLocator()->get('JobStatuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->JobStatuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\JobStatusesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\JobStatusesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
