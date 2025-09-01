<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ArtifactMetadataTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ArtifactMetadataTable Test Case
 */
class ArtifactMetadataTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ArtifactMetadataTable
     */
    protected $ArtifactMetadata;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected $fixtures = [
        'app.ArtifactMetadata',
        'app.Artifacts',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ArtifactMetadata') ? [] : ['className' => ArtifactMetadataTable::class];
        $this->ArtifactMetadata = $this->getTableLocator()->get('ArtifactMetadata', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ArtifactMetadata);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\ArtifactMetadataTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getJsonFields method
     *
     * @return void
     * @uses \App\Model\Table\ArtifactMetadataTable::getJsonFields()
     */
    public function testGetJsonFields(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @uses \App\Model\Table\ArtifactMetadataTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
