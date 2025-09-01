<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateHotFolderEntriesIndexes extends AbstractMigration
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
            ->addIndex(
                [
                    'contents_hash_sum',
                ],
                [
                    'name' => 'hot_folder_entries_contents_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'hot_folder_entries_created_index',
                ]
            )
            ->addIndex(
                [
                    'hot_folder_id',
                ],
                [
                    'name' => 'hot_folder_entries_hot_folder_id_index',
                ]
            )
            ->addIndex(
                [
                    'last_check_time',
                ],
                [
                    'name' => 'hot_folder_entries_last_check_time_index',
                ]
            )
            ->addIndex(
                [
                    'listing_hash_sum',
                ],
                [
                    'name' => 'hot_folder_entries_listing_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'lock_code',
                ],
                [
                    'name' => 'hot_folder_entries_lock_code_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'hot_folder_entries_modified_index',
                ]
            )
            ->addIndex(
                [
                    'next_check_time',
                ],
                [
                    'name' => 'hot_folder_entries_next_check_time_index',
                ]
            )
            ->addIndex(
                [
                    'path_hash_sum',
                ],
                [
                    'name' => 'hot_folder_entries_path_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'path',
                ],
                [
                    'name' => 'hot_folder_entries_path_index',
                ]
            )
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
            ->removeIndexByName('hot_folder_entries_contents_hash_sum_index')
            ->removeIndexByName('hot_folder_entries_created_index')
            ->removeIndexByName('hot_folder_entries_hot_folder_id_index')
            ->removeIndexByName('hot_folder_entries_last_check_time_index')
            ->removeIndexByName('hot_folder_entries_listing_hash_sum_index')
            ->removeIndexByName('hot_folder_entries_lock_code_index')
            ->removeIndexByName('hot_folder_entries_modified_index')
            ->removeIndexByName('hot_folder_entries_next_check_time_index')
            ->removeIndexByName('hot_folder_entries_path_hash_sum_index')
            ->removeIndexByName('hot_folder_entries_path_index')
            ->update();
    }
}
