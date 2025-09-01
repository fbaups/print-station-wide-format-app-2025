<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Message[]|CollectionInterface $messages
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $alertMessages
 * @var string $emailDomain
 * @var string $smsGatewayProvider
 */

use App\Model\Entity\Message;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Messages'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => true,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);


//$headers must match the Controller
$headers = [
    'id',
    'type',
    'name',
    'completed',
    'subject',
    'email_to',
    'smtp_message',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($messages as $message): ?>
    <?php $rows[$counter][] = $this->Number->format($message->id) ?>
    <?php $rows[$counter][] = h($message->type) ?>
    <?php $rows[$counter][] = h($message->name) ?>
    <?php $rows[$counter][] = $this->Time->format($message->completed) ?>
    <?php $rows[$counter][] = h($message->subject) ?>
    <?php $rows[$counter][] = h($message->email_to) ?>
    <?php $rows[$counter][] = h($message->smtp_message) ?>
    <?php
    $appendName = ($message->name) ? ": $message->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$message->id}{$appendName}",
        'data-record-id' => $message->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $message->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id)]);
    ?>
    <?php $counter++ ?>
<?php endforeach; ?>
<?php
if ($isAjax) {
    $result = [
        "message" => $message,
        "draw" => intval($datatablesQuery['draw']),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $rows,
    ];
    echo json_encode($result, JSON_PRETTY_PRINT);
    return;
}
?>
<div class="container-fluid px-4">
    <?php
    if ($emailDomain === 'localhost.com') {
        ?>
        <div class="alert alert-warning">
            <?php
            $message = __('It looks like the SMTP server settings have not been configured. Please \'Edit SMTP Settings\' before sending email messages.');
            echo $message;
            ?>
        </div>
        <?php
    }
    ?>

    <?php
    if ($smsGatewayProvider === '\App\MessageGateways\DummySmsGateway') {
        ?>
        <div class="alert alert-warning">
            <?php
            $message = __('It looks like the SMS Gateway is not configured. Please \'Edit SMS Gateway Settings\' before sending SMS messages.');
            echo $message;
            ?>
        </div>
        <?php
    }
    ?>
    <div class="card">

        <div class="card-header">
            <?= __('Message Listing') ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit SMTP Settings'), ['controller' => 'settings', 'action' => 'edit-group', 'email_server'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit SMS Gateway Settings'), ['controller' => 'settings', 'action' => 'edit-group', 'sms_gateway'],
                ['class' => 'btn btn-secondary btn-sm float-end me-2', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="messages index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['type'] ?>"><?= Inflector::humanize('type') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['completed'] ?>"><?= Inflector::humanize('completed') ?></th>
                            <th data-db-type="<?= $typeMap['subject'] ?>"><?= Inflector::humanize('subject') ?></th>
                            <th data-db-type="<?= $typeMap['email_to'] ?>"><?= Inflector::humanize('email_to') ?></th>
                            <th data-db-type="<?= $typeMap['smtp_message'] ?>"><?= __('SMTP Message') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <thead>
                        <tr class="filters">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>

                        </thead>
                        <tbody>
                        <!--populated by DataTables-->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="previewRecord" tabindex="-1" role="dialog" aria-labelledby="previewRecord"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo __('Record Preview') ?></h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="loader-content">
                    <div class="spinner-border d-inline-block align-middle" role="status">
                        <span class="sr-only"><?php echo __('Loading...') ?></span>
                    </div>
                    <span class="px-3 align-middle"><?php echo __('Loading Record Data...') ?></span>
                </div>
                <div class="record-content">

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');

//DataTables initialisation for the index view...
echo $this->Html->script('datatables_manager');

?>
<script>
    $(document).ready(function () {
        //DataTablesManager.autoRefresh = 10000; //every 10 seconds
        DataTablesManager.run();
    });
</script>
<?php
$this->end();
?>
