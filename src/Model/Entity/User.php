<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Utility\Gravatar\Gravatar;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property DateTime $created
 * @property DateTime $modified
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $logger_name
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $suburb
 * @property string|null $state
 * @property string|null $post_code
 * @property string|null $country
 * @property string|null $mobile
 * @property string|null $phone
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property bool|null $is_confirmed
 * @property int|null $user_statuses_id
 * @property DateTime|null $password_expiry
 *
 * @property \App\Model\Entity\UserStatus $user_status
 * @property \App\Model\Entity\UserLocalization[] $user_localizations
 * @property \App\Model\Entity\Role[] $roles
 */
class User extends Entity
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
        'email' => true,
        'username' => true,
        'password' => true,
        'first_name' => true,
        'last_name' => true,
        'address_1' => true,
        'address_2' => true,
        'suburb' => true,
        'state' => true,
        'post_code' => true,
        'country' => true,
        'mobile' => true,
        'phone' => true,
        'activation' => true,
        'expiration' => true,
        'is_confirmed' => true,
        'user_statuses_id' => true,
        'password_expiry' => true,
        'user_status' => true,
        'user_localizations' => true,
        'roles' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
    ];

    /**
     * Automatically Hash Passwords
     *
     * @param $password
     * @return mixed
     */
    protected function _setPassword($password): mixed
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    /**
     * Get the Full Name of a User
     *
     * @return string
     */
    protected function _getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Get a Name that can be used in the Logs. Has the ID and full Name
     *
     * @return string
     */
    protected function _getLoggerName(): string
    {
        $fullName = trim($this->first_name . ' ' . $this->last_name);
        return "[{$this->id}:{$fullName}]";
    }

    public function toArray(): array
    {
        $fullName = $this->full_name;

        $Gravatar = new Gravatar();
        $gravatarUrl = $Gravatar->buildGravatarURL($this->email);

        $data = parent::toArray();
        $data['full_name'] = $fullName;
        $data['gravatar_url'] = $gravatarUrl;

        return $data;
    }


}
