<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class OverhaulIndexes1 extends \App\Migrations\AppAbstractMigration
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

        $this->table('document_alerts')
            ->addIndex(
                [
                    'code',
                ],
                [
                    'name' => 'document_alerts_code_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'document_alerts_created_index',
                ]
            )
            ->addIndex(
                [
                    'document_id',
                ],
                [
                    'name' => 'document_alerts_document_id_index',
                ]
            )
            ->addIndex(
                [
                    'level',
                ],
                [
                    'name' => 'document_alerts_level_index',
                ]
            )
            ->update();

        $this->table('document_properties')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'document_properties_created_index',
                ]
            )
            ->addIndex(
                [
                    'document_id',
                ],
                [
                    'name' => 'document_properties_document_id_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'document_properties_modified_index',
                ]
            )
            ->update();

        $this->table('document_status_movements')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'document_status_movements_created_index',
                ]
            )
            ->addIndex(
                [
                    'document_id',
                ],
                [
                    'name' => 'document_status_movements_document_id_index',
                ]
            )
            ->addIndex(
                [
                    'document_status_from',
                ],
                [
                    'name' => 'document_status_movements_document_status_from_index',
                ]
            )
            ->addIndex(
                [
                    'document_status_to',
                ],
                [
                    'name' => 'document_status_movements_document_status_to_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'document_status_movements_user_id_index',
                ]
            )
            ->update();

        $this->table('document_statuses')
            ->addIndex(
                [
                    'allow_from_status',
                ],
                [
                    'name' => 'document_statuses_allow_from_status_index',
                ]
            )
            ->addIndex(
                [
                    'allow_to_status',
                ],
                [
                    'name' => 'document_statuses_allow_to_status_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'document_statuses_created_index',
                ]
            )
            ->addIndex(
                [
                    'hex_code',
                ],
                [
                    'name' => 'document_statuses_hex_code_index',
                ]
            )
            ->addIndex(
                [
                    'icon',
                ],
                [
                    'name' => 'document_statuses_icon_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'document_statuses_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'document_statuses_name_index',
                ]
            )
            ->addIndex(
                [
                    'sort',
                ],
                [
                    'name' => 'document_statuses_sort_index',
                ]
            )
            ->update();

        $this->table('documents')
            ->addIndex(
                [
                    'artifact_token',
                ],
                [
                    'name' => 'documents_artifact_token_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'documents_created_index',
                ]
            )
            ->addIndex(
                [
                    'document_status_id',
                ],
                [
                    'name' => 'documents_document_status_id_index',
                ]
            )
            ->addIndex(
                [
                    'external_creation_date',
                ],
                [
                    'name' => 'documents_external_creation_date_index',
                ]
            )
            ->addIndex(
                [
                    'guid',
                ],
                [
                    'name' => 'documents_guid_index',
                ]
            )
            ->addIndex(
                [
                    'hash_sum',
                ],
                [
                    'name' => 'documents_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'job_id',
                ],
                [
                    'name' => 'documents_job_id_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'documents_modified_index',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ],
                [
                    'name' => 'documents_priority_index',
                ]
            )
            ->update();

        $this->table('documents_users')
            ->addIndex(
                [
                    'document_id',
                ],
                [
                    'name' => 'documents_users_document_id_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'documents_users_user_id_index',
                ]
            )
            ->update();

        $this->table('errands')
            ->addIndex(
                [
                    'background_service_link',
                ],
                [
                    'name' => 'errands_background_service_link_index',
                ]
            )
            ->addIndex(
                [
                    'background_service_name',
                ],
                [
                    'name' => 'errands_background_service_name_index',
                ]
            )
            ->addIndex(
                [
                    'errors_retry',
                ],
                [
                    'name' => 'errands_errors_retry_index',
                ]
            )
            ->addIndex(
                [
                    'errors_retry_limit',
                ],
                [
                    'name' => 'errands_errors_retry_limit_index',
                ]
            )
            ->addIndex(
                [
                    'lock_to_thread',
                ],
                [
                    'name' => 'errands_lock_to_thread_index',
                ]
            )
            ->addIndex(
                [
                    'progress_bar',
                ],
                [
                    'name' => 'errands_progress_bar_index',
                ]
            )
            ->addIndex(
                [
                    'return_value',
                ],
                [
                    'name' => 'errands_return_value_index',
                ]
            )
            ->update();

        $this->table('error_levels')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'error_levels_created_index',
                ]
            )
            ->addIndex(
                [
                    'css_alert',
                ],
                [
                    'name' => 'error_levels_css_alert_index',
                ]
            )
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'error_levels_description_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'error_levels_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'error_levels_name_index',
                ]
            )
            ->addIndex(
                [
                    'value',
                ],
                [
                    'name' => 'error_levels_value_index',
                ]
            )
            ->update();

        $this->table('hot_folders')
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'hot_folders_activation_index',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'hot_folders_auto_delete_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'hot_folders_created_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'hot_folders_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'is_enabled',
                ],
                [
                    'name' => 'hot_folders_is_enabled_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'hot_folders_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'hot_folders_name_index',
                ]
            )
            ->addIndex(
                [
                    'next_polling_time',
                ],
                [
                    'name' => 'hot_folders_next_polling_time_index',
                ]
            )
            ->addIndex(
                [
                    'polling_interval',
                ],
                [
                    'name' => 'hot_folders_polling_interval_index',
                ]
            )
            ->addIndex(
                [
                    'stable_interval',
                ],
                [
                    'name' => 'hot_folders_stable_interval_index',
                ]
            )
            ->addIndex(
                [
                    'submit_url_enabled',
                ],
                [
                    'name' => 'hot_folders_submit_url_enabled_index',
                ]
            )
            ->addIndex(
                [
                    'submit_url',
                ],
                [
                    'name' => 'hot_folders_submit_url_index',
                ]
            )
            ->addIndex(
                [
                    'workflow',
                ],
                [
                    'name' => 'hot_folders_workflow_index',
                ]
            )
            ->update();

        $this->table('job_alerts')
            ->addIndex(
                [
                    'code',
                ],
                [
                    'name' => 'job_alerts_code_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'job_alerts_created_index',
                ]
            )
            ->addIndex(
                [
                    'job_id',
                ],
                [
                    'name' => 'job_alerts_job_id_index',
                ]
            )
            ->addIndex(
                [
                    'level',
                ],
                [
                    'name' => 'job_alerts_level_index',
                ]
            )
            ->update();

        $this->table('job_properties')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'job_properties_created_index',
                ]
            )
            ->addIndex(
                [
                    'job_id',
                ],
                [
                    'name' => 'job_properties_job_id_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'job_properties_modified_index',
                ]
            )
            ->update();

        $this->table('job_status_movements')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'job_status_movements_created_index',
                ]
            )
            ->addIndex(
                [
                    'job_id',
                ],
                [
                    'name' => 'job_status_movements_job_id_index',
                ]
            )
            ->addIndex(
                [
                    'job_status_from',
                ],
                [
                    'name' => 'job_status_movements_job_status_from_index',
                ]
            )
            ->addIndex(
                [
                    'job_status_to',
                ],
                [
                    'name' => 'job_status_movements_job_status_to_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'job_status_movements_user_id_index',
                ]
            )
            ->update();

        $this->table('job_statuses')
            ->addIndex(
                [
                    'allow_from_status',
                ],
                [
                    'name' => 'job_statuses_allow_from_status_index',
                ]
            )
            ->addIndex(
                [
                    'allow_to_status',
                ],
                [
                    'name' => 'job_statuses_allow_to_status_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'job_statuses_created_index',
                ]
            )
            ->addIndex(
                [
                    'hex_code',
                ],
                [
                    'name' => 'job_statuses_hex_code_index',
                ]
            )
            ->addIndex(
                [
                    'icon',
                ],
                [
                    'name' => 'job_statuses_icon_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'job_statuses_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'job_statuses_name_index',
                ]
            )
            ->addIndex(
                [
                    'sort',
                ],
                [
                    'name' => 'job_statuses_sort_index',
                ]
            )
            ->update();

        $this->table('jobs')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'jobs_created_index',
                ]
            )
            ->addIndex(
                [
                    'external_creation_date',
                ],
                [
                    'name' => 'jobs_external_creation_date_index',
                ]
            )
            ->addIndex(
                [
                    'external_job_number',
                ],
                [
                    'name' => 'jobs_external_job_number_index',
                ]
            )
            ->addIndex(
                [
                    'guid',
                ],
                [
                    'name' => 'jobs_guid_index',
                ]
            )
            ->addIndex(
                [
                    'hash_sum',
                ],
                [
                    'name' => 'jobs_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'job_status_id',
                ],
                [
                    'name' => 'jobs_job_status_id_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'jobs_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'jobs_name_index',
                ]
            )
            ->addIndex(
                [
                    'order_id',
                ],
                [
                    'name' => 'jobs_order_id_index',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ],
                [
                    'name' => 'jobs_priority_index',
                ]
            )
            ->addIndex(
                [
                    'quantity',
                ],
                [
                    'name' => 'jobs_quantity_index',
                ]
            )
            ->update();

        $this->table('jobs_users')
            ->addIndex(
                [
                    'job_id',
                ],
                [
                    'name' => 'jobs_users_job_id_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'jobs_users_user_id_index',
                ]
            )
            ->update();

        $this->table('message_connections')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'message_connections_created_index',
                ]
            )
            ->addIndex(
                [
                    'direction',
                ],
                [
                    'name' => 'message_connections_direction_index',
                ]
            )
            ->addIndex(
                [
                    'message_link',
                ],
                [
                    'name' => 'message_connections_message_link_index',
                ]
            )
            ->addIndex(
                [
                    'user_link',
                ],
                [
                    'name' => 'message_connections_user_link_index',
                ]
            )
            ->update();

        $this->table('messages')
            ->addIndex(
                [
                    'email_bcc',
                ],
                [
                    'name' => 'messages_email_bcc_index',
                ]
            )
            ->addIndex(
                [
                    'email_cc',
                ],
                [
                    'name' => 'messages_email_cc_index',
                ]
            )
            ->addIndex(
                [
                    'email_from',
                ],
                [
                    'name' => 'messages_email_from_index',
                ]
            )
            ->addIndex(
                [
                    'errors_retry',
                ],
                [
                    'name' => 'messages_errors_retry_index',
                ]
            )
            ->addIndex(
                [
                    'errors_retry_limit',
                ],
                [
                    'name' => 'messages_errors_retry_limit_index',
                ]
            )
            ->addIndex(
                [
                    'reply_to',
                ],
                [
                    'name' => 'messages_reply_to_index',
                ]
            )
            ->addIndex(
                [
                    'smtp_code',
                ],
                [
                    'name' => 'messages_smtp_code_index',
                ]
            )
            ->update();

        $this->table('order_alerts')
            ->addIndex(
                [
                    'code',
                ],
                [
                    'name' => 'order_alerts_code_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'order_alerts_created_index',
                ]
            )
            ->addIndex(
                [
                    'level',
                ],
                [
                    'name' => 'order_alerts_level_index',
                ]
            )
            ->addIndex(
                [
                    'order_id',
                ],
                [
                    'name' => 'order_alerts_order_id_index',
                ]
            )
            ->update();

        $this->table('order_properties')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'order_properties_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'order_properties_modified_index',
                ]
            )
            ->addIndex(
                [
                    'order_id',
                ],
                [
                    'name' => 'order_properties_order_id_index',
                ]
            )
            ->update();

        $this->table('order_status_movements')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'order_status_movements_created_index',
                ]
            )
            ->addIndex(
                [
                    'order_id',
                ],
                [
                    'name' => 'order_status_movements_order_id_index',
                ]
            )
            ->addIndex(
                [
                    'order_status_from',
                ],
                [
                    'name' => 'order_status_movements_order_status_from_index',
                ]
            )
            ->addIndex(
                [
                    'order_status_to',
                ],
                [
                    'name' => 'order_status_movements_order_status_to_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'order_status_movements_user_id_index',
                ]
            )
            ->update();

        $this->table('order_statuses')
            ->addIndex(
                [
                    'allow_from_status',
                ],
                [
                    'name' => 'order_statuses_allow_from_status_index',
                ]
            )
            ->addIndex(
                [
                    'allow_to_status',
                ],
                [
                    'name' => 'order_statuses_allow_to_status_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'order_statuses_created_index',
                ]
            )
            ->addIndex(
                [
                    'hex_code',
                ],
                [
                    'name' => 'order_statuses_hex_code_index',
                ]
            )
            ->addIndex(
                [
                    'icon',
                ],
                [
                    'name' => 'order_statuses_icon_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'order_statuses_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'order_statuses_name_index',
                ]
            )
            ->addIndex(
                [
                    'sort',
                ],
                [
                    'name' => 'order_statuses_sort_index',
                ]
            )
            ->update();

        $this->table('orders')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'orders_created_index',
                ]
            )
            ->addIndex(
                [
                    'external_creation_date',
                ],
                [
                    'name' => 'orders_external_creation_date_index',
                ]
            )
            ->addIndex(
                [
                    'external_order_number',
                ],
                [
                    'name' => 'orders_external_order_number_index',
                ]
            )
            ->addIndex(
                [
                    'guid',
                ],
                [
                    'name' => 'orders_guid_index',
                ]
            )
            ->addIndex(
                [
                    'hash_sum',
                ],
                [
                    'name' => 'orders_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'orders_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'orders_name_index',
                ]
            )
            ->addIndex(
                [
                    'quantity'
                ],
                [
                    'name' => 'orders_quantity_index',
                ]
            )
            ->addIndex(
                [
                    'external_system_type',
                ],
                [
                    'name' => 'orders_external_system_type_index',
                ]
            )
            ->addIndex(
                [
                    'order_status_id',
                ],
                [
                    'name' => 'orders_order_status_id_index',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ],
                [
                    'name' => 'orders_priority_index',
                ]
            )
            ->update();

        $this->table('orders_users')
            ->addIndex(
                [
                    'order_id',
                ],
                [
                    'name' => 'orders_users_order_id_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'orders_users_user_id_index',
                ]
            )
            ->update();

        $this->table('roles')
            ->addIndex(
                [
                    'alias',
                ],
                [
                    'name' => 'roles_alias_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'roles_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'roles_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'roles_name_index',
                ]
            )
            ->addIndex(
                [
                    'session_timeout',
                ],
                [
                    'name' => 'roles_session_timeout_index',
                ]
            )
            ->update();

        $this->table('roles_users')
            ->addIndex(
                [
                    'role_id',
                ],
                [
                    'name' => 'roles_users_role_id_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'roles_users_user_id_index',
                ]
            )
            ->update();

        $this->table('scheduled_tasks')
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'scheduled_tasks_activation_index',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'scheduled_tasks_auto_delete_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'scheduled_tasks_created_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'scheduled_tasks_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'is_enabled',
                ],
                [
                    'name' => 'scheduled_tasks_is_enabled_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'scheduled_tasks_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'scheduled_tasks_name_index',
                ]
            )
            ->addIndex(
                [
                    'next_run_time',
                ],
                [
                    'name' => 'scheduled_tasks_next_run_time_index',
                ]
            )
            ->addIndex(
                [
                    'schedule',
                ],
                [
                    'name' => 'scheduled_tasks_schedule_index',
                ]
            )
            ->addIndex(
                [
                    'workflow',
                ],
                [
                    'name' => 'scheduled_tasks_workflow_index',
                ]
            )
            ->update();

        $this->table('seeds')
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'seeds_activation_index',
                ]
            )
            ->addIndex(
                [
                    'bid_limit',
                ],
                [
                    'name' => 'seeds_bid_limit_index',
                ]
            )
            ->addIndex(
                [
                    'bids',
                ],
                [
                    'name' => 'seeds_bids_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'seeds_created_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'seeds_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'seeds_modified_index',
                ]
            )
            ->addIndex(
                [
                    'token',
                ],
                [
                    'name' => 'seeds_token_index',
                ]
            )
            ->addIndex(
                [
                    'user_link',
                ],
                [
                    'name' => 'seeds_user_link_index',
                ]
            )
            ->update();

        $this->table('settings')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'settings_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'settings_modified_index',
                ]
            )
            ->update();

        $this->table('user_localizations')
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'user_localizations_created_index',
                ]
            )
            ->addIndex(
                [
                    'date_format',
                ],
                [
                    'name' => 'user_localizations_date_format_index',
                ]
            )
            ->addIndex(
                [
                    'datetime_format',
                ],
                [
                    'name' => 'user_localizations_datetime_format_index',
                ]
            )
            ->addIndex(
                [
                    'locale',
                ],
                [
                    'name' => 'user_localizations_locale_index',
                ]
            )
            ->addIndex(
                [
                    'location',
                ],
                [
                    'name' => 'user_localizations_location_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'user_localizations_modified_index',
                ]
            )
            ->addIndex(
                [
                    'time_format',
                ],
                [
                    'name' => 'user_localizations_time_format_index',
                ]
            )
            ->addIndex(
                [
                    'timezone',
                ],
                [
                    'name' => 'user_localizations_timezone_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'user_localizations_user_id_index',
                ]
            )
            ->addIndex(
                [
                    'week_start',
                ],
                [
                    'name' => 'user_localizations_week_start_index',
                ]
            )
            ->update();

        $this->table('user_statuses')
            ->addIndex(
                [
                    'alias',
                ],
                [
                    'name' => 'user_statuses_alias_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'user_statuses_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'user_statuses_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'user_statuses_name_index',
                ]
            )
            ->addIndex(
                [
                    'name_status_icon',
                ],
                [
                    'name' => 'user_statuses_name_status_icon_index',
                ]
            )
            ->addIndex(
                [
                    'rank',
                ],
                [
                    'name' => 'user_statuses_rank_index',
                ]
            )
            ->update();

        $this->table('users')
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'users_activation_index',
                ]
            )
            ->addIndex(
                [
                    'country',
                ],
                [
                    'name' => 'users_country_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'users_created_index',
                ]
            )
            ->addIndex(
                [
                    'email',
                ],
                [
                    'name' => 'users_email_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'users_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'first_name',
                ],
                [
                    'name' => 'users_first_name_index',
                ]
            )
            ->addIndex(
                [
                    'is_confirmed',
                ],
                [
                    'name' => 'users_is_confirmed_index',
                ]
            )
            ->addIndex(
                [
                    'last_name',
                ],
                [
                    'name' => 'users_last_name_index',
                ]
            )
            ->addIndex(
                [
                    'mobile',
                ],
                [
                    'name' => 'users_mobile_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'users_modified_index',
                ]
            )
            ->addIndex(
                [
                    'password_expiry',
                ],
                [
                    'name' => 'users_password_expiry_index',
                ]
            )
            ->addIndex(
                [
                    'post_code',
                ],
                [
                    'name' => 'users_post_code_index',
                ]
            )
            ->addIndex(
                [
                    'state',
                ],
                [
                    'name' => 'users_state_index',
                ]
            )
            ->addIndex(
                [
                    'user_statuses_id',
                ],
                [
                    'name' => 'users_user_statuses_id_index',
                ]
            )
            ->addIndex(
                [
                    'username',
                ],
                [
                    'name' => 'users_username_index',
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

        $this->table('document_alerts')
            ->removeIndexByName('document_alerts_code_index')
            ->removeIndexByName('document_alerts_created_index')
            ->removeIndexByName('document_alerts_document_id_index')
            ->removeIndexByName('document_alerts_level_index')
            ->update();

        $this->table('document_properties')
            ->removeIndexByName('document_properties_created_index')
            ->removeIndexByName('document_properties_document_id_index')
            ->removeIndexByName('document_properties_modified_index')
            ->update();

        $this->table('document_status_movements')
            ->removeIndexByName('document_status_movements_created_index')
            ->removeIndexByName('document_status_movements_document_id_index')
            ->removeIndexByName('document_status_movements_document_status_from_index')
            ->removeIndexByName('document_status_movements_document_status_to_index')
            ->removeIndexByName('document_status_movements_user_id_index')
            ->update();

        $this->table('document_statuses')
            ->removeIndexByName('document_statuses_allow_from_status_index')
            ->removeIndexByName('document_statuses_allow_to_status_index')
            ->removeIndexByName('document_statuses_created_index')
            ->removeIndexByName('document_statuses_hex_code_index')
            ->removeIndexByName('document_statuses_icon_index')
            ->removeIndexByName('document_statuses_modified_index')
            ->removeIndexByName('document_statuses_name_index')
            ->removeIndexByName('document_statuses_sort_index')
            ->update();

        $this->table('documents')
            ->removeIndexByName('documents_artifact_token_index')
            ->removeIndexByName('documents_created_index')
            ->removeIndexByName('documents_document_status_id_index')
            ->removeIndexByName('documents_external_creation_date_index')
            ->removeIndexByName('documents_guid_index')
            ->removeIndexByName('documents_hash_sum_index')
            ->removeIndexByName('documents_job_id_index')
            ->removeIndexByName('documents_modified_index')
            ->removeIndexByName('documents_priority_index')
            ->update();

        $this->table('documents_users')
            ->removeIndexByName('documents_users_document_id_index')
            ->removeIndexByName('documents_users_user_id_index')
            ->update();

        $this->table('errands')
            ->removeIndexByName('errands_background_service_link_index')
            ->removeIndexByName('errands_background_service_name_index')
            ->removeIndexByName('errands_errors_retry_index')
            ->removeIndexByName('errands_errors_retry_limit_index')
            ->removeIndexByName('errands_lock_to_thread_index')
            ->removeIndexByName('errands_progress_bar_index')
            ->removeIndexByName('errands_return_value_index')
            ->update();

        $this->table('error_levels')
            ->removeIndexByName('error_levels_created_index')
            ->removeIndexByName('error_levels_css_alert_index')
            ->removeIndexByName('error_levels_description_index')
            ->removeIndexByName('error_levels_modified_index')
            ->removeIndexByName('error_levels_name_index')
            ->removeIndexByName('error_levels_value_index')
            ->update();

        $this->table('hot_folders')
            ->removeIndexByName('hot_folders_activation_index')
            ->removeIndexByName('hot_folders_auto_delete_index')
            ->removeIndexByName('hot_folders_created_index')
            ->removeIndexByName('hot_folders_expiration_index')
            ->removeIndexByName('hot_folders_is_enabled_index')
            ->removeIndexByName('hot_folders_modified_index')
            ->removeIndexByName('hot_folders_name_index')
            ->removeIndexByName('hot_folders_next_polling_time_index')
            ->removeIndexByName('hot_folders_polling_interval_index')
            ->removeIndexByName('hot_folders_stable_interval_index')
            ->removeIndexByName('hot_folders_submit_url_enabled_index')
            ->removeIndexByName('hot_folders_submit_url_index')
            ->removeIndexByName('hot_folders_workflow_index')
            ->update();

        $this->table('job_alerts')
            ->removeIndexByName('job_alerts_code_index')
            ->removeIndexByName('job_alerts_created_index')
            ->removeIndexByName('job_alerts_job_id_index')
            ->removeIndexByName('job_alerts_level_index')
            ->update();

        $this->table('job_properties')
            ->removeIndexByName('job_properties_created_index')
            ->removeIndexByName('job_properties_job_id_index')
            ->removeIndexByName('job_properties_modified_index')
            ->update();

        $this->table('job_status_movements')
            ->removeIndexByName('job_status_movements_created_index')
            ->removeIndexByName('job_status_movements_job_id_index')
            ->removeIndexByName('job_status_movements_job_status_from_index')
            ->removeIndexByName('job_status_movements_job_status_to_index')
            ->removeIndexByName('job_status_movements_user_id_index')
            ->update();

        $this->table('job_statuses')
            ->removeIndexByName('job_statuses_allow_from_status_index')
            ->removeIndexByName('job_statuses_allow_to_status_index')
            ->removeIndexByName('job_statuses_created_index')
            ->removeIndexByName('job_statuses_hex_code_index')
            ->removeIndexByName('job_statuses_icon_index')
            ->removeIndexByName('job_statuses_modified_index')
            ->removeIndexByName('job_statuses_name_index')
            ->removeIndexByName('job_statuses_sort_index')
            ->update();

        $this->table('jobs')
            ->removeIndexByName('jobs_created_index')
            ->removeIndexByName('jobs_external_creation_date_index')
            ->removeIndexByName('jobs_external_job_number_index')
            ->removeIndexByName('jobs_guid_index')
            ->removeIndexByName('jobs_hash_sum_index')
            ->removeIndexByName('jobs_job_status_id_index')
            ->removeIndexByName('jobs_modified_index')
            ->removeIndexByName('jobs_name_index')
            ->removeIndexByName('jobs_order_id_index')
            ->removeIndexByName('jobs_priority_index')
            ->removeIndexByName('jobs_quantity_index')
            ->update();

        $this->table('jobs_users')
            ->removeIndexByName('jobs_users_job_id_index')
            ->removeIndexByName('jobs_users_user_id_index')
            ->update();

        $this->table('message_connections')
            ->removeIndexByName('message_connections_created_index')
            ->removeIndexByName('message_connections_direction_index')
            ->removeIndexByName('message_connections_message_link_index')
            ->removeIndexByName('message_connections_user_link_index')
            ->update();

        $this->table('messages')
            ->removeIndexByName('messages_email_bcc_index')
            ->removeIndexByName('messages_email_cc_index')
            ->removeIndexByName('messages_email_from_index')
            ->removeIndexByName('messages_errors_retry_index')
            ->removeIndexByName('messages_errors_retry_limit_index')
            ->removeIndexByName('messages_reply_to_index')
            ->removeIndexByName('messages_smtp_code_index')
            ->update();

        $this->table('order_alerts')
            ->removeIndexByName('order_alerts_code_index')
            ->removeIndexByName('order_alerts_created_index')
            ->removeIndexByName('order_alerts_level_index')
            ->removeIndexByName('order_alerts_order_id_index')
            ->update();

        $this->table('order_properties')
            ->removeIndexByName('order_properties_created_index')
            ->removeIndexByName('order_properties_modified_index')
            ->removeIndexByName('order_properties_order_id_index')
            ->update();

        $this->table('order_status_movements')
            ->removeIndexByName('order_status_movements_created_index')
            ->removeIndexByName('order_status_movements_order_id_index')
            ->removeIndexByName('order_status_movements_order_status_from_index')
            ->removeIndexByName('order_status_movements_order_status_to_index')
            ->removeIndexByName('order_status_movements_user_id_index')
            ->update();

        $this->table('order_statuses')
            ->removeIndexByName('order_statuses_allow_from_status_index')
            ->removeIndexByName('order_statuses_allow_to_status_index')
            ->removeIndexByName('order_statuses_created_index')
            ->removeIndexByName('order_statuses_hex_code_index')
            ->removeIndexByName('order_statuses_icon_index')
            ->removeIndexByName('order_statuses_modified_index')
            ->removeIndexByName('order_statuses_name_index')
            ->removeIndexByName('order_statuses_sort_index')
            ->update();

        $this->table('orders')
            ->removeIndexByName('orders_created_index')
            ->removeIndexByName('orders_external_creation_date_index')
            ->removeIndexByName('orders_external_order_number_index')
            ->removeIndexByName('orders_guid_index')
            ->removeIndexByName('orders_hash_sum_index')
            ->removeIndexByName('orders_modified_index')
            ->removeIndexByName('orders_name_index')
            ->removeIndexByName('orders_order_status_id_index')
            ->removeIndexByName('orders_quantity_index')
            ->removeIndexByName('orders_external_system_type_index')
            ->removeIndexByName('orders_priority_index')
            ->update();

        $this->table('orders_users')
            ->removeIndexByName('orders_users_order_id_index')
            ->removeIndexByName('orders_users_user_id_index')
            ->update();

        $this->table('roles')
            ->removeIndexByName('roles_alias_index')
            ->removeIndexByName('roles_created_index')
            ->removeIndexByName('roles_modified_index')
            ->removeIndexByName('roles_name_index')
            ->removeIndexByName('roles_session_timeout_index')
            ->update();

        $this->table('roles_users')
            ->removeIndexByName('roles_users_role_id_index')
            ->removeIndexByName('roles_users_user_id_index')
            ->update();

        $this->table('scheduled_tasks')
            ->removeIndexByName('scheduled_tasks_activation_index')
            ->removeIndexByName('scheduled_tasks_auto_delete_index')
            ->removeIndexByName('scheduled_tasks_created_index')
            ->removeIndexByName('scheduled_tasks_expiration_index')
            ->removeIndexByName('scheduled_tasks_is_enabled_index')
            ->removeIndexByName('scheduled_tasks_modified_index')
            ->removeIndexByName('scheduled_tasks_name_index')
            ->removeIndexByName('scheduled_tasks_next_run_time_index')
            ->removeIndexByName('scheduled_tasks_schedule_index')
            ->removeIndexByName('scheduled_tasks_workflow_index')
            ->update();

        $this->table('seeds')
            ->removeIndexByName('seeds_activation_index')
            ->removeIndexByName('seeds_bid_limit_index')
            ->removeIndexByName('seeds_bids_index')
            ->removeIndexByName('seeds_created_index')
            ->removeIndexByName('seeds_expiration_index')
            ->removeIndexByName('seeds_modified_index')
            ->removeIndexByName('seeds_token_index')
            ->removeIndexByName('seeds_user_link_index')
            ->update();

        $this->table('settings')
            ->removeIndexByName('settings_created_index')
            ->removeIndexByName('settings_modified_index')
            ->update();

        $this->table('user_localizations')
            ->removeIndexByName('user_localizations_created_index')
            ->removeIndexByName('user_localizations_date_format_index')
            ->removeIndexByName('user_localizations_datetime_format_index')
            ->removeIndexByName('user_localizations_locale_index')
            ->removeIndexByName('user_localizations_location_index')
            ->removeIndexByName('user_localizations_modified_index')
            ->removeIndexByName('user_localizations_time_format_index')
            ->removeIndexByName('user_localizations_timezone_index')
            ->removeIndexByName('user_localizations_user_id_index')
            ->removeIndexByName('user_localizations_week_start_index')
            ->update();

        $this->table('user_statuses')
            ->removeIndexByName('user_statuses_alias_index')
            ->removeIndexByName('user_statuses_created_index')
            ->removeIndexByName('user_statuses_modified_index')
            ->removeIndexByName('user_statuses_name_index')
            ->removeIndexByName('user_statuses_name_status_icon_index')
            ->removeIndexByName('user_statuses_rank_index')
            ->update();

        $this->table('users')
            ->removeIndexByName('users_activation_index')
            ->removeIndexByName('users_country_index')
            ->removeIndexByName('users_created_index')
            ->removeIndexByName('users_email_index')
            ->removeIndexByName('users_expiration_index')
            ->removeIndexByName('users_first_name_index')
            ->removeIndexByName('users_is_confirmed_index')
            ->removeIndexByName('users_last_name_index')
            ->removeIndexByName('users_mobile_index')
            ->removeIndexByName('users_modified_index')
            ->removeIndexByName('users_password_expiry_index')
            ->removeIndexByName('users_post_code_index')
            ->removeIndexByName('users_state_index')
            ->removeIndexByName('users_user_statuses_id_index')
            ->removeIndexByName('users_username_index')
            ->update();
    }
}
