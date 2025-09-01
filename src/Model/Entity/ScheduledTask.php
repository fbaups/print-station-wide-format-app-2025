<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * ScheduledTask Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property string|null $name
 * @property string|null $description
 * @property bool|null $is_enabled
 * @property string|null $schedule
 * @property string|null $workflow
 * @property string|null $parameters
 * @property DateTime|null $next_run_time
 */
class ScheduledTask extends Entity
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
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'name' => true,
        'description' => true,
        'is_enabled' => true,
        'schedule' => true,
        'workflow' => true,
        'parameters' => true,
        'next_run_time' => true,
    ];
}
