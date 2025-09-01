<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Article Entity
 *
 * @property int $id
 * @property int|null $article_status_id
 * @property int|null $user_link
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime|null $activation
 * @property \Cake\I18n\DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property string|null $title
 * @property string|null $body
 * @property int|null $priority
 *
 * @property \App\Model\Entity\ArticleStatus $article_status
 * @property \App\Model\Entity\Role[] $roles
 * @property \App\Model\Entity\User[] $users
 */
class Article extends Entity
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
        'article_status_id' => true,
        'user_link' => true,
        'created' => true,
        'modified' => true,
        'activation' => true,
        'expiration' => true,
        'auto_delete' => true,
        'title' => true,
        'body' => true,
        'priority' => true,
        'article_status' => true,
        'roles' => true,
        'users' => true,
    ];
}
