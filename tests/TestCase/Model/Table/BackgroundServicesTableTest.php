<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\BackgroundServicesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\BackgroundServicesTable Test Case
 */
class BackgroundServicesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\BackgroundServicesTable
     */
    protected $BackgroundServices;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.BackgroundServices',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('BackgroundServices') ? [] : ['className' => BackgroundServicesTable::class];
        $this->BackgroundServices = $this->getTableLocator()->get('BackgroundServices', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->BackgroundServices);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\BackgroundServicesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\BackgroundServicesTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
