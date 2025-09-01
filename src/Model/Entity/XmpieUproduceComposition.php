<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * XmpieUproduceComposition Entity
 *
 * @property int $id
 * @property string|null $guid
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property string|null $name
 * @property string|null $description
 * @property int|null $errand_link
 * @property int|null $artifact_link
 * @property int|null $integration_credential_link
 *
 * @property XmpieUproduceCompositionJob[] $xmpie_uproduce_composition_jobs
 */
class XmpieUproduceComposition extends Entity
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
        'guid' => true,
        'created' => true,
        'modified' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'name' => true,
        'description' => true,
        'errand_link' => true,
        'artifact_link' => true,
        'integration_credential_link' => true,
        'xmpie_uproduce_composition_jobs' => true,
    ];
}
