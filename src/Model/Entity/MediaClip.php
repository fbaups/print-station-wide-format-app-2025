<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Table\MediaClipsTable;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * MediaClip Entity
 *
 * @property int $id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property string|null $name
 * @property string|null $description
 * @property int|null $rank
 * @property int|null $artifact_link
 * @property \Cake\I18n\DateTime|null $activation
 * @property \Cake\I18n\DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property float|null $trim_start
 * @property float|null $trim_end
 * @property float|null $duration
 * @property string|null $fitting
 * @property bool|null $muted
 * @property bool|null $loop
 * @property bool|null $autoplay
 *
 * @property \App\Model\Entity\Artifact $artifact
 */
class MediaClip extends Entity
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
        'rank' => true,
        'artifact_link' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'trim_start' => true,
        'trim_end' => true,
        'duration' => true,
        'fitting' => true,
        'muted' => true,
        'loop' => true,
        'autoplay' => true,
        'artifact' => true,
    ];

    /**
     * Get the Artifact behind the MediaClip URL
     *
     * ***WARNING***
     * Calling this in a loop is expensive as it hits the DB every time.
     * Better to compile and Artifacts[] if needed for listings.
     *
     * @return string
     */
    protected function _getArtifact()
    {
        /** @var MediaClipsTable $MedaClipsTable */
        $MedaClipsTable = TableRegistry::getTableLocator()->get('MediaClips');

        return $MedaClipsTable->getArtifactBehindMediaClip($this);
    }
}
