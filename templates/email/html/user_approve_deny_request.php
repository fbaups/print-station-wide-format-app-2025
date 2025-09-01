<?php
/**
 * @var \App\View\AppView $this
 * @var string $content
 * @var array $entities
 * @var string $url_approve
 * @var string $url_deny
 * @var string $url_edit
 */

/** @var \App\Model\Entity\User $user */
$user = $entities['user'];

$preHeaderText = __("Please approve/deny a new User request for {0} {1}.", $user->first_name, $user->last_name);
$this->set('preHeaderText', $preHeaderText);
?>
<tr>
    <td class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <p>Hi Administrator,</p>
                    <p><?= $user->first_name ?> <?= $user->last_name ?> has requested access to <?= APP_NAME ?>.
                        Please action this request by clicking on a link below.</p>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                        <tbody>
                        <tr>
                            <td align="left">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                    <tr>
                                        <td><a href="<?= $url_approve ?>" target="_blank">Approve Access</a></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary">
                        <tbody>
                        <tr>
                            <td align="left">
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                    <tr>
                                        <td><a href="<?= $url_deny ?>" target="_blank">Deny Access</a></td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p>If you approve their request, you can edit their details here: <?= $url_edit ?></p>
                </td>
            </tr>
        </table>
    </td>
</tr>
