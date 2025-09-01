<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateErrorLevels extends AbstractMigration
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
        $this->table('error_levels')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('value', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('css_alert', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => true,
            ])
            ->create();

        $this->seed();
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

        $this->table('error_levels')->drop()->save();
    }

    public function seed()
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $levels = [
            ["0", "Emergency", "System is unusable", "danger"],
            ["1", "Alert", "Action must be taken immediately", "danger"],
            ["2", "Critical", "Critical conditions", "danger"],
            ["3", "Error", "Error conditions", "danger"],
            ["4", "Warning", "Warning conditions", "warning"],
            ["5", "Notice", "Normal but significant conditions", "primary"],
            ["6", "Informational", "Informational messages", "info"],
            ["7", "Debug", "Debug messages", "secondary"],
        ];

        $data = [];
        foreach ($levels as $level) {
            $data[] = [
                'created' => $currentDate,
                'modified' => $currentDate,
                'value' => $level[0],
                'name' => $level[1],
                'description' => $level[2],
                'css_alert' => $level[3],
            ];
        }

        if (!empty($data)) {
            $table = $this->table('error_levels');
            $table->insert($data)->save();
        }
    }
}


