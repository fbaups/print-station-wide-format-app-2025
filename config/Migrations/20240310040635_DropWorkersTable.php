<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class DropWorkersTable extends BaseMigration
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

        $this->table('workers')->drop()->save();
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
        $this->table('workers')
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
            ->addColumn('server', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('domain', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('errand_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('errand_name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('appointment_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('retirement_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('termination_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('force_retirement', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('force_shutdown', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('pid', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('background_services_link', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addIndex(
                [
                    'appointment_date',
                ],
                [
                    'name' => 'workers_appointment_date',
                ]
            )
            ->addIndex(
                [
                    'force_retirement',
                ],
                [
                    'name' => 'workers_force_retirement',
                ]
            )
            ->addIndex(
                [
                    'force_shutdown',
                ],
                [
                    'name' => 'workers_force_shutdown',
                ]
            )
            ->addIndex(
                [
                    'retirement_date',
                ],
                [
                    'name' => 'workers_retirement_date',
                ]
            )
            ->addIndex(
                [
                    'termination_date',
                ],
                [
                    'name' => 'workers_termination_date',
                ]
            )
            ->create();
    }
}
