<?php
/**
 * @var \App\View\AppView $this
 * @var string $content
 * @var array $entities
 * @var string $url
 */

/** @var \App\Model\Entity\User $user */
$user = $entities['user'];

$preHeaderText = __("Hello {0}. Please accept your invitation.", $user->first_name);
$this->set('preHeaderText', $preHeaderText);
?>
<tr>
    <td class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <p>Hi <?= $user->first_name ?>,</p>
                    <p>Welcome to <?= APP_NAME ?>.
                        Please accept your invitation by clicking on the link below.</p>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                        <tbody>
                        <tr>
                            <td align="left">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                    <tr>
                                        <td><a href="<?= $url ?>" target="_blank">Accept Your Invitation</a></td>
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
