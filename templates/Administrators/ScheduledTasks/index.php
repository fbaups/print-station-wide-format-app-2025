<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var ScheduledTask[]|CollectionInterface $scheduledTasks
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 * @var array $servicesStats
 */

use App\Model\Entity\ScheduledTask;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Scheduled Tasks'));
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
    'schedule',
    'workflow',
    'is_enabled',
    'next_run_time',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($scheduledTasks as $scheduledTask): ?>
    <?php $rows[$counter][] = $this->Number->format($scheduledTask->id) ?>
    <?php $rows[$counter][] = h($scheduledTask->name) ?>
    <?php $rows[$counter][] = h($scheduledTask->schedule) ?>
    <?php $rows[$counter][] = array_reverse(explode("\\", $scheduledTask->workflow))[0] ?>
    <?php $rows[$counter][] = $this->Text->boolToWord($scheduledTask->is_enabled) ?>
    <?php $rows[$counter][] = $this->Time->format($scheduledTask->next_run_time) ?>
    <?php
    $appendName = ($scheduledTask->name) ? ": $scheduledTask->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$scheduledTask->id}{$appendName}",
        'data-record-id' => $scheduledTask->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $scheduledTask->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $scheduledTask->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $scheduledTask->id], ['confirm' => __('Are you sure you want to delete # {0}?', $scheduledTask->id)]);
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

        if ($servicesStats['ScheduledTask']['total'] === 0) {
            $alertMessage = __('No Scheduled Task Background Services are installed.');
            $showAlert = true;
        } elseif ($servicesStats['ScheduledTask']['paused'] > 0) {
            $alertMessage = __('Some Scheduled Task Background Services are paused. This typically indicates a failure to start the Service.');
            $showAlert = true;
        } elseif ($servicesStats['ScheduledTask']['disabled'] > 0) {
            $alertMessage = __('Some Scheduled Task Background Services are disabled.');
            $showAlert = true;
        } elseif ($servicesStats['ScheduledTask']['running'] === 0) {
            $alertMessage = __('No Scheduled Task Background Services are running.');
            $showAlert = true;
        } elseif ($servicesStats['ScheduledTask']['total'] !== $servicesStats['ScheduledTask']['running']) {
            $alertMessage = __('Only {0} of the {1} Scheduled Task Background Services are running.', $servicesStats['ScheduledTask']['running'], $servicesStats['ScheduledTask']['total']);
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
            <?= __('Scheduled Task Listing') ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Scheduled Task'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="scheduledTasks index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['schedule'] ?>"><?= Inflector::humanize('schedule') ?></th>
                            <th data-db-type="<?= $typeMap['workflow'] ?>"><?= Inflector::humanize('workflow') ?></th>
                            <th data-db-type="<?= $typeMap['is_enabled'] ?>"><?= Inflector::humanize('is_enabled') ?></th>
                            <th data-db-type="<?= $typeMap['next_run_time'] ?>"><?= Inflector::humanize('next_run_time') ?></th>
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
