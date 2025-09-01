<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArtifactsFixture
 */
class ArtifactsFixture extends TestFixture
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
                'created' => '2022-11-28 09:57:43',
                'modified' => '2022-11-28 09:57:43',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'size' => 1,
                'mime_type' => 'Lorem ipsum dolor sit amet',
                'activation' => '2022-11-28 09:57:43',
                'expiration' => '2022-11-28 09:57:43',
                'auto_delete' => 1,
                'token' => 'Lorem ipsum dolor sit amet',
                'url' => 'Lorem ipsum dolor sit amet',
                'unc' => 'Lorem ipsum dolor sit amet',
                'hash_sum' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
