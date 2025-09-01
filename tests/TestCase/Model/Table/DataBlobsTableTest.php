<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\DataBlobsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\DataBlobsTable Test Case
 */
class DataBlobsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\DataBlobsTable
     */
    protected $DataBlobs;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.DataBlobs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('DataBlobs') ? [] : ['className' => DataBlobsTable::class];
        $this->DataBlobs = $this->getTableLocator()->get('DataBlobs', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->DataBlobs);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\DataBlobsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\DataBlobsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
