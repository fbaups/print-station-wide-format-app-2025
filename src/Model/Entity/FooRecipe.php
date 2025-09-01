<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * FooRecipe Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property DateTime|null $publish_date
 * @property int|null $ingredient_count
 * @property int|null $method_count
 * @property bool|null $is_active
 * @property string|null $meta
 *
 * @property \App\Model\Entity\FooIngredient[] $foo_ingredients
 * @property \App\Model\Entity\FooMethod[] $foo_methods
 * @property \App\Model\Entity\FooRating[] $foo_ratings
 * @property \App\Model\Entity\FooAuthor[] $foo_authors
 * @property \App\Model\Entity\FooTag[] $foo_tags
 */
class FooRecipe extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'publish_date' => true,
        'ingredient_count' => true,
        'method_count' => true,
        'is_active' => true,
        'meta' => true,
        'foo_ingredients' => true,
        'foo_methods' => true,
        'foo_ratings' => true,
        'foo_authors' => true,
        'foo_tags' => true,
    ];
}
