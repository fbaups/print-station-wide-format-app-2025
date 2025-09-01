<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class SeedSmsGatewayCredentials extends BaseMigration
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
        $this->seedSettings();
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

    public function seedSettings()
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'SMS Gateway Username',
                'description' => 'Username for your SMS Gateway. Leave blank if the SMS Gateway API does not use this.',
                'property_group' => 'sms_gateway',
                'property_key' => 'sms_gateway_username',
                'property_value' => '',
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => '0',
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'SMS Gateway Password',
                'description' => 'Password for your SMS Gateway. Leave blank if the SMS Gateway API does not use this.',
                'property_group' => 'sms_gateway',
                'property_key' => 'sms_gateway_password',
                'property_value' => '',
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => '1',
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'SMS Gateway API ID',
                'description' => 'API ID. Leave blank if the SMS Gateway API does not use this.',
                'property_group' => 'sms_gateway',
                'property_key' => 'sms_gateway_api_id',
                'property_value' => '',
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => '1',
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'SMS Gateway API KEY',
                'description' => 'API KEY. Leave blank if the SMS Gateway API does not use this.',
                'property_group' => 'sms_gateway',
                'property_key' => 'sms_gateway_api_key',
                'property_value' => '',
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => '1',
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
    }

}
