<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var HotFolderEntry[]|CollectionInterface $hotFolderEntries
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\HotFolderEntry;
use App\View\AppView;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Hot Folder Entries'));
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
    'hot_folder_id',
    'path',
    'last_check_time',
    'status',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($hotFolderEntries as $hotFolderEntry): ?>
    <?php $rows[$counter][] = $this->Number->format($hotFolderEntry->id) ?>
    <?php
    $hotFolderName = "[{$hotFolderEntry->hot_folder->id}] {$hotFolderEntry->hot_folder->name}";

    $rows[$counter][] = $hotFolderEntry->has('hot_folder') ? $this->Html->link($hotFolderName, ['controller' => 'HotFolders', 'action' => 'view', $hotFolderEntry->hot_folder->id]) : ''
    ?>
    <?php
    $path = $hotFolderEntry->path;
    if (str_ends_with($path, "\\") || str_ends_with($path, "/")) {
        $path = TextFormatter::makeDirectoryTrailingSmartSlash($path);
    }
    $rows[$counter][] = $path;
    ?>
    <?php $rows[$counter][] = $this->Time->format($hotFolderEntry->last_check_time) ?>
    <?php $rows[$counter][] = h($hotFolderEntry->status) ?>
    <?php
    $appendName = ($hotFolderEntry->name) ? ": $hotFolderEntry->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$hotFolderEntry->id}{$appendName}",
        'data-record-id' => $hotFolderEntry->id,
    ];
    $rows[$counter][] =
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $hotFolderEntry->id], ['confirm' => __('Are you sure you want to delete # {0}?', $hotFolderEntry->id)]);
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
            <?= __('Hot Folder Entry Listing') ?>
            <div class="timer-holder float-end d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>
            <?= $this->Html->link(__('Go To Hot Folders'), ['controller' => 'hot-folders', 'action' => 'index'],
                ['class' => 'btn btn-secondary btn-sm float-end me-2', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="hotFolderEntries index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['hot_folder_id'] ?>"><?= Inflector::humanize('Hot Folder ID') ?></th>
                            <th data-db-type="<?= $typeMap['path'] ?>"><?= Inflector::humanize('path') ?></th>
                            <th data-db-type="<?= $typeMap['last_check_time'] ?>"><?= Inflector::humanize('last_check_time') ?></th>
                            <th data-db-type="<?= $typeMap['status'] ?>"><?= Inflector::humanize('status') ?></th>
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
