<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Job[]|CollectionInterface $jobs
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\Job;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Jobs'));
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
    'Jobs.id',
    'Jobs.job_status_id',
    'Jobs.name',
    'Jobs.description',
    'Jobs.quantity',
    'Jobs.external_job_number',
    'Jobs.external_creation_date',
    'Jobs.priority',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($jobs as $job): ?>
    <?php $rows[$counter][] = $job->has('order') && $job->order->name ? $this->Html->link($job->order->id, ['controller' => 'Orders', 'action' => 'view', $job->order->id]) : '' ?>
    <?php $rows[$counter][] = $this->Number->format($job->id) ?>
    <?php $rows[$counter][] = $job->has('job_status') ? $job->job_status->id . " - " . $job->job_status->name : '' ?>
    <?php $rows[$counter][] = h($job->name) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($job->description) ?? '', 50) ?>
    <?php $rows[$counter][] = $this->Number->format($job->quantity) ?>
    <?php $rows[$counter][] = h($job->external_job_number) ?>
    <?php $rows[$counter][] = $this->Time->format($job->external_creation_date) ?>
    <?php $rows[$counter][] = $job->priority === null ? '' : $this->Number->format($job->priority) ?>
    <?php
    $appendName = ($job->name) ? ": $job->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$job->id}{$appendName}",
        'data-record-id' => $job->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $job->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $job->id]);
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
            <?= __('Job Listing') ?>
            <div class="timer-holder float-end d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="jobs index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['order_id'] ?>"><?= Inflector::humanize('Order ID') ?></th>
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= Inflector::humanize('Job ID') ?></th>
                            <th data-db-type="<?= $typeMap['job_status_id'] ?>"><?= Inflector::humanize('job_status') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['description'] ?>"><?= Inflector::humanize('description') ?></th>
                            <th data-db-type="<?= $typeMap['quantity'] ?>"><?= Inflector::humanize('qty') ?></th>
                            <th data-db-type="<?= $typeMap['external_job_number'] ?>"><?= Inflector::humanize('External Job ID') ?></th>
                            <th data-db-type="<?= $typeMap['external_creation_date'] ?>"><?= Inflector::humanize('External Date') ?></th>
                            <th data-db-type="<?= $typeMap['priority'] ?>"><?= Inflector::humanize('priority') ?></th>
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
