<?php
/**
 * @var AppView $this
 * @var ScheduledTask $scheduledTask
 */

use App\Model\Entity\ScheduledTask;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Scheduled Task'));
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
    <?= $this->Html->link(__('&larr; Back to Scheduled Tasks'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($scheduledTask->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Scheduled Task'), ['action' => 'edit', $scheduledTask->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="scheduledTasks view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($scheduledTask->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($scheduledTask->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Schedule') ?></th>
                            <td><?= h($scheduledTask->schedule) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Workflow') ?></th>
                            <td><?= h($scheduledTask->workflow) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($scheduledTask->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($scheduledTask->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($scheduledTask->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($scheduledTask->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($scheduledTask->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Next Polling Time') ?></th>
                            <td><?= h($scheduledTask->next_run_time) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $scheduledTask->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Enabled') ?></th>
                            <td><?= $scheduledTask->is_enabled ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                    <div class="text">
                        <strong><?= __('Parameters') ?></strong>
                        <blockquote>
                            <?= $this->Text->autoParagraph(h($scheduledTask->parameters)); ?>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>



