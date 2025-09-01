<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateDataBlobs extends BaseMigration
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
        $dbDriver = $this->getDbDriver();
        $largeTextType = $this->getLargeTextType();
        $largeTextLimit = $this->getLargeTextLimit();
        $largeTextTypeIndex = $this->getLargeTextTypeIndex();
        $largeTextLimitIndex = $this->getLargeTextLimitIndex();

        $this->table('data_blobs')
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
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('grouping', 'string', [
                'default' => null,
                'length' => 50,
                'null' => true,
            ])
            ->addColumn('format', 'string', [
                'default' => null,
                'length' => 100,
                'null' => true,
            ])
            ->addColumn('blob', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('hash_sum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addIndex(
                [
                    'grouping',
                ],
                [
                    'name' => 'data_blobs_grouping_index',
                ]
            )
            ->addIndex(
                [
                    'format',
                ],
                [
                    'name' => 'data_blobs_format_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'data_blobs_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'data_blobs_modified_index',
                ]
            )
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'data_blobs_activation_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'data_blobs_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'data_blobs_auto_delete_index',
                ]
            )
            ->addIndex(
                [
                    'hash_sum',
                ],
                [
                    'name' => 'data_blobs_hash_sum_index',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('data_blobs');
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

        $this->table('data_blobs')->drop()->save();
    }
}
