<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class SeedHotFolderWorkerLimit extends AbstractMigration
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
                'name' => 'Hot Folder Worker Limit',
                'description' => 'How many Hot Folder Workers can be booted at the one time',
                'property_group' => 'hot_folder',
                'property_key' => 'hot_folder_worker_limit',
                'property_value' => '1',
                'selections' => '{"0":"0","1":"1"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
    }

}
