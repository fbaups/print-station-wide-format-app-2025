<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class SeedArticleStatuses  extends BaseMigration
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
        $this->seedData();
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
    }

    public function seedData(): void
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [
            [
                'sort' => 4,
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Approval',
                'description' => 'The Article is awaiting approval and will not be shown to Users.',
            ],
            [
                'sort' => 5,
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Rejected',
                'description' => 'The Article has been rejected and will not be shown to Users.',
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('article_statuses');
            $table->insert($data)->save();
        }
    }

}
