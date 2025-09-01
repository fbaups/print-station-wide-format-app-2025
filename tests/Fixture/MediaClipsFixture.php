<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MediaClipsFixture
 */
class MediaClipsFixture extends TestFixture
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
                'created' => '2025-05-10 07:14:25',
                'modified' => '2025-05-10 07:14:25',
                'name' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet',
                'rank' => 1,
                'artifact_link' => 1,
                'activation' => '2025-05-10 07:14:25',
                'expiration' => '2025-05-10 07:14:25',
                'auto_delete' => 1,
                'trim_start' => 1,
                'trim_end' => 1,
                'duration' => 1,
                'fitting' => 'Lorem ipsum do',
                'muted' => 1,
                'loop' => 1,
                'autoplay' => 1,
            ],
        ];
        parent::init();
    }
}
