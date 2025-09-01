<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateOrdersJobsDocuments extends \App\Migrations\AppAbstractMigration
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

        $this->table('document_alerts')
            ->addColumn('document_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('level', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('message', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('document_properties')
            ->addColumn('document_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
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
            ->addColumn('meta_data', $largeTextType, [
                'default' => null,
                'length' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->create();

        $this->table('document_status_movements')
            ->addColumn('document_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('document_status_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('document_status_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('note', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->create();

        $this->table('document_statuses')
            ->addColumn('sort', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
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
            ->addColumn('allow_from_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('allow_to_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('icon', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('hex_code', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('documents')
            ->addColumn('guid', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('document_status_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
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
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => '1',
                'length' => 10,
                'null' => false,
            ])
            ->addColumn('artifact_token', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('external_document_number', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('external_creation_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('external_url', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('payload', $largeTextType, [
                'default' => null,
                'length' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('hash_sum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->create();

        $this->table('documents_users')
            ->addColumn('document_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $this->table('job_alerts')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('level', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('message', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('job_properties')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
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
            ->addColumn('meta_data', $largeTextType, [
                'default' => null,
                'length' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->create();

        $this->table('job_status_movements')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('job_status_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('job_status_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('note', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->create();

        $this->table('job_statuses')
            ->addColumn('sort', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
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
            ->addColumn('allow_from_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('allow_to_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('icon', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('hex_code', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('jobs')
            ->addColumn('guid', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('job_status_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
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
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('external_job_number', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('external_creation_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payload', $largeTextType, [
                'default' => null,
                'length' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('hash_sum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->create();

        $this->table('jobs_users')
            ->addColumn('job_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $this->table('order_alerts')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('level', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('message', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->addColumn('code', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('order_properties')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
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
            ->addColumn('meta_data', $largeTextType, [
                'default' => null,
                'length' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->create();

        $this->table('order_status_movements')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('order_status_from', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('order_status_to', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('note', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->create();

        $this->table('order_statuses')
            ->addColumn('sort', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
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
            ->addColumn('allow_from_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('allow_to_status', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('icon', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('hex_code', 'string', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->create();

        $this->table('orders')
            ->addColumn('guid', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('order_status_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
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
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('quantity', 'integer', [
                'default' => '1',
                'length' => 10,
                'null' => false,
            ])
            ->addColumn('external_system_type', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('external_order_number', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->addColumn('external_creation_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('payload', $largeTextType, [
                'default' => null,
                'length' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('hash_sum', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => true,
            ])
            ->create();

        $this->table('orders_users')
            ->addColumn('order_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $this->seedData();

        $this->convertNtextToNvarchar('document_properties');
        $this->convertNtextToNvarchar('documents');
        $this->convertNtextToNvarchar('job_properties');
        $this->convertNtextToNvarchar('jobs');
        $this->convertNtextToNvarchar('order_properties');
        $this->convertNtextToNvarchar('orders');
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

        $this->table('document_alerts')->drop()->save();
        $this->table('document_properties')->drop()->save();
        $this->table('document_status_movements')->drop()->save();
        $this->table('document_statuses')->drop()->save();
        $this->table('documents')->drop()->save();
        $this->table('documents_users')->drop()->save();
        $this->table('job_alerts')->drop()->save();
        $this->table('job_properties')->drop()->save();
        $this->table('job_status_movements')->drop()->save();
        $this->table('job_statuses')->drop()->save();
        $this->table('jobs')->drop()->save();
        $this->table('jobs_users')->drop()->save();
        $this->table('order_alerts')->drop()->save();
        $this->table('order_properties')->drop()->save();
        $this->table('order_status_movements')->drop()->save();
        $this->table('order_statuses')->drop()->save();
        $this->table('orders')->drop()->save();
        $this->table('orders_users')->drop()->save();
    }

    public function seedData()
    {
        /* ORDERS & JOBS
        Received            The order has been successfully submitted by the customer.
        Review              The order is being reviewed before production begins.
        Prepress            The order is being proofed and being prepared for printing.
        Printing            The order is currently being printed.
        Finishing           Post-printing processes like trimming, binding, laminating, etc., are being performed.
        Quality Control     The printed items are undergoing quality checks to ensure they meet standards.
        Packaging           The finished products are being packaged for shipment.
        Dispatch            The order is complete and is available for pickup or awaiting dispatch.
        Shipped             The order has been dispatched and is in transit to the customer.
        Delivered           The order has been successfully delivered to the customer.
        On Hold             There is an issue or delay with the order, and it cannot proceed until resolved.
        Cancelled           The order has been cancelled either by the customer or by the system/administrator.
        Refunded            A refund has been processed for the order.
        Completed           The entire order process, including production, shipping, and delivery, is finished.
        Archived            The order has been archived and is ready for deletion.
         */

        /* FILES
        Requested           A request to download and process a file has been received.
        Downloading         The file is being downloaded.
        Processing          The file is being processed and properties being catalogued.
        Ready               The file is ready for production.
        Error               The file failed to download or is failed to process.
        Archived            The file has been archived and is ready for deletion.
         */


        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [];
        $stats = [
            ['sort' => '1', 'name' => 'Received', 'description' => 'The order has been successfully submitted by the customer.', 'from' => '', 'to' => ''],
            ['sort' => '2', 'name' => 'Review', 'description' => 'The order is being reviewed before production begins.', 'from' => '', 'to' => ''],
            ['sort' => '3', 'name' => 'Prepress', 'description' => 'The order is being proofed and being prepared for printing.', 'from' => '', 'to' => ''],
            ['sort' => '4', 'name' => 'Printing', 'description' => 'The order is currently being printed.', 'from' => '', 'to' => ''],
            ['sort' => '5', 'name' => 'Finishing', 'description' => 'Post-printing processes like trimming, binding, laminating, etc., are being performed.', 'from' => '', 'to' => ''],
            ['sort' => '6', 'name' => 'Quality Control', 'description' => 'The printed items are undergoing quality checks to ensure they meet standards.', 'from' => '', 'to' => ''],
            ['sort' => '7', 'name' => 'Packaging', 'description' => 'The finished products are being packaged for shipment.', 'from' => '', 'to' => ''],
            ['sort' => '8', 'name' => 'Dispatch', 'description' => 'The order is complete and is available for pickup or awaiting dispatch.', 'from' => '', 'to' => ''],
            ['sort' => '9', 'name' => 'Shipped', 'description' => 'The order has been dispatched and is in transit to the customer.', 'from' => '', 'to' => ''],
            ['sort' => '10', 'name' => 'Delivered', 'description' => 'The order has been successfully delivered to the customer.', 'from' => '', 'to' => ''],
            ['sort' => '11', 'name' => 'On Hold', 'description' => 'There is an issue or delay with the order, and it cannot proceed until resolved.', 'from' => '', 'to' => ''],
            ['sort' => '12', 'name' => 'Cancelled', 'description' => 'The order has been cancelled either by the customer or by the system/administrator.', 'from' => '', 'to' => ''],
            ['sort' => '13', 'name' => 'Refunded', 'description' => 'A refund has been processed for the order.', 'from' => '', 'to' => ''],
            ['sort' => '14', 'name' => 'Completed', 'description' => 'The entire order process, including production, shipping, and delivery, is finished.', 'from' => '', 'to' => ''],
            ['sort' => '15', 'name' => 'Archived', 'description' => 'The order has been archived and is ready for deletion.', 'from' => '', 'to' => ''],
        ];
        foreach ($stats as $s => $stat) {
            $data[] = [
                'sort' => $stat['sort'],
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => $stat['name'],
                'description' => $stat['description'],
                'allow_from_status' => $stat['from'],
                'allow_to_status' => $stat['to'],
                'icon' => null,
                'hex_code' => null,
            ];
        }

        if (!empty($data)) {
            $table = $this->table('order_statuses');
            $table->insert($data)->save();

            $table = $this->table('job_statuses');
            $table->insert($data)->save();
        }

        $data = [];
        $stats = [
            ['sort' => '1', 'name' => 'Requested', 'description' => 'A request to download and process a file has been received.', 'from' => '', 'to' => ''],
            ['sort' => '2', 'name' => 'Downloading', 'description' => 'The file is being downloaded.', 'from' => '', 'to' => ''],
            ['sort' => '3', 'name' => 'Processing', 'description' => 'The file is being processed and properties being catalogued.', 'from' => '', 'to' => ''],
            ['sort' => '4', 'name' => 'Ready', 'description' => 'The file is ready for production.', 'from' => '', 'to' => ''],
            ['sort' => '5', 'name' => 'Error', 'description' => 'The file failed to download or is failed to process.', 'from' => '', 'to' => ''],
            ['sort' => '6', 'name' => 'Archived', 'description' => 'The file has been archived and is ready for deletion.', 'from' => '', 'to' => ''],
        ];
        foreach ($stats as $s => $stat) {
            $data[] = [
                'sort' => $stat['sort'],
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => $stat['name'],
                'description' => $stat['description'],
                'allow_from_status' => $stat['from'],
                'allow_to_status' => $stat['to'],
                'icon' => null,
                'hex_code' => null,
            ];
        }

        if (!empty($data)) {
            $table = $this->table('document_statuses');
            $table->insert($data)->save();
        }
    }

}
