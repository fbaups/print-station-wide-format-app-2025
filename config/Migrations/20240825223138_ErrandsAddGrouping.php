<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class ErrandsAddGrouping extends AbstractMigration
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
            ->addColumn('grouping', 'string', [
                'after' => 'lock_to_thread',
                'default' => null,
                'length' => 50,
                'null' => true,
            ])
            ->addIndex(
                [
                    'grouping',
                ],
                [
                    'name' => 'errands_grouping_index',
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

        $this->table('errands')
            ->removeIndexByName('errands_grouping_index')
            ->update();

        $this->table('errands')
            ->removeColumn('grouping')
            ->update();
    }
}
