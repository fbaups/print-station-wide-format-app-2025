<?php
/**
 * @var AppView $this
 * @var Order $order
 */

use App\Model\Entity\Order;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Order'));
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
    <?= $this->AuthUser->link(__('&larr; Back to Orders'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($order->name) ?>
            <?= $this->AuthUser->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Order'), ['action' => 'edit', $order->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="orders view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Guid') ?></th>
                            <td><?= h($order->guid) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Order Status') ?></th>
                            <td><?= $order->has('order_status') ? $this->AuthUser->link($order->order_status->name, ['controller' => 'OrderStatuses', 'action' => 'view', $order->order_status->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($order->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($order->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('External Order Number') ?></th>
                            <td><?= h($order->external_order_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($order->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= $order->priority === null ? '' : $this->Number->format($order->priority) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($order->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($order->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('External Creation Date') ?></th>
                            <td><?= h($order->external_creation_date) ?></td>
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
            <?= __('Related Users') ?>
        </div>

        <div class="card-body">
            <div class="orders index content">
                <?php if (!empty($order->users)) : ?>
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
                            <?php foreach ($order->users as $users) : ?>
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
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'Users', 'action' => 'view', $users->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'Users', 'action' => 'edit', $users->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'Users', 'action' => 'delete', $users->id], ['confirm' => __('Are you sure you want to delete # {0}?', $users->id)]) ?>
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


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Jobs') ?>
        </div>

        <div class="card-body">
            <div class="orders index content">
                <?php if (!empty($order->jobs)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Guid') ?></th>
                                <th><?= __('Order Id') ?></th>
                                <th><?= __('Job Status Id') ?></th>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Description') ?></th>
                                <th><?= __('Quantity') ?></th>
                                <th><?= __('External Job Number') ?></th>
                                <th><?= __('External Creation Date') ?></th>
                                <th><?= __('Priority') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($order->jobs as $jobs) : ?>
                                <tr>
                                    <td><?= h($jobs->id) ?></td>
                                    <td><?= h($jobs->guid) ?></td>
                                    <td><?= h($jobs->order_id) ?></td>
                                    <td><?= h($jobs->job_status_id) ?></td>
                                    <td><?= h($jobs->name) ?></td>
                                    <td><?= h($jobs->description) ?></td>
                                    <td><?= h($jobs->quantity) ?></td>
                                    <td><?= h($jobs->external_job_number) ?></td>
                                    <td><?= h($jobs->external_creation_date) ?></td>
                                    <td><?= h($jobs->priority) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'Jobs', 'action' => 'view', $jobs->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'Jobs', 'action' => 'edit', $jobs->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'Jobs', 'action' => 'delete', $jobs->id], ['confirm' => __('Are you sure you want to delete # {0}?', $jobs->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Jobs found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Order Alerts') ?>
        </div>

        <div class="card-body">
            <div class="orders index content">
                <?php if (!empty($order->order_alerts)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Order Id') ?></th>
                                <th><?= __('Level') ?></th>
                                <th><?= __('Message') ?></th>
                                <th><?= __('Code') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($order->order_alerts as $orderAlerts) : ?>
                                <tr>
                                    <td><?= h($orderAlerts->id) ?></td>
                                    <td><?= h($orderAlerts->order_id) ?></td>
                                    <td><?= h($orderAlerts->level) ?></td>
                                    <td><?= h($orderAlerts->message) ?></td>
                                    <td><?= h($orderAlerts->code) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'OrderAlerts', 'action' => 'view', $orderAlerts->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'OrderAlerts', 'action' => 'edit', $orderAlerts->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'OrderAlerts', 'action' => 'delete', $orderAlerts->id], ['confirm' => __('Are you sure you want to delete # {0}?', $orderAlerts->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Order Alerts found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Order Properties') ?>
        </div>

        <div class="card-body">
            <div class="orders index content">
                <?php if (!empty($order->order_properties)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Order Id') ?></th>
                                <th><?= __('Meta Data') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($order->order_properties as $orderProperties) : ?>
                                <tr>
                                    <td><?= h($orderProperties->id) ?></td>
                                    <td><?= h($orderProperties->order_id) ?></td>
                                    <td><?= h($orderProperties->meta_data) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'OrderProperties', 'action' => 'view', $orderProperties->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'OrderProperties', 'action' => 'edit', $orderProperties->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'OrderProperties', 'action' => 'delete', $orderProperties->id], ['confirm' => __('Are you sure you want to delete # {0}?', $orderProperties->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Order Properties found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Order Status Movements') ?>
        </div>

        <div class="card-body">
            <div class="orders index content">
                <?php if (!empty($order->order_status_movements)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Order Id') ?></th>
                                <th><?= __('User Id') ?></th>
                                <th><?= __('Order Status From') ?></th>
                                <th><?= __('Order Status To') ?></th>
                                <th><?= __('Note') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($order->order_status_movements as $orderStatusMovements) : ?>
                                <tr>
                                    <td><?= h($orderStatusMovements->id) ?></td>
                                    <td><?= h($orderStatusMovements->order_id) ?></td>
                                    <td><?= h($orderStatusMovements->user_id) ?></td>
                                    <td><?= h($orderStatusMovements->order_status_from) ?></td>
                                    <td><?= h($orderStatusMovements->order_status_to) ?></td>
                                    <td><?= h($orderStatusMovements->note) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'OrderStatusMovements', 'action' => 'view', $orderStatusMovements->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'OrderStatusMovements', 'action' => 'edit', $orderStatusMovements->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'OrderStatusMovements', 'action' => 'delete', $orderStatusMovements->id], ['confirm' => __('Are you sure you want to delete # {0}?', $orderStatusMovements->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Order Status Movements found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



