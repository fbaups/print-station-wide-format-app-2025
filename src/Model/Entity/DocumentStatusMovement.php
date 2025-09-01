<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * DocumentStatusMovement Entity
 *
 * @property int $id
 * @property int|null $document_id
 * @property DateTime|null $created
 * @property int|null $user_id
 * @property int|null $document_status_from
 * @property int|null $document_status_to
 * @property string|null $note
 *
 * @property \App\Model\Entity\Document $document
 * @property \App\Model\Entity\User $user
 */
class DocumentStatusMovement extends Entity
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
        'document_id' => true,
        'created' => true,
        'user_id' => true,
        'document_status_from' => true,
        'document_status_to' => true,
        'note' => true,
        'document' => true,
        'user' => true,
    ];
}
