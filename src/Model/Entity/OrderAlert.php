<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * OrderAlert Entity
 *
 * @property int $id
 * @property int $order_id
 * @property DateTime $created
 * @property string|null $level
 * @property string|null $message
 * @property int|null $code
 *
 * @property \App\Model\Entity\Order $order
 */
class OrderAlert extends Entity
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
        'order_id' => true,
        'created' => true,
        'level' => true,
        'message' => true,
        'code' => true,
        'order' => true,
    ];
}
