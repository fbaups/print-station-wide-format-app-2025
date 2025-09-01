<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateArtifactsTables extends BaseMigration
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

        $this->table('artifact_metadata')
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
            ->addColumn('artifact_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('width', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('height', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('exif', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->create();

        $this->convertNtextToNvarchar('artifact_metadata');

        $this->table('artifacts')
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
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('size', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('mime_type', 'string', [
                'default' => null,
                'limit' => 50,
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
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('url', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('unc', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('hash_sum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->create();

        $this->table('artifact_metadata')
            ->addIndex(
                [
                    'artifact_id',
                ],
                [
                    'name' => 'artifact_metadata_artifact_id_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'artifact_metadata_created_index',
                ]
            )
            ->addIndex(
                [
                    'height',
                ],
                [
                    'name' => 'artifact_metadata_height_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'artifact_metadata_modified_index',
                ]
            )
            ->addIndex(
                [
                    'width',
                ],
                [
                    'name' => 'artifact_metadata_width_index',
                ]
            )
            ->update();

        $this->table('artifacts')
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'artifacts_activation_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'artifacts_created_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'artifacts_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'hash_sum',
                ],
                [
                    'name' => 'artifacts_hash_sum_index',
                ]
            )
            ->addIndex(
                [
                    'mime_type',
                ],
                [
                    'name' => 'artifacts_mime_type_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'artifacts_modified_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'artifacts_name_index',
                ]
            )
            ->addIndex(
                [
                    'token',
                ],
                [
                    'name' => 'artifacts_token_index',
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
    public function down()
    {

        $this->table('artifact_metadata')->drop()->save();
        $this->table('artifacts')->drop()->save();
    }
}
