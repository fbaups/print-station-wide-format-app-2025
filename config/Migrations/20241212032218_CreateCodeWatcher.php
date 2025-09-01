<?php
declare(strict_types=1);

use arajcany\ToolBox\Utility\TextFormatter;
use App\Migrations\AppBaseMigration as BaseMigration;

class CreateCodeWatcher extends BaseMigration
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
        $this->table('code_watcher_files')
            ->addColumn('code_watcher_folder_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('local_timezone', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('local_year', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('local_month', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('local_day', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('local_hour', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('local_minute', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('local_second', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('time_grouping_key', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('path_checksum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('base_path', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('file_path', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('sha1', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('crc32', 'string', [
                'default' => null,
                'limit' => 20,
                'null' => true,
            ])
            ->addColumn('mime', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('size', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'code_watcher_folder_id',
                ],
                [
                    'name' => 'code_watcher_files_code_watcher_folder_id_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'code_watcher_files_created_index',
                ]
            )
            ->addIndex(
                [
                    'local_timezone',
                ],
                [
                    'name' => 'code_watcher_files_local_timezone_index',
                ]
            )
            ->addIndex(
                [
                    'local_year',
                ],
                [
                    'name' => 'code_watcher_files_local_year_index',
                ]
            )
            ->addIndex(
                [
                    'local_month',
                ],
                [
                    'name' => 'code_watcher_files_local_month_index',
                ]
            )
            ->addIndex(
                [
                    'local_day',
                ],
                [
                    'name' => 'code_watcher_files_local_day_index',
                ]
            )
            ->addIndex(
                [
                    'local_hour',
                ],
                [
                    'name' => 'code_watcher_files_local_hour_index',
                ]
            )
            ->addIndex(
                [
                    'local_minute',
                ],
                [
                    'name' => 'code_watcher_files_local_minute_index',
                ]
            )
            ->addIndex(
                [
                    'local_second',
                ],
                [
                    'name' => 'code_watcher_files_local_second_index',
                ]
            )
            ->addIndex(
                [
                    'time_grouping_key',
                ],
                [
                    'name' => 'code_watcher_files_time_grouping_key_index',
                ]
            )
            ->addIndex(
                [
                    'path_checksum',
                ],
                [
                    'name' => 'code_watcher_files_path_checksum_index',
                ]
            )
            ->addIndex(
                [
                    'base_path',
                ],
                [
                    'name' => 'code_watcher_files_base_path_index',
                ]
            )
            ->addIndex(
                [
                    'file_path',
                ],
                [
                    'name' => 'code_watcher_files_file_path_index',
                ]
            )
            ->addIndex(
                [
                    'sha1',
                ],
                [
                    'name' => 'code_watcher_files_sha1_index',
                ]
            )
            ->addIndex(
                [
                    'crc32',
                ],
                [
                    'name' => 'code_watcher_files_crc32_index',
                ]
            )
            ->addIndex(
                [
                    'mime',
                ],
                [
                    'name' => 'code_watcher_files_mime_index',
                ]
            )
            ->addIndex(
                [
                    'size',
                ],
                [
                    'name' => 'code_watcher_files_size_index',
                ]
            )
            ->create();

        $this->table('code_watcher_folders')
            ->addColumn('code_watcher_project_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
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
            ->addColumn('base_path', 'string', [
                'default' => null,
                'limit' => 850,
                'null' => true,
            ])
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'code_watcher_folders_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'code_watcher_folders_modified_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'code_watcher_folders_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'code_watcher_folders_activation_index',
                ]
            )
            ->addIndex(
                [
                    'base_path',
                ],
                [
                    'name' => 'code_watcher_folders_base_path_index',
                ]
            )
            ->addIndex(
                [
                    'code_watcher_project_id',
                ],
                [
                    'name' => 'code_watcher_folders_code_watcher_project_id_index',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'code_watcher_folders_auto_delete_index',
                ]
            )
            ->create();

        $this->table('code_watcher_projects')
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
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 850,
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
            ->addColumn('enable_tracking', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'code_watcher_projects_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'code_watcher_projects_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'code_watcher_projects_name_index',
                ]
            )
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'code_watcher_projects_description_index',
                ]
            )
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'code_watcher_projects_activation_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'code_watcher_projects_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'code_watcher_projects_auto_delete_index',
                ]
            )
            ->addIndex(
                [
                    'enable_tracking',
                ],
                [
                    'name' => 'code_watcher_projects_enable_tracking_index',
                ]
            )
            ->create();

        $this->seedData();
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
        $this->table('code_watcher_files')->drop()->save();
        $this->table('code_watcher_folders')->drop()->save();
        $this->table('code_watcher_projects')->drop()->save();
    }


    public function seedData(): void
    {
        $root = TextFormatter::makeDirectoryTrailingSmartSlash(ROOT);

        $currentDate = gmdate("Y-m-d H:i:s");

        //create a Project
        $data = [
            [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => APP_NAME,
                'description' => 'Tracking the code development of ' . APP_NAME . '.',
                'enable_tracking' => true,
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('code_watcher_projects');
            $table->insert($data)->save();
        }


        //create Folders in the Project
        $data = [
            [
                'code_watcher_project_id' => 1,
                'created' => $currentDate,
                'modified' => $currentDate,
                'base_path' => TextFormatter::makeDirectoryTrailingSmartSlash($root . 'config'),
            ],
            [
                'code_watcher_project_id' => 1,
                'created' => $currentDate,
                'modified' => $currentDate,
                'base_path' => TextFormatter::makeDirectoryTrailingSmartSlash($root . 'src'),
            ],
            [
                'code_watcher_project_id' => 1,
                'created' => $currentDate,
                'modified' => $currentDate,
                'base_path' => TextFormatter::makeDirectoryTrailingSmartSlash($root . 'templates'),
            ],
            [
                'code_watcher_project_id' => 1,
                'created' => $currentDate,
                'modified' => $currentDate,
                'base_path' => TextFormatter::makeDirectoryTrailingSmartSlash($root . 'webroot'),
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('code_watcher_folders');
            $table->insert($data)->save();
        }


    }

}
