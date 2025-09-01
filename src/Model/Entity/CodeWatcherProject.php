<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CodeWatcherProject Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property \Cake\I18n\DateTime|null $activation
 * @property \Cake\I18n\DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property bool|null $enable_tracking
 *
 * @property \App\Model\Entity\CodeWatcherFolder[] $code_watcher_folders
 */
class CodeWatcherProject extends Entity
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
        'name' => true,
        'description' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'enable_tracking' => true,
        'code_watcher_folders' => true,
    ];
}
