<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * JobsUser Entity
 *
 * @property int $id
 * @property int $job_id
 * @property int $user_id
 *
 * @property \App\Model\Entity\Job $job
 * @property \App\Model\Entity\User $user
 */
class JobsUser extends Entity
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
        'user_id' => true,
        'job' => true,
        'user' => true,
    ];
}
