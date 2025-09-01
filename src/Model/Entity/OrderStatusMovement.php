<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * OrderStatusMovement Entity
 *
 * @property int $id
 * @property int|null $order_id
 * @property DateTime|null $created
 * @property int|null $user_id
 * @property int|null $order_status_from
 * @property int|null $order_status_to
 * @property string|null $note
 *
 * @property \App\Model\Entity\Order $order
 * @property \App\Model\Entity\User $user
 */
class OrderStatusMovement extends Entity
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
        'user_id' => true,
        'order_status_from' => true,
        'order_status_to' => true,
        'note' => true,
        'order' => true,
        'user' => true,
    ];
}
