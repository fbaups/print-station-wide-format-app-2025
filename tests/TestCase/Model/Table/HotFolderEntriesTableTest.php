<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\HotFolderEntriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\HotFolderEntriesTable Test Case
 */
class HotFolderEntriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\HotFolderEntriesTable
     */
    protected $HotFolderEntries;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.HotFolderEntries',
        'app.HotFolders',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('HotFolderEntries') ? [] : ['className' => HotFolderEntriesTable::class];
        $this->HotFolderEntries = $this->getTableLocator()->get('HotFolderEntries', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->HotFolderEntries);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\HotFolderEntriesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\HotFolderEntriesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\HotFolderEntriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
