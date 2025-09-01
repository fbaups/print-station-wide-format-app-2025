<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArticleStatusesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArticleStatusesTable Test Case
 */
class ArticleStatusesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArticleStatusesTable
     */
    protected $ArticleStatuses;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.ArticleStatuses',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ArticleStatuses') ? [] : ['className' => ArticleStatusesTable::class];
        $this->ArticleStatuses = $this->getTableLocator()->get('ArticleStatuses', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ArticleStatuses);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ArticleStatusesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\ArticleStatusesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
