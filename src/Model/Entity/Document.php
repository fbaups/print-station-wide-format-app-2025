<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Document Entity
 *
 * @property int $id
 * @property string|null $guid
 * @property int $job_id
 * @property int $document_status_id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property int $quantity
 * @property string|null $artifact_token
 * @property string|null $external_document_number
 * @property DateTime|null $external_creation_date
 * @property string|null $external_url
 * @property string|null $payload
 * @property int|null $priority
 * @property string|null $hash_sum
 *
 * @property \App\Model\Entity\Job $job
 * @property \App\Model\Entity\DocumentStatus $document_status
 * @property \App\Model\Entity\DocumentAlert[] $document_alerts
 * @property \App\Model\Entity\DocumentProperty[] $document_properties
 * @property \App\Model\Entity\DocumentStatusMovement[] $document_status_movements
 * @property \App\Model\Entity\User[] $users
 */
class Document extends Entity
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
        'job_id' => true,
        'document_status_id' => true,
        'created' => true,
        'modified' => true,
        'name' => true,
        'description' => true,
        'quantity' => true,
        'artifact_token' => true,
        'external_document_number' => true,
        'external_creation_date' => true,
        'external_url' => true,
        'payload' => true,
        'priority' => true,
        'hash_sum' => true,
        'job' => true,
        'document_status' => true,
        'document_alerts' => true,
        'document_properties' => true,
        'document_status_movements' => true,
        'users' => true,
    ];
}
