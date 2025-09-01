<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateBackgroundServicesTable extends BaseMigration
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

        $this->table('application_logs')
            ->changeColumn('url', 'string', [
                'default' => null,
                'limit' => 850,
                'null' => true,
            ])
            ->update();

        $this->table('audits')
            ->changeColumn('url', 'string', [
                'default' => null,
                'limit' => 850,
                'null' => true,
            ])
            ->update();

        $this->table('background_services')
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
            ->addColumn('pid', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('current_state', 'string', [
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
            ->addColumn('force_recycle', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('force_shutdown', 'boolean', [
                'default' => null,
                'limit' => null,
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
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'background_services_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'background_services_modified_index',
                ]
            )
            ->addIndex(
                [
                    'server',
                ],
                [
                    'name' => 'background_services_server_index',
                ]
            )
            ->addIndex(
                [
                    'domain',
                ],
                [
                    'name' => 'background_services_domain_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'background_services_name_index',
                ]
            )
            ->addIndex(
                [
                    'type',
                ],
                [
                    'name' => 'background_services_type_index',
                ]
            )
            ->addIndex(
                [
                    'pid',
                ],
                [
                    'name' => 'background_services_pid_index',
                ]
            )
            ->addIndex(
                [
                    'current_state',
                ],
                [
                    'name' => 'background_services_current_state_index',
                ]
            )
            ->addIndex(
                [
                    'appointment_date',
                ],
                [
                    'name' => 'background_services_appointment_date_index',
                ]
            )
            ->addIndex(
                [
                    'retirement_date',
                ],
                [
                    'name' => 'background_services_retirement_date_index',
                ]
            )
            ->addIndex(
                [
                    'termination_date',
                ],
                [
                    'name' => 'background_services_termination_date_index',
                ]
            )
            ->addIndex(
                [
                    'force_recycle',
                ],
                [
                    'name' => 'background_services_force_recycle_index',
                ]
            )
            ->addIndex(
                [
                    'force_shutdown',
                ],
                [
                    'name' => 'background_services_force_shutdown_index',
                ]
            )
            ->addIndex(
                [
                    'errand_link',
                ],
                [
                    'name' => 'background_services_errand_link_index',
                ]
            )
            ->addIndex(
                [
                    'errand_name',
                ],
                [
                    'name' => 'background_services_errand_name_index',
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

        $this->table('application_logs')
            ->changeColumn('url', 'string', [
                'default' => null,
                'length' => 1024,
                'null' => true,
            ])
            ->update();

        $this->table('audits')
            ->changeColumn('url', 'string', [
                'default' => null,
                'length' => 1024,
                'null' => true,
            ])
            ->update();

        $this->table('background_services')->drop()->save();
    }
}
