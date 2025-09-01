<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IntegrationCredentialsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IntegrationCredentialsTable Test Case
 */
class IntegrationCredentialsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IntegrationCredentialsTable
     */
    protected $IntegrationCredentials;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.IntegrationCredentials',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('IntegrationCredentials') ? [] : ['className' => IntegrationCredentialsTable::class];
        $this->IntegrationCredentials = $this->getTableLocator()->get('IntegrationCredentials', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->IntegrationCredentials);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\IntegrationCredentialsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\IntegrationCredentialsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
