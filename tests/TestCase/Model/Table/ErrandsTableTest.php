<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ErrandsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ErrandsTable Test Case
 */
class ErrandsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ErrandsTable
     */
    protected $Errands;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Errands',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Errands') ? [] : ['className' => ErrandsTable::class];
        $this->Errands = $this->getTableLocator()->get('Errands', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Errands);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getReadyToRunCount method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::getReadyToRunCount()
     */
    public function testGetReadyToRunCount(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getNextErrand method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::getNextErrand()
     */
    public function testGetNextErrand(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildQueryForErrandsRowLock method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::buildQueryForErrandsRowLock()
     */
    public function testBuildQueryForErrandsRowLock(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildQueryForErrands method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::buildQueryForErrands()
     */
    public function testBuildQueryForErrands(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test createErrand method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::createErrand()
     */
    public function testCreateErrand(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test deleteDuplicates method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::deleteDuplicates()
     */
    public function testDeleteDuplicates(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test deleteDuplicatesQuery method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::deleteDuplicatesQuery()
     */
    public function testDeleteDuplicatesQuery(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findSubTableForCompare method
     *
     * @return void
     * @uses \App\Model\Table\ErrandsTable::findSubTableForCompare()
     */
    public function testFindSubTableForCompare(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
