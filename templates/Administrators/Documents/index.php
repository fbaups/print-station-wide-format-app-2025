<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Document[]|CollectionInterface $documents
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\Document;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Documents'));
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
    'Jobs.order_id',
    'Documents.job_id',
    'Documents.id',
    'Documents.document_status_id',
    'Documents.name',
    'Documents.description',
    'Documents.quantity',
    'Documents.external_document_number',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($documents as $document): ?>
    <?php
    if ($document->has('job')) {
        if ($document->job->has('order')) {
            $rows[$counter][] = $this->Html->link($document->job->order->id, ['controller' => 'Orders', 'action' => 'view', $document->job->order->id]);
        } else {
            $rows[$counter][] = '';
        }
    } else {
        $rows[$counter][] = '';
    }
    ?>
    <?php $rows[$counter][] = $document->has('job') && $document->job->name ? $this->Html->link($document->job->id, ['controller' => 'Jobs', 'action' => 'view', $document->job->id]) : '' ?>
    <?php $rows[$counter][] = $this->Number->format($document->id) ?>
    <?php $rows[$counter][] = $document->has('document_status') ? $document->document_status->id . " - " . $document->document_status->name : '' ?>
    <?php $rows[$counter][] = h($document->name) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($document->description) ?? '', 50) ?>
    <?php $rows[$counter][] = $this->Number->format($document->quantity) ?>
    <?php $rows[$counter][] = h($document->external_document_number) ?>
    <?php
    $appendName = ($document->name) ? ": $document->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$document->id}{$appendName}",
        'data-record-id' => $document->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $document->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $document->id]) . " | " .
        $this->Html->link(__('Force Download'), ['action' => 'download', $document->id]);
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
            <?= __('Document Listing') ?>
            <div class="timer-holder float-end d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="documents index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="integer"><?= Inflector::humanize('Order ID') ?></th>
                            <th data-db-type="<?= $typeMap['job_id'] ?>"><?= Inflector::humanize('Job ID') ?></th>
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= Inflector::humanize('Document ID') ?></th>
                            <th data-db-type="<?= $typeMap['document_status_id'] ?>"><?= Inflector::humanize('document_status') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['description'] ?>"><?= Inflector::humanize('description') ?></th>
                            <th data-db-type="<?= $typeMap['quantity'] ?>"><?= Inflector::humanize('quantity') ?></th>
                            <th data-db-type="<?= $typeMap['external_document_number'] ?>"><?= Inflector::humanize('external_document_number') ?></th>
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
        DataTablesManager.autoRefresh = 10000; //every 10 seconds
        DataTablesManager.run();
    });
</script>
<?php
$this->end();
?>
