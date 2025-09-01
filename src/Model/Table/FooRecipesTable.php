<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Database\Schema\TableSchema;
use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * FooRecipes Model
 *
 * @property \App\Model\Table\FooIngredientsTable&\Cake\ORM\Association\HasMany $FooIngredients
 * @property \App\Model\Table\FooMethodsTable&\Cake\ORM\Association\HasMany $FooMethods
 * @property \App\Model\Table\FooRatingsTable&\Cake\ORM\Association\HasMany $FooRatings
 * @property \App\Model\Table\FooAuthorsTable&\Cake\ORM\Association\BelongsToMany $FooAuthors
 * @property \App\Model\Table\FooTagsTable&\Cake\ORM\Association\BelongsToMany $FooTags
 *
 * @method \App\Model\Entity\FooRecipe newEmptyEntity()
 * @method \App\Model\Entity\FooRecipe newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FooRecipe[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FooRecipe get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\FooRecipe findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FooRecipe patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FooRecipe[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FooRecipe|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FooRecipe saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FooRecipe[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FooRecipe[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FooRecipe[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FooRecipe[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FooRecipesTable extends AppTable
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('foo_recipes');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('FooIngredients', [
            'foreignKey' => 'foo_recipe_id',
        ]);
        $this->hasMany('FooMethods', [
            'foreignKey' => 'foo_recipe_id',
        ]);
        $this->hasMany('FooRatings', [
            'foreignKey' => 'foo_recipe_id',
        ]);
        $this->belongsToMany('FooAuthors', [
            'foreignKey' => 'foo_recipe_id',
            'targetForeignKey' => 'foo_author_id',
            'joinTable' => 'foo_authors_foo_recipes',
        ]);
        $this->belongsToMany('FooTags', [
            'foreignKey' => 'foo_recipe_id',
            'targetForeignKey' => 'foo_tag_id',
            'joinTable' => 'foo_recipes_foo_tags',
        ]);

        $this->initializeSchemaJsonFields($this->getJsonFields());
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 256)
            ->allowEmptyString('name');

        $validator
            ->scalar('description')
            ->maxLength('description', 1024)
            ->allowEmptyString('description');

        $validator
            ->dateTime('publish_date')
            ->allowEmptyDateTime('publish_date');

        $validator
            ->integer('ingredient_count')
            ->allowEmptyString('ingredient_count');

        $validator
            ->integer('method_count')
            ->allowEmptyString('method_count');

        $validator
            ->boolean('is_active')
            ->allowEmptyString('is_active');

        $validator
            ->scalar('meta')
            ->maxLength('meta', 2048)
            ->allowEmptyString('meta');

        return $validator;
    }

    /**
     * List of properties that can be JSON encoded
     *
     * @return array
     */
    public function getJsonFields(): array
    {
        $jsonFields = [];

        return $jsonFields;
    }
}
