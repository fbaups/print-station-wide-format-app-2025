<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArticlesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArticlesTable Test Case
 */
class ArticlesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArticlesTable
     */
    protected $Articles;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Articles',
        'app.ArticleStatuses',
        'app.Roles',
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
        $config = $this->getTableLocator()->exists('Articles') ? [] : ['className' => ArticlesTable::class];
        $this->Articles = $this->getTableLocator()->get('Articles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Articles);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ArticlesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\ArticlesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test reformatEntity method
     *
     * @return void
     * @uses \App\Model\Table\ArticlesTable::reformatEntity()
     */
    public function testReformatEntity(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test extractImagesFromBodyText method
     *
     * @return void
     * @uses \App\Model\Table\ArticlesTable::extractImagesFromBodyText()
     */
    public function testExtractImagesFromBodyText(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
