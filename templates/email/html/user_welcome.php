<?php
/**
 * @var AppView $this
 * @var string $content
 * @var array $entities
 * @var string $url
 */

/** @var User $user */

use App\Model\Entity\User;
use App\View\AppView;

$user = $entities['user'];

$preHeaderText = __("Hello {0}. Please confirm your email address.", $user->first_name);
$this->set('preHeaderText', $preHeaderText);
?>
<tr>
    <td class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <p>Hi <?= $user->first_name ?>,</p>
                    <p>Welcome to <?= APP_NAME ?>. .</p>
                    <p>We hope you enjoy using the Web Dashboard and the following awesome features:</p>
                    <ul>
                        <li>User account management.</li>
                        <li>Hot Folders.</li>
                    </ul>
                    <p>If there are any issues, please contact the <?= APP_NAME ?> Administrator.</p>
                </td>
            </tr>
        </table>
    </td>
</tr>
