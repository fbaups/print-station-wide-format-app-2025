<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Order[]|CollectionInterface $orders
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\Order;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Orders'));
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
    'Orders.id',
    'order_status_id',
    'Orders.name',
    'Orders.description',
    'Orders.quantity',
    'Orders.external_order_number',
    'Orders.external_creation_date',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($orders as $order): ?>
    <?php $rows[$counter][] = $this->Number->format($order->id) ?>
    <?php $rows[$counter][] = $order->has('order_status') ? $order->order_status->id . " - " . $order->order_status->name : '' ?>
    <?php $rows[$counter][] = h($order->name) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($order->description) ?? '', 50) ?>
    <?php $rows[$counter][] = $this->Number->format($order->quantity) ?>
    <?php $rows[$counter][] = h($order->external_order_number) ?>
    <?php $rows[$counter][] = $this->Time->format($order->external_creation_date) ?>
    <?php
    $appendName = ($order->name) ? ": $order->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$order->id}{$appendName}",
        'data-record-id' => $order->id,
    ];
    $outputLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#orderOutput",
        'data-record-title' => "Order #{$order->id}{$appendName}",
        'data-record-id' => $order->id,
    ];
    $deleteLink = '';
    if ($this->AuthUser->hasAccess(['action' => 'delete'])) {
        $deleteLink = " | " .
            $this->Form->postLink(__('Delete'), ['action' => 'delete', $order->id], ['confirm' => __('Are you sure you want to delete # {0}?', $order->id)]);
    }
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('Ouptut'), "#", $outputLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $order->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $order->id]) .
        $deleteLink;
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
            <?= __('Order Listing') ?>
            <div class="timer-holder float-end d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="orders index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= Inflector::humanize('Order ID') ?></th>
                            <th data-db-type="<?= $typeMap['order_status_id'] ?>"><?= Inflector::humanize('order_status') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['description'] ?>"><?= Inflector::humanize('description') ?></th>
                            <th data-db-type="<?= $typeMap['quantity'] ?>"><?= Inflector::humanize('quantity') ?></th>
                            <th data-db-type="<?= $typeMap['external_order_number'] ?>"><?= Inflector::humanize('External Order ID') ?></th>
                            <th data-db-type="<?= $typeMap['external_creation_date'] ?>"><?= Inflector::humanize('External Date') ?></th>
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

<div class="modal fade" id="orderOutput" tabindex="-1" role="dialog" aria-labelledby="orderOutput"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo __('Order Output') ?></h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="loader-content">
                    <div class="spinner-border d-inline-block align-middle" role="status">
                        <span class="sr-only"><?php echo __('Loading...') ?></span>
                    </div>
                    <span class="px-3 align-middle"><?php echo __('Loading Order Data...') ?></span>
                </div>
                <div class="output-processor-content">

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
echo $this->Html->script('output_processor');

?>
<script>
    $(document).ready(function () {
        DataTablesManager.autoRefresh = 10000; //every 10 seconds
        DataTablesManager.run();

        OutputProcessor.run();
    });
</script>
<?php
$this->end();
?>
