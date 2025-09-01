<?php
/**
 * @var AppView $this
 * @var Errand $errand
 */

use App\Model\Entity\Errand;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Errand'));
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
    <?= $this->Html->link(__('&larr; Back to Errands'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($errand->name) ?> Details
        </div>

        <div class="card-body">
            <div class="errands view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($errand->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Server') ?></th>
                            <td><?= h($errand->server) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Domain') ?></th>
                            <td><?= h($errand->domain) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($errand->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('BackgroundService Name') ?></th>
                            <td><?= h($errand->background_service_name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Class') ?></th>
                            <td><?= h($errand->class) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Method') ?></th>
                            <td><?= h($errand->method) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parameters') ?></th>
                            <td>
                                <pre><?= h(json_encode($errand->parameters, JSON_PRETTY_PRINT)) ?></pre>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Status') ?></th>
                            <td><?= h($errand->status) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Return Message') ?></th>
                            <td>
                                <pre><?= h(json_encode($errand->return_message, JSON_PRETTY_PRINT)) ?></pre>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Errors Thrown') ?></th>
                            <td>
                                <pre><?= h(json_encode($errand->errors_thrown, JSON_PRETTY_PRINT)) ?></pre>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Hash Sum') ?></th>
                            <td><?= h($errand->hash_sum) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Wait For Link') ?></th>
                            <td><?= $errand->wait_for_link === null ? '' : $this->Number->format($errand->wait_for_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('BackgroundService Link') ?></th>
                            <td><?= $errand->background_service_link === null ? '' : $this->Number->format($errand->background_service_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Progress Bar') ?></th>
                            <td><?= $errand->progress_bar === null ? '' : $this->Number->format($errand->progress_bar) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= $errand->priority === null ? '' : $this->Number->format($errand->priority) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Return Value') ?></th>
                            <td><?= $errand->return_value === null ? '' : $this->Number->format($errand->return_value) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Errors Retry') ?></th>
                            <td><?= $errand->errors_retry === null ? '' : $this->Number->format($errand->errors_retry) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Errors Retry Limit') ?></th>
                            <td><?= $errand->errors_retry_limit === null ? '' : $this->Number->format($errand->errors_retry_limit) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Lock To Thread') ?></th>
                            <td><?= $errand->lock_to_thread === null ? '' : $this->Number->format($errand->lock_to_thread) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($errand->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($errand->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($errand->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($errand->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Started') ?></th>
                            <td><?= h($errand->started) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Completed') ?></th>
                            <td><?= h($errand->completed) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $errand->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



