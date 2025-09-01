<?php

use App\Migrations\AppBaseMigration as BaseMigration;

class SeedSettingsRemoteUpdate extends BaseMigration
{

    public function up()
    {
        $this->seedSettings();
    }

    public function down()
    {
    }

    public function seedSettings()
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Remote Update Base URL',
                'description' => 'The base URL of the remote update files',
                'property_group' => 'remote_update',
                'property_key' => 'remote_update_url',
                'property_value' => 'http://localhost/update/', //will be updated at Build time with contents of config/remote_update.json
                'selections' => '',
                'html_select_type' => 'text',
                'match_pattern' => null,
                'is_masked' => false
            ]
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
    }

}

