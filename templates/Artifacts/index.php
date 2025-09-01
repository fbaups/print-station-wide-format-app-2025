<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Artifact[]|CollectionInterface $artifacts
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var array $repoCheckResult
 * @var string $message
 */

use App\Model\Entity\Artifact;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Artifacts'));
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
    'description',
    'size',
    'mime_type',
    'activation',
    'expiration',
    'auto_delete',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($artifacts as $artifact): ?>
    <?php $rows[$counter][] = $this->Number->format($artifact->id) ?>
    <?php $rows[$counter][] = h($artifact->name) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($artifact->description) ?? '', 50) ?>
    <?php $rows[$counter][] = $artifact->size === null ? '' : $this->Number->toReadableSize($artifact->size) ?>
    <?php $rows[$counter][] = h($artifact->mime_type) ?>
    <?php $rows[$counter][] = $this->Time->format($artifact->activation) ?>
    <?php $rows[$counter][] = $this->Time->format($artifact->expiration) ?>
    <?php $rows[$counter][] = $this->Text->boolToWord($artifact->auto_delete) ?>
    <?php
    $appendName = ($artifact->name) ? ": $artifact->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$artifact->id}{$appendName}",
        'data-record-id' => $artifact->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $artifact->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $artifact->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $artifact->id], ['confirm' => __('Are you sure you want to delete # {0}?', $artifact->id)]);
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
        $editRepoSettingsUrl = ['controller' => 'settings', 'action' => 'edit-group', 'repository'];
        $showAlert = false;

        if (!$repoCheckResult['isURL']) {
            $showAlert = true;
        }

        if (!$repoCheckResult['isSFTP'] && !$repoCheckResult['isUNC']) {
            $showAlert = true;
        }

        if ($showAlert) {
            ?>
            <div class="alert alert-danger">
                <?php echo __("Could not connect to the Repository. Please configure the") ?>
                <?php echo $this->Html->link("Repository Settings", $editRepoSettingsUrl) ?>.
            </div>
            <?php
        }
    }
    ?>

    <div class="card">

        <div class="card-header">
            <?= __('Artifact Listing') ?>
            <div class="timer-holder float-end ms-2 d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>

            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Artifact'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;Mobile Upload'), ['action' => 'mobile-rx'],
                ['class' => 'btn btn-secondary btn-sm float-end me-2', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="artifacts index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['description'] ?>"><?= Inflector::humanize('description') ?></th>
                            <th data-db-type="<?= $typeMap['size'] ?>"><?= Inflector::humanize('size') ?></th>
                            <th data-db-type="<?= $typeMap['mime_type'] ?>"><?= Inflector::humanize('mime_type') ?></th>
                            <th data-db-type="<?= $typeMap['activation'] ?>"><?= Inflector::humanize('activation') ?></th>
                            <th data-db-type="<?= $typeMap['expiration'] ?>"><?= Inflector::humanize('expiration') ?></th>
                            <th data-db-type="<?= $typeMap['auto_delete'] ?>"><?= Inflector::humanize('auto_delete') ?></th>
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
    <div class="modal-dialog modal-fullscreen" role="document">
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
