<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UserLocalizationsFixture
 */
class UserLocalizationsFixture extends TestFixture
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
                'user_id' => 1,
                'created' => '2022-12-05 06:33:17',
                'modified' => '2022-12-05 06:33:17',
                'location' => 'Lorem ipsum dolor sit amet',
                'locale' => 'Lorem ip',
                'timezone' => 'Lorem ipsum dolor sit amet',
                'time_format' => 'Lorem ipsum dolor sit amet',
                'date_format' => 'Lorem ipsum dolor sit amet',
                'datetime_format' => 'Lorem ipsum dolor sit amet',
                'week_start' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
