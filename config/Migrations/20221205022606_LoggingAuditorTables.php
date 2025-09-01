<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class LoggingAuditorTables extends BaseMigration
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

        $this->table('application_logs')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('level', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('user_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('url', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('message', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('message_overflow', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addIndex(
                [
                    'level',
                ]
            )
            ->addIndex(
                [
                    'user_link',
                ]
            )
            ->addIndex(
                [
                    'url',
                ]
            )
            ->addIndex(
                [
                    'message',
                ]
            )
            ->addIndex(
                [
                    'created',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('application_logs');

        $this->table('audits')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('expiration', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('level', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('user_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('url', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('message', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('message_overflow', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addIndex(
                [
                    'level',
                ]
            )
            ->addIndex(
                [
                    'user_link',
                ]
            )
            ->addIndex(
                [
                    'url',
                ]
            )
            ->addIndex(
                [
                    'message',
                ]
            )
            ->addIndex(
                [
                    'created',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('audits');
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

        $this->table('application_logs')->drop()->save();
        $this->table('audits')->drop()->save();
    }
}
