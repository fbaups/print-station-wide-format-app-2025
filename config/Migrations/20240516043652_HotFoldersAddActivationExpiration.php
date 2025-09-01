<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class HotFoldersAddActivationExpiration extends AbstractMigration
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

        $this->table('hot_folders')
            ->addColumn('activation', 'datetime', [
                'after' => 'submit_url_enabled',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('expiration', 'datetime', [
                'after' => 'activation',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('auto_delete', 'boolean', [
                'after' => 'expiration',
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

        $this->table('hot_folders')
            ->removeColumn('activation')
            ->removeColumn('expiration')
            ->removeColumn('auto_delete')
            ->update();
    }
}
