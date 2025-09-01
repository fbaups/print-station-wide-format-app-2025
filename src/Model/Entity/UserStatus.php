<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * UserStatus Entity
 *
 * @property int $id
 * @property int|null $rank
 * @property DateTime $created
 * @property DateTime $modified
 * @property string|null $name
 * @property string|null $description
 * @property string|null $alias
 * @property string|null $name_status_icon
 */
class UserStatus extends Entity
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
        'rank' => true,
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'alias' => true,
        'name_status_icon' => true,
    ];
}
