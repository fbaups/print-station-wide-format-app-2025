<?php
/**
 * @var AppView $this
 * @var User $user
 */

use App\Model\Entity\User;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __("person-fill"));
$this->set('headerTitle', __('View User'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);

?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Users'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($user->id) ?> - <?= h($user->first_name) ?> <?= h($user->last_name) ?>
        </div>

        <div class="card-body">
            <div class="users view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Email') ?></th>
                            <td><?= h($user->email) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Username') ?></th>
                            <td><?= h($user->username) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('First Name') ?></th>
                            <td><?= h($user->first_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Last Name') ?></th>
                            <td><?= h($user->last_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Address 1') ?></th>
                            <td><?= h($user->address_1) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Address 2') ?></th>
                            <td><?= h($user->address_2) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Suburb') ?></th>
                            <td><?= h($user->suburb) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('State') ?></th>
                            <td><?= h($user->state) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Post Code') ?></th>
                            <td><?= h($user->post_code) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Country') ?></th>
                            <td><?= h($user->country) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Mobile') ?></th>
                            <td><?= h($user->mobile) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Phone') ?></th>
                            <td><?= h($user->phone) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User Status') ?></th>
                            <td><?= $user->has('user_status') ? $this->Html->link($user->user_status->name, ['controller' => 'UserStatuses', 'action' => 'view', $user->user_status->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($user->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($user->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($user->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($user->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($user->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Password Expiry') ?></th>
                            <td><?= h($user->password_expiry) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Confirmed') ?></th>
                            <td><?= $user->is_confirmed ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Roles') ?>
        </div>

        <div class="card-body">
            <div class="users index content">
                <?php if (!empty($user->roles)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Description') ?></th>
                                <th><?= __('Alias') ?></th>
                                <th><?= __('Session Timeout') ?></th>
                            </tr>
                            <?php foreach ($user->roles as $roles) : ?>
                                <tr>
                                    <td><?= h($roles->id) ?></td>
                                    <td><?= h($roles->name) ?></td>
                                    <td><?= h($roles->description) ?></td>
                                    <td><?= h($roles->alias) ?></td>
                                    <td><?= h($roles->session_timeout) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Roles found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related User Localisations') ?>
        </div>

        <div class="card-body">
            <div class="users index content">
                <?php if (!empty($user->user_localizations)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('User Id') ?></th>
                                <th><?= __('Location') ?></th>
                                <th><?= __('Locale') ?></th>
                                <th><?= __('Timezone') ?></th>
                                <th><?= __('Time Format') ?></th>
                                <th><?= __('Date Format') ?></th>
                                <th><?= __('Datetime Format') ?></th>
                                <th><?= __('Week Start') ?></th>
                            </tr>
                            <?php foreach ($user->user_localizations as $userLocalizations) : ?>
                                <tr>
                                    <td><?= h($userLocalizations->id) ?></td>
                                    <td><?= h($userLocalizations->user_id) ?></td>
                                    <td><?= h($userLocalizations->location) ?></td>
                                    <td><?= h($userLocalizations->locale) ?></td>
                                    <td><?= h($userLocalizations->timezone) ?></td>
                                    <td><?= h($userLocalizations->time_format) ?></td>
                                    <td><?= h($userLocalizations->date_format) ?></td>
                                    <td><?= h($userLocalizations->datetime_format) ?></td>
                                    <td><?= h($userLocalizations->week_start) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no User Localisations found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



