<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var HotFolder[]|CollectionInterface $hotFolders
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 * @var array $servicesStats
 */

use App\Model\Entity\HotFolder;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Hot Folders'));
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
    'path',
    'workflow',
    'is_enabled',
    'submit_url_enabled',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($hotFolders as $hotFolder): ?>
    <?php $rows[$counter][] = $this->Number->format($hotFolder->id) ?>
    <?php $rows[$counter][] = h($hotFolder->name) ?>
    <?php $rows[$counter][] = h($hotFolder->path) ?>
    <?php $rows[$counter][] = array_reverse(explode("\\", $hotFolder->workflow))[0] ?>
    <?php $rows[$counter][] = $this->Text->boolToWord($hotFolder->is_enabled) ?>
    <?php $rows[$counter][] = $this->Text->boolToWord($hotFolder->submit_url_enabled) ?>
    <?php
    $appendName = ($hotFolder->name) ? ": $hotFolder->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$hotFolder->id}{$appendName}",
        'data-record-id' => $hotFolder->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $hotFolder->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $hotFolder->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $hotFolder->id], ['confirm' => __('Are you sure you want to delete # {0}?', $hotFolder->id)]);
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

        if ($servicesStats['HotFolder']['total'] === 0) {
            $alertMessage = __('No Hot Folder Background Services are installed.');
            $showAlert = true;
        } elseif ($servicesStats['HotFolder']['paused'] > 0) {
            $alertMessage = __('Some Hot Folder Background Services are paused. This typically indicates a failure to start the Service.');
            $showAlert = true;
        } elseif ($servicesStats['HotFolder']['disabled'] > 0) {
            $alertMessage = __('Some Hot Folder Background Services are disabled.');
            $showAlert = true;
        } elseif ($servicesStats['HotFolder']['running'] === 0) {
            $alertMessage = __('No Hot Folder Background Services are running.');
            $showAlert = true;
        } elseif ($servicesStats['HotFolder']['total'] !== $servicesStats['HotFolder']['running']) {
            $alertMessage = __('Only {0} of the {1} Hot Folder Background Services are running.', $servicesStats['HotFolder']['running'], $servicesStats['HotFolder']['total']);
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
            <?= __('Hot Folder Listing') ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Hot Folder'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
            <?= $this->Html->link(__('Go To Tokens'), ['controller' => 'seeds', 'action' => 'hot-folder-tokens'],
                ['class' => 'btn btn-secondary btn-sm float-end me-2', 'escape' => false]) ?>
            <?= $this->Html->link(__('Go To Entries'), ['controller' => 'hot-folder-entries', 'action' => 'index'],
                ['class' => 'btn btn-secondary btn-sm float-end me-2', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="hotFolders index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['path'] ?>"><?= Inflector::humanize('path') ?></th>
                            <th data-db-type="<?= $typeMap['workflow'] ?>"><?= Inflector::humanize('workflow') ?></th>
                            <th data-db-type="<?= $typeMap['is_enabled'] ?>"><?= Inflector::humanize('enabled') ?></th>
                            <th data-db-type="<?= $typeMap['submit_url_enabled'] ?>">URL Submit</th>
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
