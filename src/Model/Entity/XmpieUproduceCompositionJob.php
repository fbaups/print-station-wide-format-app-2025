<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * XmpieUproduceCompositionJob Entity
 *
 * @property int $id
 * @property int $xmpie_uproduce_composition_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property int|null $job_number
 *
 * @property XmpieUproduceComposition $xmpie_uproduce_composition
 * @property XmpieUproduceCompositionJobCallback[] $xmpie_uproduce_composition_job_callbacks
 */
class XmpieUproduceCompositionJob extends Entity
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
        'xmpie_uproduce_composition_id' => true,
        'created' => true,
        'modified' => true,
        'job_number' => true,
        'xmpie_uproduce_composition' => true,
        'xmpie_uproduce_composition_job_callbacks' => true,
    ];
}
