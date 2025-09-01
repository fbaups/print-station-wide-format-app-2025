<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Errand[]|CollectionInterface $errands
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 * @var array $servicesStats
 */

use App\Model\Entity\Errand;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Errands'));
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
    'name',
    'class',
    'method',
    'parameters',
    'status',
    'started',
    'completed',
    'priority',
    'return_value',
    'lock_to_thread',
    'actions',
];

$counter = 0;
$rows = [];
?>
<?php foreach ($errands as $errand): ?>
    <?php $rows[$counter][] = $this->Number->format($errand->id) ?>
    <?php $rows[$counter][] = $this->Text->truncate($errand->name, 30) ?>
    <?php $rows[$counter][] = $this->Text->tooltipPathSyntax($errand->class) ?>
    <?php $rows[$counter][] = h($errand->method) ?>
    <?php $rows[$counter][] = $this->Text->truncate($errand->parameters, 35) ?>
    <?php $rows[$counter][] = h($errand->status) ?>
    <?php $rows[$counter][] = ($errand->started) ? $this->Time->format($errand->started) : ''; ?>
    <?php $rows[$counter][] = ($errand->completed) ? $this->Time->format($errand->completed) : ''; ?>
    <?php $rows[$counter][] = $errand->priority === null ? '' : $this->Number->format($errand->priority) ?>
    <?php $rows[$counter][] = $errand->return_value === null ? '' : $this->Number->format($errand->return_value) ?>
    <?php $rows[$counter][] = $errand->lock_to_thread === null ? '' : $this->Number->format($errand->lock_to_thread) ?>
    <?php

    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #$errand->id: $errand->name",
        'data-record-id' => $errand->id,
    ];

    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $errand->id]);
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
    if ($this->AuthUser->hasRole('superadmin')) {
        $editBackgroundServicesUrl = ['controller' => 'background-services', 'action' => 'index'];

        $showAlert = false;
        $alertMessage = '';

        if ($servicesStats['Errand']['total'] === 0) {
            $alertMessage = __('No Errand Background Services are installed.');
            $showAlert = true;
        } elseif ($servicesStats['Errand']['paused'] > 0) {
            $alertMessage = __('Some Errand Background Services are paused. This typically indicates a failure to start the Service.');
            $showAlert = true;
        } elseif ($servicesStats['Errand']['disabled'] > 0) {
            $alertMessage = __('Some Errand Background Services are disabled.');
            $showAlert = true;
        } elseif ($servicesStats['Errand']['running'] === 0) {
            $alertMessage = __('No Errand Background Services are running.');
            $showAlert = true;
        } elseif ($servicesStats['Errand']['total'] !== $servicesStats['Errand']['running']) {
            $alertMessage = __('Only {0} of the {1} Errand Background Services are running.', $servicesStats['Errand']['running'], $servicesStats['Errand']['total']);
            $showAlert = true;
        }

        if ($showAlert) {
            ?>
            <div class="alert alert-info">
                <?php echo $alertMessage ?>
                <?php echo __("Please configure") ?>
                <?php echo $this->Html->link("Background Services", $editBackgroundServicesUrl) ?>.
            </div>
            <?php
        }
    }
    ?>

    <div class="card">

        <div class="card-header">
            <?= __('Errand Listing') ?>
            <div class="timer-holder float-end d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="errands index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['class'] ?>"><?= Inflector::humanize('class') ?></th>
                            <th data-db-type="<?= $typeMap['method'] ?>"><?= Inflector::humanize('method') ?></th>
                            <th data-db-type="<?= $typeMap['parameters'] ?>"><?= Inflector::humanize('parameters') ?></th>
                            <th data-db-type="<?= $typeMap['status'] ?>"><?= Inflector::humanize('status') ?></th>
                            <th data-db-type="<?= $typeMap['started'] ?>"><?= Inflector::humanize('started') ?></th>
                            <th data-db-type="<?= $typeMap['completed'] ?>"><?= Inflector::humanize('completed') ?></th>
                            <th data-db-type="<?= $typeMap['priority'] ?>"><?= Inflector::humanize('priority') ?></th>
                            <th data-db-type="<?= $typeMap['return_value'] ?>"><?= Inflector::humanize('ret_val') ?></th>
                            <th data-db-type="<?= $typeMap['lock_to_thread'] ?>"><?= Inflector::humanize('thread') ?></th>
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
