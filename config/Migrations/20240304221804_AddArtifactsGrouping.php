<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddArtifactsGrouping extends AbstractMigration
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
            ->addColumn('grouping', 'string', [
                'after' => 'hash_sum',
                'default' => null,
                'length' => 50,
                'null' => true,
            ])
            ->addIndex(
                [
                    'grouping',
                ],
                [
                    'name' => 'artifacts_grouping_index',
                ]
            )
            ->update();

        $this->table('hot_folders')
            ->addColumn('submit_url_enabled', 'boolean', [
                'after' => 'submit_url',
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

        $this->table('artifacts')
            ->removeIndexByName('artifacts_grouping_index')
            ->update();

        $this->table('artifacts')
            ->removeColumn('grouping')
            ->update();

        $this->table('hot_folders')
            ->removeColumn('submit_url_enabled')
            ->update();
    }
}
