<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateTheatrePins extends BaseMigration
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
        $this->table('theatre_pins')
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
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('pin_code', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_link', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                $this->index('created')
                    ->setName('theatre_pins_created_index')
            )
            ->addIndex(
                $this->index('modified')
                    ->setName('theatre_pins_modified_index')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('theatre_pins_name_index')
            )
            ->addIndex(
                $this->index('pin_code')
                    ->setName('theatre_pins_pin_code_index')
            )
            ->addIndex(
                $this->index('user_link')
                    ->setName('theatre_pins_user_link_index')
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

        $this->table('theatre_pins')->drop()->save();
    }
}
