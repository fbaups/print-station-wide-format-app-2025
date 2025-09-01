<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SeedHotFolderWorkerSettings extends AbstractMigration
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
                'name' => 'Hot Folder Worker Life Expectancy',
                'description' => 'How long Hot Folder Workers can run for till they are retired (minutes)',
                'property_group' => 'hot_folder',
                'property_key' => 'hot_folder_worker_life_expectancy',
                'property_value' => '6',
                'selections' => '{"6":"6","10":"10","11":"11","12":"12","13":"13","14":"14","15":"15","16":"16","17":"17","18":"18","19":"19","20":"20"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Hot Folder Worker Grace Period',
                'description' => 'Grace period for a long running Hot Folder Worker before forced termination (minutes)',
                'property_group' => 'hot_folder',
                'property_key' => 'hot_folder_worker_grace_period',
                'property_value' => '1',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Hot Folder Retry Limit',
                'description' => 'How many times to retry running an Hot Folder',
                'property_group' => 'hot_folder',
                'property_key' => 'hot_folder_retry_limit',
                'property_value' => '3',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Hot Folder Worker Sleep Timeout',
                'description' => 'How long to sleep for if there are no Hot Folders to run',
                'property_group' => 'hot_folder',
                'property_key' => 'hot_folder_worker_sleep',
                'property_value' => '5',
                'selections' => '{"5":"5","10":"10","15":"15","20":"20","30":"30","60":"60"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ]
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
    }

}
