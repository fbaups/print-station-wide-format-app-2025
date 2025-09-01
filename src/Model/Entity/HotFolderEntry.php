<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * HotFolderEntry Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property int $hot_folder_id
 * @property string|null $path
 * @property string|null $path_hash_sum
 * @property string|null $listing_hash_sum
 * @property string|null $contents_hash_sum
 * @property DateTime|null $last_check_time
 * @property DateTime|null $next_check_time
 * @property int|null $lock_code
 * @property int|null $errand_link
 * @property string|null $status
 *
 * @property \App\Model\Entity\HotFolder $hot_folder
 */
class HotFolderEntry extends Entity
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
        'hot_folder_id' => true,
        'path' => true,
        'path_hash_sum' => true,
        'listing_hash_sum' => true,
        'contents_hash_sum' => true,
        'last_check_time' => true,
        'next_check_time' => true,
        'lock_code' => true,
        'errand_link' => true,
        'status' => true,
        'hot_folder' => true,
    ];
}
