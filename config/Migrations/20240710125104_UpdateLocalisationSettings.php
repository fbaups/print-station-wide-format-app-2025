<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class UpdateLocalisationSettings extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $dateFormats = [
            'International' => [
                'j F Y' => '9 July 2024',
                'jS \o\f F Y' => '1st of December 2024',
            ],
            'Australian' => [
                'd/m/Y' => '19/07/2024 (day/month/year)',
                'd/m/y' => '19/07/24 (day/month/year)',
            ],
            'United States' => [
                'm/d/Y' => '09/27/2024 (month/day/year)',
                'm/d/y' => '09/27/24 (month/day/year)',
            ],
        ];

        $timeFormats = [
            '24 Hour' => [
                'H:i:s' => '13:30:45',
                'H:i' => '13:30',
            ],
            '12 Hour' => [
                'g:i a' => '1:30 pm',
                'h:i a' => '01:30 pm',
            ],
        ];

        $datetimeFormats = [
            'International' => [
                'Y-m-d H:i:s' => '2024-07-09 13:30:00',
                'j F Y, g:i a' => '9 July 2024, 1:30 pm',
            ],
            'Australian' => [
                'd/m/Y h:i a' => '19/07/2024 01:30 pm (day/month/year)',
                'd/m/y g:i a' => '19/07/24 1:30 pm (day/month/year)',
            ],
            'United States' => [
                'm/d/Y h:i a' => '09/27/2024 01:30 pm (month/day/year)',
                'm/d/y g:i a' => '09/27/24 1:30 pm (month/day/year)',
            ],
        ];

        $builder = $this->getUpdateBuilder();
        $builder
            ->update('settings')
            ->set('selections', json_encode($dateFormats))
            ->set('property_value', 'd/m/y')
            ->where(['property_key' => 'date_format'])
            ->execute();

        $builder = $this->getUpdateBuilder();
        $builder
            ->update('settings')
            ->set('selections', json_encode($timeFormats))
            ->set('property_value', 'h:i a')
            ->where(['property_key' => 'time_format'])
            ->execute();

        $builder = $this->getUpdateBuilder();
        $builder
            ->update('settings')
            ->set('selections', json_encode($datetimeFormats))
            ->set('property_value', 'Y-m-d H:i:s')
            ->where(['property_key' => 'datetime_format'])
            ->execute();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
    }
}
