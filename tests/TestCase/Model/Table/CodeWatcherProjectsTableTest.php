<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CodeWatcherProjectsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CodeWatcherProjectsTable Test Case
 */
class CodeWatcherProjectsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CodeWatcherProjectsTable
     */
    protected $CodeWatcherProjects;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.CodeWatcherProjects',
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
        $config = $this->getTableLocator()->exists('CodeWatcherProjects') ? [] : ['className' => CodeWatcherProjectsTable::class];
        $this->CodeWatcherProjects = $this->getTableLocator()->get('CodeWatcherProjects', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CodeWatcherProjects);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherProjectsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\CodeWatcherProjectsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
