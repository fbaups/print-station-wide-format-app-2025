<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * ApplicationLog Entity
 *
 * @property int $id
 * @property DateTime $created
 * @property DateTime $expiration
 * @property string|null $level
 * @property int|null $user_link
 * @property string|null $url
 * @property string|null $message
 * @property string|null $message_overflow
 */
class ApplicationLog extends Entity
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
        'expiration' => true,
        'level' => true,
        'user_link' => true,
        'url' => true,
        'message' => true,
        'message_overflow' => true,
    ];
}
