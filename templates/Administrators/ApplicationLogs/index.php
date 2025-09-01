<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var ApplicationLog[]|CollectionInterface $applicationLogs
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\ApplicationLog;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Routing\Router;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __('list-columns-reverse'));
$this->set('headerTitle', __('Application Logs'));
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
    'expiration',
    'level',
    'user_link',
    'url',
    'message',
    'actions',
];

$rows = [];
$counter = 0;

$baseUrl = (Router::url("/", true));
?>
<?php foreach ($applicationLogs as $applicationLog): ?>
    <?php $rows[$counter][] = $this->Number->format($applicationLog->id) ?>
    <?php $rows[$counter][] = $this->Time->format($applicationLog->created) ?>
    <?php $rows[$counter][] = h($applicationLog->level) ?>
    <?php $rows[$counter][] = $applicationLog->user_link === null ? '' : $this->Number->format($applicationLog->user_link) ?>
    <?php $rows[$counter][] = $this->Text->truncate(str_replace($baseUrl, '', $applicationLog->url), 50) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($applicationLog->message) ?? '', 50) ?>
    <?php
    $appendName = ($applicationLog->name) ? ": $applicationLog->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$applicationLog->id}{$appendName}",
        'data-record-id' => $applicationLog->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $applicationLog->id]);
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
            <?= __('Application Log Listing') ?>
            <div class="timer-holder float-end d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="applicationLogs index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['created'] ?>"><?= Inflector::humanize('created') ?></th>
                            <th data-db-type="<?= $typeMap['level'] ?>"><?= Inflector::humanize('level') ?></th>
                            <th data-db-type="<?= $typeMap['user_link'] ?>"><?= __('User ID') ?></th>
                            <th data-db-type="<?= $typeMap['url'] ?>"><?= strtoupper('url') ?></th>
                            <th data-db-type="<?= $typeMap['message'] ?>"><?= Inflector::humanize('message') ?></th>
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
        DataTablesManager.autoRefresh = 10000; //every 10 seconds
        DataTablesManager.run();
    });
</script>
<?php
$this->end();
?>
