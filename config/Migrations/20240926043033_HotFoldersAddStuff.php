<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class HotFoldersAddStuff extends AbstractMigration
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

        $this->table('hot_folder_entries')
            ->addColumn('status', 'string', [
                'after' => 'errand_link',
                'default' => null,
                'length' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'errand_link',
                ],
                [
                    'name' => 'hot_folder_entries_errand_link_index',
                ]
            )
            ->addIndex(
                [
                    'status',
                ],
                [
                    'name' => 'hot_folder_entries_status_index',
                ]
            )
            ->update();

        $this->table('hot_folders')
            ->addColumn('auto_clear_entries', 'boolean', [
                'after' => 'parameters',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->update();
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

        $this->table('hot_folder_entries')
            ->removeIndexByName('hot_folder_entries_errand_link_index')
            ->removeIndexByName('hot_folder_entries_status_index')
            ->update();

        $this->table('hot_folder_entries')
            ->removeColumn('status')
            ->update();

        $this->table('hot_folders')
            ->removeColumn('auto_clear_entries')
            ->update();
    }
}
