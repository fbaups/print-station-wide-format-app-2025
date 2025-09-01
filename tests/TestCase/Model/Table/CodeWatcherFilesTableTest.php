<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CodeWatcherFilesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CodeWatcherFilesTable Test Case
 */
class CodeWatcherFilesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CodeWatcherFilesTable
     */
    protected $CodeWatcherFiles;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.CodeWatcherFiles',
        'app.CodeWatcherFolders',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CodeWatcherFiles') ? [] : ['className' => CodeWatcherFilesTable::class];
        $this->CodeWatcherFiles = $this->getTableLocator()->get('CodeWatcherFiles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CodeWatcherFiles);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherFilesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherFilesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherFilesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
