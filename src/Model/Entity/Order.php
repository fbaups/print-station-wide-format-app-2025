<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Order Entity
 *
 * @property int $id
 * @property string|null $guid
 * @property int $order_status_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property int $quantity
 * @property string|null $external_system_type
 * @property string|null $external_order_number
 * @property DateTime|null $external_creation_date
 * @property string|null $payload
 * @property int|null $priority
 * @property string|null $hash_sum
 *
 * @property \App\Model\Entity\OrderStatus $order_status
 * @property \App\Model\Entity\Job[] $jobs
 * @property \App\Model\Entity\OrderAlert[] $order_alerts
 * @property \App\Model\Entity\OrderProperty[] $order_properties
 * @property \App\Model\Entity\OrderStatusMovement[] $order_status_movements
 * @property \App\Model\Entity\User[] $users
 */
class Order extends Entity
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
        'order_status_id' => true,
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'quantity' => true,
        'external_system_type' => true,
        'external_order_number' => true,
        'external_creation_date' => true,
        'payload' => true,
        'priority' => true,
        'hash_sum' => true,
        'order_status' => true,
        'jobs' => true,
        'order_alerts' => true,
        'order_properties' => true,
        'order_status_movements' => true,
        'users' => true,
    ];
}
