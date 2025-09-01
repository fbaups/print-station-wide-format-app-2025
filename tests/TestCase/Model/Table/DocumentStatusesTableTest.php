<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DocumentStatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DocumentStatusesTable Test Case
 */
class DocumentStatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DocumentStatusesTable
     */
    protected $DocumentStatuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.DocumentStatuses',
        'app.Documents',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('DocumentStatuses') ? [] : ['className' => DocumentStatusesTable::class];
        $this->DocumentStatuses = $this->getTableLocator()->get('DocumentStatuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->DocumentStatuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\DocumentStatusesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\DocumentStatusesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
