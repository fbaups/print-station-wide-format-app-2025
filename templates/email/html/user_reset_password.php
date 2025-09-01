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

$preHeaderText = __("Hello {0}. Please reset your password.", $user->first_name);
$this->set('preHeaderText', $preHeaderText);
?>
<tr>
    <td class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <p>Hi <?= $user->first_name ?>,</p>
                    <p>Welcome to <?= APP_NAME ?>.
                        Please reset your password by clicking on the link below.</p>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                        <tbody>
                        <tr>
                            <td align="left">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                    <tr>
                                        <td><a href="<?= $url ?>" target="_blank">Reset Your Password</a></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p>If there are any issues, please contact the <?= APP_NAME ?> Administrator.</p>
                </td>
            </tr>
        </table>
    </td>
</tr>
