<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Heartbeat[]|CollectionInterface $heartbeats
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\Heartbeat;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Heartbeats'));
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
    'created',
    'server',
    'domain',
    'type',
    'context',
    'pid',
    'name',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($heartbeats as $heartbeat): ?>
    <?php $rows[$counter][] = $this->Number->format($heartbeat->id) ?>
    <?php $rows[$counter][] = $this->Time->format($heartbeat->created) ?>
    <?php $rows[$counter][] = h($heartbeat->server) ?>
    <?php $rows[$counter][] = h($heartbeat->domain) ?>
    <?php $rows[$counter][] = h($heartbeat->type) ?>
    <?php $rows[$counter][] = h($heartbeat->context) ?>
    <?php $rows[$counter][] = $heartbeat->pid === null ? '' : $this->Number->format($heartbeat->pid) ?>
    <?php $rows[$counter][] = h($heartbeat->name) ?>
    <?php
    $appendName = ($heartbeat->name) ? ": $heartbeat->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$heartbeat->id}{$appendName}",
        'data-record-id' => $heartbeat->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $heartbeat->id], ['confirm' => __('Are you sure you want to delete # {0}?', $heartbeat->id)]);
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
    <div class="card">

        <div class="card-header">
            <?= __('Heartbeat Listing') ?>
            <?= $this->Form->postLink(__('Purge'), ['action' => 'truncate'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'confirm' => __('Are you sure you want to purge all records?')]); ?>
        </div>

        <div class="card-body">
            <div class="heartbeats index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['created'] ?>"><?= Inflector::humanize('created') ?></th>
                            <th data-db-type="<?= $typeMap['server'] ?>"><?= Inflector::humanize('server') ?></th>
                            <th data-db-type="<?= $typeMap['domain'] ?>"><?= Inflector::humanize('domain') ?></th>
                            <th data-db-type="<?= $typeMap['type'] ?>"><?= Inflector::humanize('type') ?></th>
                            <th data-db-type="<?= $typeMap['context'] ?>"><?= Inflector::humanize('context') ?></th>
                            <th data-db-type="<?= $typeMap['pid'] ?>"><?= Inflector::humanize('pid') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
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
