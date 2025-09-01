<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * DocumentStatus Entity
 *
 * @property int $id
 * @property int|null $sort
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property string|null $allow_from_status
 * @property string|null $allow_to_status
 * @property string|null $icon
 * @property string|null $hex_code
 *
 * @property \App\Model\Entity\Document[] $documents
 */
class DocumentStatus extends Entity
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
        'sort' => true,
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'allow_from_status' => true,
        'allow_to_status' => true,
        'icon' => true,
        'hex_code' => true,
        'documents' => true,
    ];
}
