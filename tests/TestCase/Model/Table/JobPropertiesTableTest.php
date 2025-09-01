<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\JobPropertiesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\JobPropertiesTable Test Case
 */
class JobPropertiesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\JobPropertiesTable
     */
    protected $JobProperties;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.JobProperties',
        'app.Jobs',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('JobProperties') ? [] : ['className' => JobPropertiesTable::class];
        $this->JobProperties = $this->getTableLocator()->get('JobProperties', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->JobProperties);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\JobPropertiesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\JobPropertiesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\JobPropertiesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
