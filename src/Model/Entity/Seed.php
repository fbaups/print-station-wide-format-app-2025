<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;
use Cake\Routing\Router;

/**
 * Seed Entity
 *
 * @property int $id
 * @property DateTime $created
 * @property DateTime $modified
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property string|null $token
 * @property string|null $url
 * @property int|null $bids
 * @property int|null $bid_limit
 * @property int|null $user_link
 */
class Seed extends Entity
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
        'activation' => true,
        'expiration' => true,
        'token' => true,
        'url' => true,
        'bids' => true,
        'bid_limit' => true,
        'user_link' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'token',
    ];

    /**
     * Get the Full URL
     *
     * @return string
     */
    protected function _getFullUrl(): string
    {
        $base = trim(Router::url("/", true), "/");
        $str = $base . $this->url;
        return trim($str);
    }

    /**
     * How long before the Entity expires
     *
     * @return int
     */
    public function getTTL(): int
    {
        $currentTime = new DateTime();

        if ($this->expiration->greaterThanOrEquals($currentTime)) {
            return $this->expiration->diffInSeconds($currentTime);
        }

        return 0;
    }
}
