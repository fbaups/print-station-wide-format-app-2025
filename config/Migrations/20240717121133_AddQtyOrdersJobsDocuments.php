<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * These migrations only runs for older installations.
 *
 * Classes:
 * 20240320091646_CreateOrdersJobsDocuments()
 * 20240519230239_OverhaulIndexes1()
 * 20240519235734_OverhaulIndexes2()
 * have been updated so that new installations are initialised properly.
 *
 */
class AddQtyOrdersJobsDocuments extends AbstractMigration
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
        //modify jobs.quantity to have default 1
        $this->table('jobs')
            ->changeColumn('quantity', 'integer', [
                'default' => '1',
                'null' => false,
            ])
            ->update();

        // Add column and index to 'documents' table if not exist
        $documentsTable = $this->table('documents');
        if (!$documentsTable->hasColumn('quantity')) {
            $documentsTable
                ->addColumn('quantity', 'integer', [
                    'default' => '1',
                    'length' => 10,
                    'null' => false,
                ])
                ->update();
        }
        if (!$documentsTable->hasIndex('quantity')) {
            $documentsTable
                ->addIndex(['quantity'], [
                    'name' => 'documents_quantity_index',
                ])
                ->update();
        }

        // Add column and index to 'orders' table if not exist
        $ordersTable = $this->table('orders');
        if (!$ordersTable->hasColumn('quantity')) {
            $ordersTable
                ->addColumn('quantity', 'integer', [
                    'default' => '1',
                    'length' => 10,
                    'null' => false,
                ])
                ->update();
        }
        if (!$ordersTable->hasIndex('quantity')) {
            $ordersTable
                ->addIndex(['quantity'], [
                    'name' => 'orders_quantity_index',
                ])
                ->update();
        }
        if (!$ordersTable->hasColumn('external_system_type')) {
            $ordersTable
                ->addColumn('external_system_type', 'string', [
                    'default' => null,
                    'limit' => 50,
                    'null' => true,
                ])
                ->update();
        }
        if (!$ordersTable->hasIndex('external_system_type')) {
            $ordersTable
                ->addIndex(['external_system_type'], [
                    'name' => 'orders_external_system_type_index',
                ])
                ->update();
        }
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
        // Remove index and column from 'documents' table if exist
        $documentsTable = $this->table('documents');
        if ($documentsTable->hasIndexByName('documents_quantity_index')) {
            $documentsTable
                ->removeIndexByName('documents_quantity_index')
                ->update();
        }
        if ($documentsTable->hasColumn('quantity')) {
            $documentsTable
                ->removeColumn('quantity')
                ->update();
        }

        // Remove index and column from 'orders' table if exist
        $ordersTable = $this->table('orders');
        if ($ordersTable->hasIndexByName('orders_quantity_index')) {
            $ordersTable
                ->removeIndexByName('orders_quantity_index')
                ->update();
        }
        if ($ordersTable->hasColumn('quantity')) {
            $ordersTable
                ->removeColumn('quantity')
                ->update();
        }
        if ($ordersTable->hasIndexByName('orders_external_system_type_index')) {
            $ordersTable
                ->removeIndexByName('orders_external_system_type_index')
                ->update();
        }
        if ($ordersTable->hasColumn('external_system_type')) {
            $ordersTable
                ->removeColumn('external_system_type')
                ->update();
        }
    }
}
