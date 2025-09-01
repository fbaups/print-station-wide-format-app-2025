<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * FooRating Entity
 *
 * @property int $id
 * @property int|null $foo_recipe_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property int|null $score
 *
 * @property \App\Model\Entity\FooRecipe $foo_recipe
 */
class FooRating extends Entity
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
        'score' => true,
        'foo_recipe' => true,
    ];
}
