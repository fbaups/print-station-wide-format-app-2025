<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\HotFoldersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\HotFoldersTable Test Case
 */
class HotFoldersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\HotFoldersTable
     */
    protected $HotFolders;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
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
        $config = $this->getTableLocator()->exists('HotFolders') ? [] : ['className' => HotFoldersTable::class];
        $this->HotFolders = $this->getTableLocator()->get('HotFolders', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->HotFolders);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\HotFoldersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\HotFoldersTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
