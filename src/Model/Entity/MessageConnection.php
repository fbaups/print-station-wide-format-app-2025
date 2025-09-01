<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * MessageConnection Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property int|null $message_link
 * @property string|null $direction
 * @property int|null $user_link
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Message $message
 */
class MessageConnection extends Entity
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
        'message_link' => true,
        'direction' => true,
        'user_link' => true,
        'user' => true,
        'message' => true,
    ];
}
