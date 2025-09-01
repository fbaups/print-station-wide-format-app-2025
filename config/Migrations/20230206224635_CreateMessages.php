<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateMessages extends \App\Migrations\AppAbstractMigration
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
        $dbDriver = $this->getDbDriver();
        $largeTextType = $this->getLargeTextType();
        $largeTextLimit = $this->getLargeTextLimit();
        $largeTextTypeIndex = $this->getLargeTextTypeIndex();
        $largeTextLimitIndex = $this->getLargeTextLimitIndex();

        //drop tables as they were created in previous migrations before Messages functionality was ready
        if($this->hasTable('message_connections')){
            $this->table('message_connections')->drop()->save();
        }
        if($this->hasTable('message_beacons')){
            $this->table('message_beacons')->drop()->save();
        }
        if($this->hasTable('messages')){
            $this->table('messages')->drop()->save();
        }

        $this->table('message_connections')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('message_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('direction', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('message_beacons')
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
            ->addColumn('beacon_hash', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('beacon_url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('beacon_data', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addIndex(
                [
                    'beacon_hash',
                ]
            )
            ->addIndex(
                [
                    'created',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ]
            )
            ->create();

        $this->table('messages')
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
                'default' => null,
                'limit' => 50,
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
            ->addColumn('server', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('domain', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('transport', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('profile', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('layout', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('template', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('email_format', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('sender', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('email_from', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('email_to', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('email_cc', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('email_bcc', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('reply_to', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('read_receipt', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('subject', $largeTextTypeIndex, [
                'default' => null,
                'limit' => $largeTextLimitIndex,
                'null' => true,
            ])
            ->addColumn('view_vars', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('headers', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('smtp_code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('smtp_message', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('lock_code', 'integer', [
                'default' => null,
                'limit' => 10,
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
            ->addColumn('beacon_hash', 'string', [
                'default' => null,
                'limit' => 50,
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
                    'beacon_hash',
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
                    'email_to',
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
                    'lock_code',
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
                    'sender',
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
                    'subject',
                ]
            )
            ->addIndex(
                [
                    'type',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('messages');
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

        $this->table('message_connections')->drop()->save();
        $this->table('message_beacons')->drop()->save();
        $this->table('messages')->drop()->save();
    }
}
