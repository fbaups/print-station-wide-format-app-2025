<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * DataBlob Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $activation
 * @property \Cake\I18n\DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property string|null $grouping
 * @property string|null $format
 * @property string|null $blob
 * @property string|null $hash_sum
 */
class DataBlob extends Entity
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
        'grouping' => true,
        'format' => true,
        'blob' => true,
        'hash_sum' => true,
    ];
}
