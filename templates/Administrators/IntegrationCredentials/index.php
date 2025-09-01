<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var IntegrationCredential[]|CollectionInterface $integrationCredentials
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\IntegrationCredential;
use App\Utility\IntegrationCredentials\MicrosoftOpenAuth2\AuthorizationFlow;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Integration Credentials'));
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
    'description',
    'is_enabled',
    'last_status_text',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($integrationCredentials as $integrationCredential): ?>
    <?php
    if ($integrationCredential->last_status_html) {
        $statusHtmlText = $integrationCredential->last_status_html;
    } else {
        $statusHtmlText = '<span class="text-warning">Unknown</span>';
    }

    $statusAction = '';
    if ($integrationCredential->type === 'MicrosoftOpenAuth2') {
        $status = $integrationCredential->last_status_text;
        if ($status === 'unauthorized') {
            $statusAction = ' | ' . $this->Form->postLink(__('Authorize Now'), ['action' => 'authenticate', $integrationCredential->id], ['data' => ['id' => $integrationCredential->id]]);
            $statusHtmlText = '<span class="text-danger">Unauthorized</span>';
        } elseif ($status === 'expired') {
            $statusAction = ' | ' . $this->Form->postLink(__('Refresh Now'), ['action' => 'authenticate', $integrationCredential->id], ['data' => ['id' => $integrationCredential->id]]);
            $statusHtmlText = '<span class="text-warning">Expired</span>';
        } elseif ($status === 'authorized') {
            $statusAction = '';
        }
    }
    ?>

    <?php $rows[$counter][] = $this->Number->format($integrationCredential->id) ?>
    <?php $rows[$counter][] = h($integrationCredential->type) ?>
    <?php $rows[$counter][] = h($integrationCredential->name) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($integrationCredential->description) ?? '', 50) ?>
    <?php $rows[$counter][] = $this->Text->boolToWord($integrationCredential->is_enabled) ?>
    <?php $rows[$counter][] = $statusHtmlText; ?>
    <?php
    $appendName = ($integrationCredential->name) ? ": $integrationCredential->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$integrationCredential->id}{$appendName}",
        'data-record-id' => $integrationCredential->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Edit'), ['action' => 'edit', $integrationCredential->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $integrationCredential->id], ['confirm' => __('Are you sure you want to delete # {0}?', $integrationCredential->id)]) .
        $statusAction;
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
            <?= __('Integration Credential Listing') ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Integration Credential'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="integrationCredentials index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['type'] ?>"><?= Inflector::humanize('type') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['description'] ?>"><?= Inflector::humanize('description') ?></th>
                            <th data-db-type="<?= $typeMap['is_enabled'] ?>"><?= Inflector::humanize('is_enabled') ?></th>
                            <th data-db-type="<?= $typeMap['last_status_text'] ?>"><?= Inflector::humanize('status') ?></th>
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
