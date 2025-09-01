<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CodeWatcherFoldersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CodeWatcherFoldersTable Test Case
 */
class CodeWatcherFoldersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CodeWatcherFoldersTable
     */
    protected $CodeWatcherFolders;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.CodeWatcherFolders',
        'app.CodeWatcherProjects',
        'app.CodeWatcherFiles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CodeWatcherFolders') ? [] : ['className' => CodeWatcherFoldersTable::class];
        $this->CodeWatcherFolders = $this->getTableLocator()->get('CodeWatcherFolders', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CodeWatcherFolders);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherFoldersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherFoldersTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherFoldersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
