<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\XmpieUproduceCompositionsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\XmpieUproduceCompositionsTable Test Case
 */
class XmpieUproduceCompositionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\XmpieUproduceCompositionsTable
     */
    protected $XmpieUproduceCompositions;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.XmpieUproduceCompositions',
        'app.XmpieUproduceCompositionJobs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('XmpieUproduceCompositions') ? [] : ['className' => XmpieUproduceCompositionsTable::class];
        $this->XmpieUproduceCompositions = $this->getTableLocator()->get('XmpieUproduceCompositions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->XmpieUproduceCompositions);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Model\Table\XmpieUproduceCompositionsTable::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
