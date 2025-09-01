<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FooAuthorsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FooAuthorsTable Test Case
 */
class FooAuthorsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FooAuthorsTable
     */
    protected $FooAuthors;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.FooAuthors',
        'app.FooRecipes',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FooAuthors') ? [] : ['className' => FooAuthorsTable::class];
        $this->FooAuthors = $this->getTableLocator()->get('FooAuthors', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->FooAuthors);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\FooAuthorsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\FooAuthorsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
