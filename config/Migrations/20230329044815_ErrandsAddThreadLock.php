<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ErrandsAddThreadLock extends AbstractMigration
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

        $this->table('errands')
            ->addColumn('lock_to_thread', 'integer', [
                'after' => 'hash_sum',
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

        $this->table('errands')
            ->removeColumn('lock_to_thread')
            ->update();
    }
}
