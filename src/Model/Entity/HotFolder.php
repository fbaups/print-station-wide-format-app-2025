<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * HotFolder Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property string|null $path
 * @property bool|null $is_enabled
 * @property string|null $workflow
 * @property string|null $parameters
 * @property DateTime|null $next_polling_time
 * @property int|null $polling_interval
 * @property int|null $stable_interval
 * @property string|null $submit_url
 * @property bool|null $submit_url_enabled
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property bool|null $auto_clear_entries
 */
class HotFolder extends Entity
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
        'path' => true,
        'is_enabled' => true,
        'workflow' => true,
        'parameters' => true,
        'next_polling_time' => true,
        'polling_interval' => true,
        'stable_interval' => true,
        'submit_url' => true,
        'submit_url_enabled' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'auto_clear_entries' => true,
    ];
}
