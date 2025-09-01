<?php
/**
 * @var AppView $this
 * @var Subscription $subscription
 */

use App\Model\Entity\Subscription;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Subscription'));
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
    <?= $this->Html->link(__('&larr; Back to Subscriptions'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($subscription->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Subscription'), ['action' => 'edit', $subscription->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="subscriptions view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($subscription->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($subscription->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($subscription->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= $subscription->priority === null ? '' : $this->Number->format($subscription->priority) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($subscription->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($subscription->modified) ?></td>
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
            <div class="subscriptions index content">
                <?php if (!empty($subscription->roles)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Description') ?></th>
                                <th><?= __('Alias') ?></th>
                                <th><?= __('Session Timeout') ?></th>
                                <th><?= __('Grouping') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($subscription->roles as $roles) : ?>
                                <tr>
                                    <td><?= h($roles->id) ?></td>
                                    <td><?= h($roles->name) ?></td>
                                    <td><?= h($roles->description) ?></td>
                                    <td><?= h($roles->alias) ?></td>
                                    <td><?= h($roles->session_timeout) ?></td>
                                    <td><?= h($roles->grouping) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'Roles', 'action' => 'view', $roles->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'Roles', 'action' => 'edit', $roles->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'Roles', 'action' => 'delete', $roles->id], ['confirm' => __('Are you sure you want to delete # {0}?', $roles->id)]) ?>
                                    </td>
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
            <?= __('Related Users') ?>
        </div>

        <div class="card-body">
            <div class="subscriptions index content">
                <?php if (!empty($subscription->users)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Email') ?></th>
                                <th><?= __('Username') ?></th>
                                <th><?= __('Password') ?></th>
                                <th><?= __('First Name') ?></th>
                                <th><?= __('Last Name') ?></th>
                                <th><?= __('Address 1') ?></th>
                                <th><?= __('Address 2') ?></th>
                                <th><?= __('Suburb') ?></th>
                                <th><?= __('State') ?></th>
                                <th><?= __('Post Code') ?></th>
                                <th><?= __('Country') ?></th>
                                <th><?= __('Mobile') ?></th>
                                <th><?= __('Phone') ?></th>
                                <th><?= __('Activation') ?></th>
                                <th><?= __('Expiration') ?></th>
                                <th><?= __('Is Confirmed') ?></th>
                                <th><?= __('User Statuses Id') ?></th>
                                <th><?= __('Password Expiry') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($subscription->users as $users) : ?>
                                <tr>
                                    <td><?= h($users->id) ?></td>
                                    <td><?= h($users->email) ?></td>
                                    <td><?= h($users->username) ?></td>
                                    <td><?= h($users->password) ?></td>
                                    <td><?= h($users->first_name) ?></td>
                                    <td><?= h($users->last_name) ?></td>
                                    <td><?= h($users->address_1) ?></td>
                                    <td><?= h($users->address_2) ?></td>
                                    <td><?= h($users->suburb) ?></td>
                                    <td><?= h($users->state) ?></td>
                                    <td><?= h($users->post_code) ?></td>
                                    <td><?= h($users->country) ?></td>
                                    <td><?= h($users->mobile) ?></td>
                                    <td><?= h($users->phone) ?></td>
                                    <td><?= h($users->activation) ?></td>
                                    <td><?= h($users->expiration) ?></td>
                                    <td><?= h($users->is_confirmed) ?></td>
                                    <td><?= h($users->user_statuses_id) ?></td>
                                    <td><?= h($users->password_expiry) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'Users', 'action' => 'view', $users->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'Users', 'action' => 'edit', $users->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'Users', 'action' => 'delete', $users->id], ['confirm' => __('Are you sure you want to delete # {0}?', $users->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Users found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



