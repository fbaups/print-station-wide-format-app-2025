<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DataBlobsFixture
 */
class DataBlobsFixture extends TestFixture
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
                'created' => '2025-03-25 03:04:36',
                'modified' => '2025-03-25 03:04:36',
                'activation' => '2025-03-25 03:04:36',
                'expiration' => '2025-03-25 03:04:36',
                'auto_delete' => 1,
                'type' => 'Lorem ipsum dolor sit amet',
                'blob' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'hash_sum' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
