<?php
declare(strict_types=1);

use App\Migrations\AppBaseMigration as BaseMigration;

class CreateFooAuthorsRecipes extends BaseMigration
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
        $this->table('foo_authors')
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
            ->addColumn('is_active', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('foo_authors_foo_recipes')
            ->addColumn('foo_author_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('foo_recipe_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->create();

        $this->table('foo_ingredients')
            ->addColumn('foo_recipe_id', 'integer', [
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
            ->addColumn('rank', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('text', 'string', [
                'default' => null,
                'limit' => 256,
                'null' => true,
            ])
            ->create();

        $this->table('foo_methods')
            ->addColumn('foo_recipe_id', 'integer', [
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
            ->addColumn('rank', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('text', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->create();

        $this->table('foo_tags')
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
                'limit' => 2048,
                'null' => true,
            ])
            ->create();

        $this->table('foo_recipes')
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
                'limit' => 256,
                'null' => true,
            ])
            ->addColumn('description', 'string', [
                'default' => null,
                'limit' => 1024,
                'null' => true,
            ])
            ->addColumn('publish_date', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('ingredient_count', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('method_count', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('meta', 'string', [
                'default' => null,
                'limit' => 2048,
                'null' => true,
            ])
            ->create();

        $this->table('foo_recipes_foo_tags')
            ->addColumn('foo_recipe_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
            ])
            ->addColumn('foo_tag_id', 'integer', [
                'default' => null,
                'limit' => 10,
                'null' => false,
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

        $this->table('foo_authors')->drop()->save();
        $this->table('foo_authors_foo_recipes')->drop()->save();
        $this->table('foo_ingredients')->drop()->save();
        $this->table('foo_methods')->drop()->save();
        $this->table('foo_recipes')->drop()->save();
        $this->table('foo_recipes_foo_tags')->drop()->save();
    }


    public function seed()
    {
        $rec = file_get_contents(CONFIG . "Migrations/recipes.json");
        $recs = json_decode($rec, true);

        $maxIngredient = 0;
        $maxMethod = 0;
        $maxDescription = 0;
        $maxName = 0;

        $insertAuthors = [];
        $authorsList = [];

        $insertRecipes = [];

        $insertAuthorsRecipes = [];

        $insertMethods = [];

        $insertIngredients = [];

        foreach ($recs as $r => $rec) {
            if (!isset($rec['Ingredients']) || !isset($rec['Method'])) {
                continue;
            }


            $currentDate = gmdate("Y-m-d H:i:s");

            $randomDate = mt_rand(0, 1662809270);
            $randomDate = date("Y-m-d H:i:s", $randomDate);


            //authors
            $author = $rec['Author'] ?? 'Unknown';
            if (!in_array($author, $authorsList)) {
                $insertAuthors[] = [
                    'created' => $currentDate,
                    'modified' => $currentDate,
                    'name' => $author,
                    'is_active' => mt_rand(0, 1),
                ];
                $authorsList[] = $author;
                $maxName = max($maxName, strlen($author));
            }
            $authorId = (array_search($author, $authorsList)) + 1;


            //recipes
            $insertRecipes[] = [
                'created' => $currentDate,
                'modified' => $currentDate,
                'name' => $rec['Name'],
                'description' => $rec['Description'],
                'publish_date' => $randomDate,
                'ingredient_count' => count($rec['Ingredients']),
                'method_count' => count($rec['Method']),
                'is_active' => mt_rand(0, 1),
                'meta' => '',
            ];
            if ($rec['Description']) {
                $maxDescription = max($maxDescription, strlen($rec['Description']));
            }
            $recipeId = $r + 1;

            //habtm table
            $insertAuthorsRecipes[] = [
                'foo_author_id' => $authorId,
                'foo_recipe_id' => $recipeId,
            ];


            //Ingredients
            $ingredients = $rec['Ingredients'];
            foreach ($ingredients as $i => $ingredient) {
                $maxIngredient = max($maxIngredient, strlen($ingredient));
                $insertIngredients[] = [
                    'foo_recipe_id' => $recipeId,
                    'created' => $currentDate,
                    'modified' => $currentDate,
                    'rank' => $i + 1,
                    'text' => $ingredient,
                ];
            }


            //Method
            $methods = $rec['Method'];
            foreach ($methods as $m => $method) {
                $maxMethod = max($maxMethod, strlen($method));
                $insertMethods[] = [
                    'foo_recipe_id' => $recipeId,
                    'created' => $currentDate,
                    'modified' => $currentDate,
                    'rank' => $m + 1,
                    'text' => $method,
                ];
            }


            //save every few loops to prevent SQL bound params limit
            if ($r % 10 === 0 || $r + 1 === count($recs)) {
                $table = $this->table('foo_authors');
                $table->insert($insertAuthors)->save();

                $table = $this->table('foo_recipes');
                $table->insert($insertRecipes)->save();

                $table = $this->table('foo_authors_foo_recipes');
                $table->insert($insertAuthorsRecipes)->save();

                $table = $this->table('foo_ingredients');
                $table->insert($insertIngredients)->save();

                $table = $this->table('foo_methods');
                $table->insert($insertMethods)->save();

                $insertAuthors = [];
                $insertRecipes = [];
                $insertAuthorsRecipes = [];
                $insertMethods = [];
                $insertIngredients = [];
            }


        }
    }
}
