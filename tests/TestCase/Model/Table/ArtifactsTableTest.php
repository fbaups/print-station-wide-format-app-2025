<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArtifactsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArtifactsTable Test Case
 */
class ArtifactsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArtifactsTable
     */
    protected $Artifacts;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.Artifacts',
        'app.ArtifactMetadata',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Artifacts') ? [] : ['className' => ArtifactsTable::class];
        $this->Artifacts = $this->getTableLocator()->get('Artifacts', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Artifacts);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ArtifactsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\ArtifactsTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
