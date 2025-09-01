<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * FooIngredient Entity
 *
 * @property int $id
 * @property int|null $foo_recipe_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property int|null $rank
 * @property string|null $text
 *
 * @property \App\Model\Entity\FooRecipe $foo_recipe
 */
class FooIngredient extends Entity
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
        'foo_recipe_id' => true,
        'created' => true,
        'modified' => true,
        'rank' => true,
        'text' => true,
        'foo_recipe' => true,
    ];
}
