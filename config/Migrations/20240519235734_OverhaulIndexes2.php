<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class OverhaulIndexes2 extends BaseMigration
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

        $this->table('artifacts')
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'artifacts_auto_delete_index',
                ]
            )
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'artifacts_description_index',
                ]
            )
            ->addIndex(
                [
                    'size',
                ],
                [
                    'name' => 'artifacts_size_index',
                ]
            )
            ->update();

        $this->table('documents')
            ->addIndex(
                [
                    'external_document_number',
                ],
                [
                    'name' => 'documents_external_document_number_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'documents_name_index',
                ]
            )
            ->addIndex(
                [
                    'quantity'
                ],
                [
                    'name' => 'documents_quantity_index',
                ]
            )
            ->update();

        $this->table('hot_folders')
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'hot_folders_description_index',
                ]
            )
            ->addIndex(
                [
                    'path',
                ],
                [
                    'name' => 'hot_folders_path_index',
                ]
            )
            ->update();

        $this->table('message_beacons')
            ->addIndex(
                [
                    'beacon_url',
                ],
                [
                    'name' => 'message_beacons_beacon_url_index',
                ]
            )
            ->update();

        $this->table('messages')
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'messages_description_index',
                ]
            )
            ->addIndex(
                [
                    'profile',
                ],
                [
                    'name' => 'messages_profile_index',
                ]
            )
            ->addIndex(
                [
                    'read_receipt',
                ],
                [
                    'name' => 'messages_read_receipt_index',
                ]
            )
            ->addIndex(
                [
                    'transport',
                ],
                [
                    'name' => 'messages_transport_index',
                ]
            )
            ->update();

        $this->table('scheduled_tasks')
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'scheduled_tasks_description_index',
                ]
            )
            ->update();

        $this->table('settings')
            ->addIndex(
                [
                    'html_select_type',
                ],
                [
                    'name' => 'settings_html_select_type_index',
                ]
            )
            ->addIndex(
                [
                    'is_masked',
                ],
                [
                    'name' => 'settings_is_masked_index',
                ]
            )
            ->addIndex(
                [
                    'match_pattern',
                ],
                [
                    'name' => 'settings_match_pattern_index',
                ]
            )
            ->update();

        $this->table('users')
            ->addIndex(
                [
                    'phone',
                ],
                [
                    'name' => 'users_phone_index',
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

        $this->table('artifacts')
            ->removeIndexByName('artifacts_auto_delete_index')
            ->removeIndexByName('artifacts_description_index')
            ->removeIndexByName('artifacts_size_index')
            ->update();

        $this->table('documents')
            ->removeIndexByName('documents_external_document_number_index')
            ->removeIndexByName('documents_name_index')
            ->removeIndexByName('documents_quantity_index')
            ->update();

        $this->table('hot_folders')
            ->removeIndexByName('hot_folders_description_index')
            ->removeIndexByName('hot_folders_path_index')
            ->update();

        $this->table('message_beacons')
            ->removeIndexByName('message_beacons_beacon_url_index')
            ->update();

        $this->table('messages')
            ->removeIndexByName('messages_description_index')
            ->removeIndexByName('messages_profile_index')
            ->removeIndexByName('messages_read_receipt_index')
            ->removeIndexByName('messages_transport_index')
            ->update();

        $this->table('scheduled_tasks')
            ->removeIndexByName('scheduled_tasks_description_index')
            ->update();

        $this->table('settings')
            ->removeIndexByName('settings_html_select_type_index')
            ->removeIndexByName('settings_is_masked_index')
            ->removeIndexByName('settings_match_pattern_index')
            ->update();

        $this->table('users')
            ->removeIndexByName('users_phone_index')
            ->update();
    }
}
