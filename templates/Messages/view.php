<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Message $message
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Message'));
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
    <?= $this->Html->link(__('&larr; Back to Messages'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($message->name) ?>
        </div>

        <div class="card-body">
            <div class="messages view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Type') ?></th>
                            <td><?= h($message->type) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($message->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($message->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Server') ?></th>
                            <td><?= h($message->server) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Domain') ?></th>
                            <td><?= h($message->domain) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Transport') ?></th>
                            <td><?= h($message->transport) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Profile') ?></th>
                            <td><?= h($message->profile) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Layout') ?></th>
                            <td><?= h($message->layout) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Template') ?></th>
                            <td><?= h($message->template) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Email Format') ?></th>
                            <td><?= h($message->email_format) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Sender') ?></th>
                            <td><?= json_encode($message->sender, JSON_PRETTY_PRINT) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Email From') ?></th>
                            <td><?= json_encode($message->email_from, JSON_PRETTY_PRINT) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Email To') ?></th>
                            <td><?= json_encode($message->email_to, JSON_PRETTY_PRINT) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Email Cc') ?></th>
                            <td><?= json_encode($message->email_cc, JSON_PRETTY_PRINT) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Email Bcc') ?></th>
                            <td><?= json_encode($message->email_bcc, JSON_PRETTY_PRINT) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Reply To') ?></th>
                            <td><?= json_encode($message->reply_to, JSON_PRETTY_PRINT) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Read Receipt') ?></th>
                            <td><?= h($message->read_receipt) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Subject') ?></th>
                            <td><?= h($message->subject) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('View Vars') ?></th>
                            <td>
                                <pre><?= json_encode($message->view_vars, JSON_PRETTY_PRINT) ?></pre>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Headers') ?></th>
                            <td><?= h($message->headers) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('SMTP Message') ?></th>
                            <td><?= h($message->smtp_message) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Errors Thrown') ?></th>
                            <td>
                                <pre><?= json_encode($message->errors_thrown, JSON_PRETTY_PRINT) ?></pre>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Beacon Hash') ?></th>
                            <td><?= h($message->beacon_hash) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Hash Sum') ?></th>
                            <td><?= h($message->hash_sum) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($message->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Priority') ?></th>
                            <td><?= $message->priority === null ? '' : $this->Number->format($message->priority) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Smtp Code') ?></th>
                            <td><?= $message->smtp_code === null ? '' : $this->Number->format($message->smtp_code) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Lock Code') ?></th>
                            <td><?= $message->lock_code === null ? '' : $this->Number->format($message->lock_code) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Errors Retry') ?></th>
                            <td><?= $message->errors_retry === null ? '' : $this->Number->format($message->errors_retry) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Errors Retry Limit') ?></th>
                            <td><?= $message->errors_retry_limit === null ? '' : $this->Number->format($message->errors_retry_limit) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($message->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($message->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($message->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($message->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Started') ?></th>
                            <td><?= h($message->started) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Completed') ?></th>
                            <td><?= h($message->completed) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $message->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



