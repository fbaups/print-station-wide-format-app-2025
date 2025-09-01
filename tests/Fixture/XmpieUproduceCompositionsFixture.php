<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * XmpieUproduceCompositionsFixture
 */
class XmpieUproduceCompositionsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'guid' => 'Lorem ipsum dolor sit amet',
                'created' => '2025-08-20 23:20:18',
                'modified' => '2025-08-20 23:20:18',
                'activation' => '2025-08-20 23:20:18',
                'expiration' => '2025-08-20 23:20:18',
                'auto_delete' => 1,
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'errand_link' => 1,
                'artifact_link' => 1,
                'integration_credential_link' => 1,
            ],
        ];
        parent::init();
    }
}
