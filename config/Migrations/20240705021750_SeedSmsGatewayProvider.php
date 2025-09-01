<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class SeedSmsGatewayProvider extends BaseMigration
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

    public function seedSettings()
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'SMS Gateway Provider',
                'description' => 'The company you have a subscription with that provides SMS gateway services.',
                'property_group' => 'sms_gateway',
                'property_key' => 'sms_gateway_provider',
                'property_value' => '\\App\\MessageGateways\\DummySmsGateway',
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => '0',
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
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
