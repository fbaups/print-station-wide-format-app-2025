<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\I18n\DateTime;
use Cake\ORM\Entity;

/**
 * Errand Entity
 *
 * @property int $id
 * @property DateTime|null $created
 * @property DateTime|null $modified
 * @property DateTime|null $activation
 * @property DateTime|null $expiration
 * @property bool|null $auto_delete
 * @property int|null $wait_for_link
 * @property string|null $server
 * @property string|null $domain
 * @property string|null $name
 * @property int|null $background_service_link
 * @property string|null $background_service_name
 * @property string|null $class
 * @property string|null $method
 * @property array|null $parameters
 * @property string|null $status
 * @property DateTime|null $started
 * @property DateTime|null $completed
 * @property int|null $progress_bar
 * @property int|null $priority
 * @property int|null $return_value
 * @property array|null $return_message
 * @property array|null $errors_thrown
 * @property int|null $errors_retry
 * @property int|null $errors_retry_limit
 * @property string|null $hash_sum
 * @property int|null $lock_to_thread
 * @property string|null $grouping
 */
class Errand extends Entity
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
        'auto_delete' => true,
        'wait_for_link' => true,
        'server' => true,
        'domain' => true,
        'name' => true,
        'background_service_link' => true,
        'background_service_name' => true,
        'class' => true,
        'method' => true,
        'parameters' => true,
        'status' => true,
        'started' => true,
        'completed' => true,
        'progress_bar' => true,
        'priority' => true,
        'return_value' => true,
        'return_message' => true,
        'errors_thrown' => true,
        'errors_retry' => true,
        'errors_retry_limit' => true,
        'hash_sum' => true,
        'lock_to_thread' => true,
        'grouping' => true,
    ];
}
