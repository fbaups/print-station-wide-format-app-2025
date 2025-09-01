<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CodeWatcherFile Entity
 *
 * @property int $id
 * @property int|null $code_watcher_folder_id
 * @property \Cake\I18n\DateTime|null $created
 * @property string|null $local_timezone
 * @property int|null $local_year
 * @property int|null $local_month
 * @property int|null $local_day
 * @property int|null $local_hour
 * @property int|null $local_minute
 * @property int|null $local_second
 * @property string|null $time_grouping_key
 * @property string|null $path_checksum
 * @property string|null $base_path
 * @property string|null $file_path
 * @property string|null $sha1
 * @property string|null $crc32
 * @property string|null $mime
 * @property int|null $size
 *
 * @property \App\Model\Entity\CodeWatcherFolder $code_watcher_folder
 */
class CodeWatcherFile extends Entity
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
        'code_watcher_folder_id' => true,
        'created' => true,
        'local_timezone' => true,
        'local_year' => true,
        'local_month' => true,
        'local_day' => true,
        'local_hour' => true,
        'local_minute' => true,
        'local_second' => true,
        'time_grouping_key' => true,
        'path_checksum' => true,
        'base_path' => true,
        'file_path' => true,
        'sha1' => true,
        'crc32' => true,
        'mime' => true,
        'size' => true,
        'code_watcher_folder' => true,
    ];
}
