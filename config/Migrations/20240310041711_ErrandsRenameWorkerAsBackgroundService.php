<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ErrandsRenameWorkerAsBackgroundService extends AbstractMigration
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

        try {
            $this->table('errands')
                ->renameColumn('worker_name', 'background_service_name')
                ->renameColumn('worker_link', 'background_service_link')
                ->update();
        } catch (\Throwable $exception) {

        }

        $sql = "UPDATE settings SET property_key = REPLACE(property_key, '_worker_', '_background_service_') WHERE property_key like '%_worker_%'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'errand_background_service_retry_limit' WHERE property_key = 'errand_retry_limit'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'message_background_service_retry_limit' WHERE property_key = 'message_retry_limit'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'hot_folder_background_service_retry_limit' WHERE property_key = 'hot_folder_retry_limit'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'database_purger_background_service_retry_limit' WHERE property_key = 'database_purger_retry_limit'";
        $this->execute($sql);
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
        try {
            $this->table('errands')
                ->renameColumn('background_service_name', 'worker_name')
                ->renameColumn('background_service_link', 'worker_link')
                ->update();
        } catch (\Throwable $exception) {

        }

        $sql = "UPDATE settings SET property_key = REPLACE(property_key, '_background_service_', '_worker_') WHERE property_key like '%_background_service_%'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'errand_retry_limit' WHERE property_key = 'errand_background_service_retry_limit'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'message_retry_limit' WHERE property_key = 'message_background_service_retry_limit'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'hot_folder_retry_limit' WHERE property_key = 'hot_folder_background_service_retry_limit'";
        $this->execute($sql);

        $sql = "UPDATE settings SET property_key = 'database_purger_retry_limit' WHERE property_key = 'database_purger_background_service_retry_limit'";
        $this->execute($sql);
    }
}
