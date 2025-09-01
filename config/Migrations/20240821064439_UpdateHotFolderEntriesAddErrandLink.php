<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UpdateHotFolderEntriesAddErrandLink extends AbstractMigration
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
            ->addColumn('errand_link', 'integer', [
                'after' => 'lock_code',
                'default' => null,
                'length' => 10,
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
            ->removeColumn('errand_link')
            ->update();
    }
}
