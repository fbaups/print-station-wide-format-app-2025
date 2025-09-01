<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateSubscriptions extends BaseMigration
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
        $this->convertNtextToNvarchar('articles');

        $this->table('subscriptions')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 512,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'subscriptions_name_index',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ],
                [
                    'name' => 'subscriptions_priority_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'subscriptions_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'subscriptions_modified_index',
                ]
            )
            ->create();

        $this->table('subscriptions_users')
            ->addColumn('subscription_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addIndex(
                [
                    'subscription_id',
                ],
                [
                    'name' => 'subscriptions_users_subscription_id_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'subscriptions_users_user_id_index',
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

        $this->table('subscriptions')->drop()->save();
        $this->table('subscriptions_users')->drop()->save();
    }
}
