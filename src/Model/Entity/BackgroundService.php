<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * BackgroundService Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $server
 * @property string|null $domain
 * @property string|null $name
 * @property string|null $type
 * @property int|null $pid
 * @property string|null $current_state
 * @property DateTime|null $appointment_date
 * @property DateTime|null $retirement_date
 * @property DateTime|null $termination_date
 * @property bool|null $force_recycle
 * @property bool|null $force_shutdown
 * @property int|null $errand_link
 * @property string|null $errand_name
 */
class BackgroundService extends Entity
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
        'server' => true,
        'domain' => true,
        'name' => true,
        'type' => true,
        'pid' => true,
        'current_state' => true,
        'appointment_date' => true,
        'retirement_date' => true,
        'termination_date' => true,
        'force_recycle' => true,
        'force_shutdown' => true,
        'errand_link' => true,
        'errand_name' => true,
    ];
}
