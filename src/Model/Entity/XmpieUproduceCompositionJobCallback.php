<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * XmpieUproduceCompositionJobCallback Entity
 *
 * @property int $id
 * @property int $xmpie_uproduce_composition_job_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property int|null $job_number
 * @property string|null $job_guid
 * @property string|null $status
 * @property DateTime|null $start
 * @property DateTime|null $end
 * @property int|null $process_count
 *
 * @property XmpieUproduceCompositionJob $xmpie_uproduce_composition_job
 */
class XmpieUproduceCompositionJobCallback extends Entity
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
        'xmpie_uproduce_composition_job_id' => true,
        'created' => true,
        'modified' => true,
        'name' => true,
        'job_number' => true,
        'job_guid' => true,
        'status' => true,
        'start' => true,
        'end' => true,
        'process_count' => true,
        'xmpie_uproduce_composition_job' => true,
    ];
}
