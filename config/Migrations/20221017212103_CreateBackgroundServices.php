<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateBackgroundServices extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up()
    {
        $dbDriver = $this->getDbDriver();
        $largeTextType = $this->getLargeTextType();
        $largeTextLimit = $this->getLargeTextLimit();
        $largeTextTypeIndex = $this->getLargeTextTypeIndex();
        $largeTextLimitIndex = $this->getLargeTextLimitIndex();

        $this->table('errands')
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
            ->addColumn('activation', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expiration', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('auto_delete', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('wait_for_link', 'integer', [
                'default' => null,
                'limit' => 10,
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
            ->addColumn('worker_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('worker_name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('class', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('method', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('parameters', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('started', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('completed', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('progress_bar', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('return_value', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('return_message', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('errors_thrown', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('errors_retry', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('errors_retry_limit', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('hash_sum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addIndex(
                [
                    'activation',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ]
            )
            ->addIndex(
                [
                    'class',
                ]
            )
            ->addIndex(
                [
                    'completed',
                ]
            )
            ->addIndex(
                [
                    'created',
                ]
            )
            ->addIndex(
                [
                    'domain',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ]
            )
            ->addIndex(
                [
                    'hash_sum',
                ]
            )
            ->addIndex(
                [
                    'method',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ]
            )
            ->addIndex(
                [
                    'name',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ]
            )
            ->addIndex(
                [
                    'server',
                ]
            )
            ->addIndex(
                [
                    'started',
                ]
            )
            ->addIndex(
                [
                    'status',
                ]
            )
            ->addIndex(
                [
                    'wait_for_link',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('errands');

        $this->table('heartbeats')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('expiration', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('auto_delete', 'boolean', [
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
            ->addColumn('type', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('context', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('pid', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('description', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addIndex(
                [
                    'auto_delete',
                ]
            )
            ->addIndex(
                [
                    'context',
                ]
            )
            ->addIndex(
                [
                    'created',
                ]
            )
            ->addIndex(
                [
                    'description',
                ]
            )
            ->addIndex(
                [
                    'domain',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ]
            )
            ->addIndex(
                [
                    'name',
                ]
            )
            ->addIndex(
                [
                    'pid',
                ]
            )
            ->addIndex(
                [
                    'server',
                ]
            )
            ->addIndex(
                [
                    'type',
                ]
            )
            ->create();

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
                ]
            )
            ->addIndex(
                [
                    'force_retirement',
                ]
            )
            ->addIndex(
                [
                    'force_shutdown',
                ]
            )
            ->addIndex(
                [
                    'retirement_date',
                ]
            )
            ->addIndex(
                [
                    'termination_date',
                ]
            )
            ->create();

        $this->seedSettings();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down()
    {

        $this->table('errands')->drop()->save();
        $this->table('heartbeats')->drop()->save();
        $this->table('message_beacons')->drop()->save();
        $this->table('messages')->drop()->save();
        $this->table('workers')->drop()->save();
    }

    public function seedSettings()
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Errand Worker Limit',
                'description' => 'How many Errand Workers can be booted at the one time',
                'property_group' => 'errand_worker',
                'property_key' => 'errand_worker_limit',
                'property_value' => '4',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","11":"11","12":"12"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Errand Worker Life Expectancy',
                'description' => 'How long Errand Workers can run for till they are retired (minutes)',
                'property_group' => 'errand_worker',
                'property_key' => 'errand_worker_life_expectancy',
                'property_value' => '6',
                'selections' => '{"6":"6","10":"10","11":"11","12":"12","13":"13","14":"14","15":"15","16":"16","17":"17","18":"18","19":"19","20":"20"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Errand Worker Grace Period',
                'description' => 'Grace period for a long running Errand Worker before forced termination (minutes)',
                'property_group' => 'errand_worker',
                'property_key' => 'errand_worker_grace_period',
                'property_value' => '1',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Errand Retry Limit',
                'description' => 'How many times to retry running an Errand',
                'property_group' => 'errand_worker',
                'property_key' => 'errand_retry_limit',
                'property_value' => '3',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Errand Worker Sleep Timeout',
                'description' => 'How long to sleep for if there are no Errands to run',
                'property_group' => 'errand_worker',
                'property_key' => 'errand_worker_sleep',
                'property_value' => '2',
                'selections' => '{"2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","16":"16","32":"32"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Message Worker Limit',
                'description' => 'How many Message Workers can be booted at the one time',
                'property_group' => 'message_worker',
                'property_key' => 'message_worker_limit',
                'property_value' => '4',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","11":"11","12":"12"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Message Worker Life Expectancy',
                'description' => 'How long Message Workers can run for till they are retired (minutes)',
                'property_group' => 'message_worker',
                'property_key' => 'message_worker_life_expectancy',
                'property_value' => '6',
                'selections' => '{"6":"6","10":"10","11":"11","12":"12","13":"13","14":"14","15":"15","16":"16","17":"17","18":"18","19":"19","20":"20"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Message Worker Grace Period',
                'description' => 'Grace period for a long running Message Workers before forced termination (minutes)',
                'property_group' => 'message_worker',
                'property_key' => 'message_worker_grace_period',
                'property_value' => '1',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Message Retry Limit',
                'description' => 'How many times to retry sending a Message',
                'property_group' => 'message_worker',
                'property_key' => 'message_retry_limit',
                'property_value' => '4',
                'selections' => '{"1":"1","2":"2","3":"3","4":"4","5":"5"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ],
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Message Worker Sleep Timeout',
                'description' => 'How long to sleep for if there are no Messages to run',
                'property_group' => 'message_worker',
                'property_key' => 'message_worker_sleep',
                'property_value' => '2',
                'selections' => '{"2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","16":"16","32":"32"}',
                'html_select_type' => 'select',
                'match_pattern' => null,
                'is_masked' => null
            ]
        ];

        if (!empty($data)) {
            $table = $this->table('settings');
            $table->insert($data)->save();
        }
    }

}
