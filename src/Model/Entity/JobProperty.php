<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * JobProperty Entity
 *
 * @property int $id
 * @property int $job_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $meta_data
 *
 * @property \App\Model\Entity\Job $job
 */
class JobProperty extends Entity
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
        'modified' => true,
        'meta_data' => true,
        'job' => true,
    ];
}
