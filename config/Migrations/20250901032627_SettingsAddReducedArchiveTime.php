<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SettingsAddReducedArchiveTime extends AbstractMigration
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
        $archiveTime = [
            "+24 hours" => "24 Hours",
            "+48 hours" => "48 Hours",
            "+3 days" => "3 Days",
            "+7 days" => "7 Days",
            "+14 days" => "14 Days",
            "+1 month" => "1 Month",
            "+3 months" => "3 Months",
            "+6 months" => "6 Months",
            "+12 months" => "12 Months",
            "+18 months" => "18 Months",
            "+24 months" => "24 Months",
            "+36 months" => "36 Months",
            "+48 months" => "48 Months",
            "+60 months" => "60 Months",
            "+600 months" => "Never"
        ];

        $builder = $this->getUpdateBuilder();
        $builder
            ->update('settings')
            ->set('selections', json_encode($archiveTime))
            ->set('property_value', '+48 hours')
            ->where(['property_key' => 'data_purge'])
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
