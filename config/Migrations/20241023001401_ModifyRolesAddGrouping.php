<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class ModifyRolesAddGrouping extends BaseMigration
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

        $this->table('roles')
            ->addColumn('grouping', 'string', [
                'after' => 'session_timeout',
                'default' => null,
                'length' => 16,
                'null' => true,
            ])
            ->addIndex(
                [
                    'grouping',
                ],
                [
                    'name' => 'roles_grouping_index',
                ]
            )
            ->update();


        $groupings = [
            'superadmin' => 'Administrators',
            'admin' => 'Administrators',
            'superuser' => 'Consumers',
            'user' => 'Consumers',
            'manager' => 'Producers',
            'supervisor' => 'Producers',
            'operator' => 'Producers',
        ];
        foreach ($groupings as $alias => $grouping) {
            $builder = $this->getUpdateBuilder();
            $builder
                ->update('roles')
                ->set('grouping', $grouping)
                ->where(['alias' => $alias])
                ->execute();
        }
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

        $this->table('roles')
            ->removeIndexByName('roles_grouping_index')
            ->update();

        $this->table('roles')
            ->removeColumn('grouping')
            ->update();
    }
}
