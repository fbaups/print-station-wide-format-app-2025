<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateIntegrationCredentials extends BaseMigration
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

        $this->table('integration_credentials')
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
            ->addColumn('parameters', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('tracking_hash', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('tracking_data', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addIndex(
                [
                    'type',
                ],
                [
                    'name' => 'integration_credentials_type_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'integration_credentials_created_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'integration_credentials_modified_index',
                ]
            )
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'integration_credentials_description_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'integration_credentials_name_index',
                ]
            )
            ->addIndex(
                [
                    'is_enabled',
                ],
                [
                    'name' => 'integration_credentials_is_enabled_index',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('integration_credentials');
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

        $this->table('integration_credentials')->drop()->save();
    }
}
