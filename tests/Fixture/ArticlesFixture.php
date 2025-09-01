<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ArticlesFixture
 */
class ArticlesFixture extends TestFixture
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
                'article_status_id' => 1,
                'user_link' => 1,
                'created' => '2024-12-02 03:30:26',
                'modified' => '2024-12-02 03:30:26',
                'activation' => '2024-12-02 03:30:26',
                'expiration' => '2024-12-02 03:30:26',
                'auto_delete' => 1,
                'title' => 'Lorem ipsum dolor sit amet',
                'body' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'priority' => 1,
            ],
        ];
        parent::init();
    }
}
