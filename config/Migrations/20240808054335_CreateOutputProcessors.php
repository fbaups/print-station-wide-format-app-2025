<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateOutputProcessors extends AbstractMigration
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
        $this->table('output_processors')
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
            ->addColumn('type', 'string', [
                'after' => 'parameters',
                'default' => null,
                'length' => 50,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 512,
                'null' => true,
            ])
            ->addColumn('is_enabled', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('parameters', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'type',
                ],
                [
                    'name' => 'output_processors_type_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'output_processors_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'output_processors_modified_index',
                ]
            )
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'output_processors_description_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'output_processors_name_index',
                ]
            )
            ->addIndex(
                [
                    'is_enabled',
                ],
                [
                    'name' => 'output_processors_is_enabled_index',
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

        $this->table('output_processors')->drop()->save();
    }
}
