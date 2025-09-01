<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Subscription Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property int|null $priority
 *
 * @property \App\Model\Entity\Role[] $roles
 * @property \App\Model\Entity\User[] $users
 */
class Subscription extends Entity
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
        'priority' => true,
        'roles' => true,
        'users' => true,
    ];
}
