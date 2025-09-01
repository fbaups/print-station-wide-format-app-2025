<?php
/**
 * @var AppView $this
 * @var Job $job
 */

use App\Model\Entity\Job;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Job'));
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
    <?= $this->AuthUser->link(__('&larr; Back to Jobs'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($job->name) ?>
            <?= $this->AuthUser->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Job'), ['action' => 'edit', $job->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="jobs view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Guid') ?></th>
                            <td><?= h($job->guid) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Order') ?></th>
                            <td><?= $job->has('order') ? $this->AuthUser->link($job->order->name, ['controller' => 'Orders', 'action' => 'view', $job->order->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Job Status') ?></th>
                            <td><?= $job->has('job_status') ? $this->AuthUser->link($job->job_status->name, ['controller' => 'JobStatuses', 'action' => 'view', $job->job_status->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($job->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($job->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('External Job Number') ?></th>
                            <td><?= h($job->external_job_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($job->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Quantity') ?></th>
                            <td><?= $this->Number->format($job->quantity) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= $job->priority === null ? '' : $this->Number->format($job->priority) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($job->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($job->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('External Creation Date') ?></th>
                            <td><?= h($job->external_creation_date) ?></td>
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
            <div class="jobs index content">
                <?php if (!empty($job->users)) : ?>
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
                            <?php foreach ($job->users as $users) : ?>
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
            <?= __('Related Documents') ?>
        </div>

        <div class="card-body">
            <div class="jobs index content">
                <?php if (!empty($job->documents)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Guid') ?></th>
                                <th><?= __('Job Id') ?></th>
                                <th><?= __('Document Status Id') ?></th>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Description') ?></th>
                                <th><?= __('Artifact Token') ?></th>
                                <th><?= __('External Document Number') ?></th>
                                <th><?= __('External Creation Date') ?></th>
                                <th><?= __('Priority') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($job->documents as $documents) : ?>
                                <tr>
                                    <td><?= h($documents->id) ?></td>
                                    <td><?= h($documents->guid) ?></td>
                                    <td><?= h($documents->job_id) ?></td>
                                    <td><?= h($documents->document_status_id) ?></td>
                                    <td><?= h($documents->name) ?></td>
                                    <td><?= h($documents->description) ?></td>
                                    <td><?= h($documents->artifact_token) ?></td>
                                    <td><?= h($documents->external_document_number) ?></td>
                                    <td><?= h($documents->external_creation_date) ?></td>
                                    <td><?= h($documents->priority) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'Documents', 'action' => 'view', $documents->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'Documents', 'action' => 'edit', $documents->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'Documents', 'action' => 'delete', $documents->id], ['confirm' => __('Are you sure you want to delete # {0}?', $documents->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Documents found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Job Alerts') ?>
        </div>

        <div class="card-body">
            <div class="jobs index content">
                <?php if (!empty($job->job_alerts)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Job Id') ?></th>
                                <th><?= __('Level') ?></th>
                                <th><?= __('Message') ?></th>
                                <th><?= __('Code') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($job->job_alerts as $jobAlerts) : ?>
                                <tr>
                                    <td><?= h($jobAlerts->id) ?></td>
                                    <td><?= h($jobAlerts->job_id) ?></td>
                                    <td><?= h($jobAlerts->level) ?></td>
                                    <td><?= h($jobAlerts->message) ?></td>
                                    <td><?= h($jobAlerts->code) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'JobAlerts', 'action' => 'view', $jobAlerts->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'JobAlerts', 'action' => 'edit', $jobAlerts->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'JobAlerts', 'action' => 'delete', $jobAlerts->id], ['confirm' => __('Are you sure you want to delete # {0}?', $jobAlerts->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Job Alerts found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Job Properties') ?>
        </div>

        <div class="card-body">
            <div class="jobs index content">
                <?php if (!empty($job->job_properties)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Job Id') ?></th>
                                <th><?= __('Meta Data') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($job->job_properties as $jobProperties) : ?>
                                <tr>
                                    <td><?= h($jobProperties->id) ?></td>
                                    <td><?= h($jobProperties->job_id) ?></td>
                                    <td><?= h($jobProperties->meta_data) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'JobProperties', 'action' => 'view', $jobProperties->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'JobProperties', 'action' => 'edit', $jobProperties->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'JobProperties', 'action' => 'delete', $jobProperties->id], ['confirm' => __('Are you sure you want to delete # {0}?', $jobProperties->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Job Properties found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Job Status Movements') ?>
        </div>

        <div class="card-body">
            <div class="jobs index content">
                <?php if (!empty($job->job_status_movements)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Job Id') ?></th>
                                <th><?= __('User Id') ?></th>
                                <th><?= __('Job Status From') ?></th>
                                <th><?= __('Job Status To') ?></th>
                                <th><?= __('Note') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($job->job_status_movements as $jobStatusMovements) : ?>
                                <tr>
                                    <td><?= h($jobStatusMovements->id) ?></td>
                                    <td><?= h($jobStatusMovements->job_id) ?></td>
                                    <td><?= h($jobStatusMovements->user_id) ?></td>
                                    <td><?= h($jobStatusMovements->job_status_from) ?></td>
                                    <td><?= h($jobStatusMovements->job_status_to) ?></td>
                                    <td><?= h($jobStatusMovements->note) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'JobStatusMovements', 'action' => 'view', $jobStatusMovements->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'JobStatusMovements', 'action' => 'edit', $jobStatusMovements->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'JobStatusMovements', 'action' => 'delete', $jobStatusMovements->id], ['confirm' => __('Are you sure you want to delete # {0}?', $jobStatusMovements->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Job Status Movements found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



