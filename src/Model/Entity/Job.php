<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Job Entity
 *
 * @property int $id
 * @property string|null $guid
 * @property int $order_id
 * @property int $job_status_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property int $quantity
 * @property string|null $external_job_number
 * @property DateTime|null $external_creation_date
 * @property string|null $payload
 * @property int|null $priority
 * @property string|null $hash_sum
 *
 * @property \App\Model\Entity\Order $order
 * @property \App\Model\Entity\JobStatus $job_status
 * @property \App\Model\Entity\Document[] $documents
 * @property \App\Model\Entity\JobAlert[] $job_alerts
 * @property \App\Model\Entity\JobProperty[] $job_properties
 * @property \App\Model\Entity\JobStatusMovement[] $job_status_movements
 * @property \App\Model\Entity\User[] $users
 */
class Job extends Entity
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
        'order_id' => true,
        'job_status_id' => true,
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'quantity' => true,
        'external_job_number' => true,
        'external_creation_date' => true,
        'payload' => true,
        'priority' => true,
        'hash_sum' => true,
        'order' => true,
        'job_status' => true,
        'documents' => true,
        'job_alerts' => true,
        'job_properties' => true,
        'job_status_movements' => true,
        'users' => true,
    ];
}
