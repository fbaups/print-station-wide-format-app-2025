<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class RemoveDeprecatedRepoSettings extends BaseMigration
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

        $sql = "DELETE FROM settings WHERE property_key = 'repo_purge_interval'";
        $this->execute($sql);

        $sql = "DELETE FROM settings WHERE property_key = 'repo_purge_limit'";
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
    }
}
