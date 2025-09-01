<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class UpdateRolesInactivityTimeout extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {

        $this->table('roles')
            ->addColumn('inactivity_timeout', 'integer', [
                'after' => 'grouping',
                'default' => null,
                'length' => 10,
                'null' => true,
            ])
            ->addIndex(
                $this->index('inactivity_timeout')
                    ->setName('roles_inactivity_timeout_index')
            )
            ->update();


        $builder = $this->getUpdateBuilder();
        $builder
            ->update('roles')
            ->set('inactivity_timeout', '5') //default 5 minute inactivity timeout
            ->execute();
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
            ->removeColumn('inactivity_timeout')
            ->update();
    }
}
