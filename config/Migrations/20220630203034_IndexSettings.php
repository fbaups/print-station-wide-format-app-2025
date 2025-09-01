<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class IndexSettings extends BaseMigration
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

        $this->table('settings')
            ->addIndex(
                [
                    'description',
                ],
                [
                    'name' => 'settings_description_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'settings_name_index',
                ]
            )
            ->addIndex(
                [
                    'property_group',
                ],
                [
                    'name' => 'settings_property_group_index',
                ]
            )
            ->addIndex(
                [
                    'property_key',
                ],
                [
                    'name' => 'settings_property_key_index',
                ]
            )
            ->addIndex(
                [
                    'property_value',
                ],
                [
                    'name' => 'settings_property_value_index',
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

        $this->table('settings')
            ->removeIndexByName('settings_description_index')
            ->removeIndexByName('settings_name_index')
            ->removeIndexByName('settings_property_group_index')
            ->removeIndexByName('settings_property_key_index')
            ->removeIndexByName('settings_property_value_index')
            ->update();
    }
}
