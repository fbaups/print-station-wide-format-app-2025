<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class UpdateBackgroundServicesLimits extends AbstractMigration
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
        $newSelections = '{"0":"0","1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","11":"11","12":"12"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'errand_worker_limit'";
        $this->execute($sql);

        $newSelections = '{"0":"0","1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","11":"11","12":"12"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'message_worker_limit'";
        $this->execute($sql);

        $newSelections = '{"0":"0","1":"1","2":"2","3":"3","4":"4"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'repo_purge_limit'";
        $this->execute($sql);

        $newSelections = '{"0":"0","1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'hot_folder_worker_limit'";
        $this->execute($sql);
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
        $newSelections = '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","11":"11","12":"12"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'errand_worker_limit'";
        $this->execute($sql);

        $newSelections = '{"1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9","10":"10","11":"11","12":"12"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'message_worker_limit'";
        $this->execute($sql);

        $newSelections = '{"0":"0","1":"1","2":"2","3":"3","4":"4"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'repo_purge_limit'";
        $this->execute($sql);

        $newSelections = '{"0":"0","1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8"}';
        $sql = "UPDATE settings SET selections = '{$newSelections}' WHERE  'property_key' = 'hot_folder_worker_limit'";
        $this->execute($sql);
    }
}
