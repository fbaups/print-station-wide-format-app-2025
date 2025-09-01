<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class UpdateIntegrationCredentialsAddLastStatus extends BaseMigration
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

        $this->table('integration_credentials')
            ->addColumn('last_status_text', 'string', [
                'after' => 'tracking_data',
                'default' => null,
                'length' => 20,
                'null' => true,
            ])
            ->addColumn('last_status_html', 'string', [
                'after' => 'last_status_text',
                'default' => null,
                'length' => 200,
                'null' => true,
            ])
            ->addColumn('last_status_datetime', 'datetime', [
                'after' => 'last_status_html',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->update();
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

        $this->table('integration_credentials')
            ->removeColumn('last_status_text')
            ->removeColumn('last_status_html')
            ->removeColumn('last_status_datetime')
            ->update();
    }
}
