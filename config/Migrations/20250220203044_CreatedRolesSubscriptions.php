<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreatedRolesSubscriptions extends BaseMigration
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
        $this->table('roles_subscriptions')
            ->addColumn('subscription_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addIndex(
                [
                    'role_id',
                ],
                [
                    'name' => 'roles_subscriptions_role_id_index',
                ]
            )
            ->addIndex(
                [
                    'subscription_id',
                ],
                [
                    'name' => 'roles_subscriptions_subscription_id_index',
                ]
            )
            ->create();
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

        $this->table('roles_subscriptions')->drop()->save();
    }
}
