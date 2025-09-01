<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class HotFolderSubmitUrl extends BaseMigration
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
            ->addColumn('submit_url', 'string', [
                'after' => 'stable_interval',
                'default' => null,
                'length' => 50,
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
            ->removeColumn('submit_url')
            ->update();
    }
}
