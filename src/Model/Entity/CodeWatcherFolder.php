<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CodeWatcherFolder Entity
 *
 * @property int $id
 * @property int|null $code_watcher_project_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $activation
 * @property \Cake\I18n\DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property string|null $base_path
 *
 * @property \App\Model\Entity\CodeWatcherProject $code_watcher_project
 * @property \App\Model\Entity\CodeWatcherFile[] $code_watcher_files
 */
class CodeWatcherFolder extends Entity
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
        'code_watcher_project_id' => true,
        'created' => true,
        'modified' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'base_path' => true,
        'code_watcher_project' => true,
        'code_watcher_files' => true,
    ];
}
