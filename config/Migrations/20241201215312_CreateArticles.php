<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateArticles extends BaseMigration
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
        $dbDriver = $this->getDbDriver();
        $largeTextType = $this->getLargeTextType();
        $largeTextLimit = $this->getLargeTextLimit();
        $largeTextTypeIndex = $this->getLargeTextTypeIndex();
        $largeTextLimitIndex = $this->getLargeTextLimitIndex();

        $this->table('article_statuses')
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
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'article_statuses_modified_index',
                ]
            )
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'article_statuses_created_index',
                ]
            )
            ->addIndex(
                [
                    'sort',
                ],
                [
                    'name' => 'article_statuses_sort_index',
                ]
            )
            ->addIndex(
                [
                    'name',
                ],
                [
                    'name' => 'article_statuses_name_index',
                ]
            )
            ->create();

        $this->table('articles')
            ->addColumn('article_status_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_link', 'integer', [
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
            ->addColumn('title', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('body', $largeTextType, [
                'default' => null,
                'limit' => $largeTextLimit,
                'null' => true,
            ])
            ->addColumn('priority', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'created',
                ],
                [
                    'name' => 'articles_created_index',
                ]
            )
            ->addIndex(
                [
                    'title',
                ],
                [
                    'name' => 'articles_title_index',
                ]
            )
            ->addIndex(
                [
                    'modified',
                ],
                [
                    'name' => 'articles_modified_index',
                ]
            )
            ->addIndex(
                [
                    'auto_delete',
                ],
                [
                    'name' => 'articles_auto_delete_index',
                ]
            )
            ->addIndex(
                [
                    'activation',
                ],
                [
                    'name' => 'articles_activation_index',
                ]
            )
            ->addIndex(
                [
                    'expiration',
                ],
                [
                    'name' => 'articles_expiration_index',
                ]
            )
            ->addIndex(
                [
                    'user_link',
                ],
                [
                    'name' => 'articles_user_link_index',
                ]
            )
            ->addIndex(
                [
                    'priority',
                ],
                [
                    'name' => 'articles_priority_index',
                ]
            )
            ->create();

        $this->convertNtextToNvarchar('articles');

        $this->table('articles_roles')
            ->addColumn('article_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'article_id',
                ],
                [
                    'name' => 'articles_roles_article_id_index',
                ]
            )
            ->addIndex(
                [
                    'role_id',
                ],
                [
                    'name' => 'articles_roles_role_id_index',
                ]
            )
            ->create();

        $this->table('articles_users')
            ->addColumn('article_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addIndex(
                [
                    'article_id',
                ],
                [
                    'name' => 'articles_users_article_id_index',
                ]
            )
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'articles_users_user_id_index',
                ]
            )
            ->create();

        $this->seedData();
    }

    public function seedData(): void
    {
        $currentDate = gmdate("Y-m-d H:i:s");

        $data = [
            [
                'sort' => 1,
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Draft',
                'description' => 'The Article is a draft and will not be shown to Users.',
            ],
            [
                'sort' => 2,
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Published',
                'description' => 'The Article is published and will can be shown to Users.',
            ],
            [
                'sort' => 3,
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => 'Archived',
                'description' => 'The Article has been archived and will not be shown to Users.',
            ],
        ];

        if (!empty($data)) {
            $table = $this->table('article_statuses');
            $table->insert($data)->save();
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
        $this->table('article_statuses')->drop()->save();
        $this->table('articles')->drop()->save();
        $this->table('articles_roles')->drop()->save();
        $this->table('articles_users')->drop()->save();
    }
}
