<?php
/**
 * @var AppView $this
 * @var Document $document
 */

use App\Model\Entity\Document;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Document'));
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
    <?= $this->Html->link(__('&larr; Back to Documents'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($document->name) ?>
            <?= $this->AuthUser->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Document'), ['action' => 'edit', $document->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="documents view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Guid') ?></th>
                            <td><?= h($document->guid) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Job') ?></th>
                            <td><?= $document->has('job') ? $this->AuthUser->link($document->job->name, ['controller' => 'Jobs', 'action' => 'view', $document->job->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Document Status') ?></th>
                            <td><?= $document->has('document_status') ? $this->AuthUser->link($document->document_status->name, ['controller' => 'DocumentStatuses', 'action' => 'view', $document->document_status->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($document->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($document->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Artifact Token') ?></th>
                            <td><?= h($document->artifact_token) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('External Document Number') ?></th>
                            <td><?= h($document->external_document_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($document->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= $document->priority === null ? '' : $this->Number->format($document->priority) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($document->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($document->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('External Creation Date') ?></th>
                            <td><?= h($document->external_creation_date) ?></td>
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
            <div class="documents index content">
                <?php if (!empty($document->users)) : ?>
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
                            <?php foreach ($document->users as $users) : ?>
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
            <?= __('Related Document Alerts') ?>
        </div>

        <div class="card-body">
            <div class="documents index content">
                <?php if (!empty($document->document_alerts)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Document Id') ?></th>
                                <th><?= __('Level') ?></th>
                                <th><?= __('Message') ?></th>
                                <th><?= __('Code') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($document->document_alerts as $documentAlerts) : ?>
                                <tr>
                                    <td><?= h($documentAlerts->id) ?></td>
                                    <td><?= h($documentAlerts->document_id) ?></td>
                                    <td><?= h($documentAlerts->level) ?></td>
                                    <td><?= h($documentAlerts->message) ?></td>
                                    <td><?= h($documentAlerts->code) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'DocumentAlerts', 'action' => 'view', $documentAlerts->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'DocumentAlerts', 'action' => 'edit', $documentAlerts->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'DocumentAlerts', 'action' => 'delete', $documentAlerts->id], ['confirm' => __('Are you sure you want to delete # {0}?', $documentAlerts->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Document Alerts found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Document Properties') ?>
        </div>

        <div class="card-body">
            <div class="documents index content">
                <?php if (!empty($document->document_properties)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Document Id') ?></th>
                                <th><?= __('Meta Data') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($document->document_properties as $documentProperties) : ?>
                                <tr>
                                    <td><?= h($documentProperties->id) ?></td>
                                    <td><?= h($documentProperties->document_id) ?></td>
                                    <td><?= h($documentProperties->meta_data) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'DocumentProperties', 'action' => 'view', $documentProperties->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'DocumentProperties', 'action' => 'edit', $documentProperties->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'DocumentProperties', 'action' => 'delete', $documentProperties->id], ['confirm' => __('Are you sure you want to delete # {0}?', $documentProperties->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Document Properties found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Document Status Movements') ?>
        </div>

        <div class="card-body">
            <div class="documents index content">
                <?php if (!empty($document->document_status_movements)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Document Id') ?></th>
                                <th><?= __('User Id') ?></th>
                                <th><?= __('Document Status From') ?></th>
                                <th><?= __('Document Status To') ?></th>
                                <th><?= __('Note') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($document->document_status_movements as $documentStatusMovements) : ?>
                                <tr>
                                    <td><?= h($documentStatusMovements->id) ?></td>
                                    <td><?= h($documentStatusMovements->document_id) ?></td>
                                    <td><?= h($documentStatusMovements->user_id) ?></td>
                                    <td><?= h($documentStatusMovements->document_status_from) ?></td>
                                    <td><?= h($documentStatusMovements->document_status_to) ?></td>
                                    <td><?= h($documentStatusMovements->note) ?></td>
                                    <td class="actions">
                                        <?= $this->AuthUser->link(__('View'), ['controller' => 'DocumentStatusMovements', 'action' => 'view', $documentStatusMovements->id]) ?>
                                        <?= $this->AuthUser->link(__('Edit'), ['controller' => 'DocumentStatusMovements', 'action' => 'edit', $documentStatusMovements->id]) ?>
                                        <?= $this->AuthUser->postLink(__('Delete'), ['controller' => 'DocumentStatusMovements', 'action' => 'delete', $documentStatusMovements->id], ['confirm' => __('Are you sure you want to delete # {0}?', $documentStatusMovements->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Document Status Movements found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



