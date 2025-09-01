<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * JobStatusMovement Entity
 *
 * @property int $id
 * @property int|null $job_id
 * @property DateTime|null $created
 * @property int|null $user_id
 * @property int|null $job_status_from
 * @property int|null $job_status_to
 * @property string|null $note
 *
 * @property \App\Model\Entity\Job $job
 * @property \App\Model\Entity\User $user
 */
class JobStatusMovement extends Entity
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
        'job_id' => true,
        'created' => true,
        'user_id' => true,
        'job_status_from' => true,
        'job_status_to' => true,
        'note' => true,
        'job' => true,
        'user' => true,
    ];
}
