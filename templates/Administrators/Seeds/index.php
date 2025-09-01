<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Seed[]|CollectionInterface $seeds
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\Seed;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Seeds'));
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
    'activation',
    'expiration',
    'token',
    'bids',
    'bid_limit',
    'user_link',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($seeds as $seed): ?>
    <?php $rows[$counter][] = $this->Number->format($seed->id) ?>
    <?php $rows[$counter][] = $this->Time->format($seed->activation) ?>
    <?php $rows[$counter][] = $this->Time->format($seed->expiration) ?>
    <?php $rows[$counter][] = h($seed->token) ?>
    <?php $rows[$counter][] = $seed->bids === null ? '' : $this->Number->format($seed->bids) ?>
    <?php $rows[$counter][] = $seed->bid_limit === null ? '' : $this->Number->format($seed->bid_limit) ?>
    <?php $rows[$counter][] = $seed->user_link === null ? '' : $this->Number->format($seed->user_link) ?>
    <?php
    $appendName = ($seed->name) ? ": $seed->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$seed->id}{$appendName}",
        'data-record-id' => $seed->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $seed->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $seed->id], ['confirm' => __('Are you sure you want to delete # {0}?', $seed->id)]);
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
            <?= __('Seed Listing') ?>
            <div class="timer-holder float-end ms-2 d-none">
                <div class="timer-container">
                    <div class="timer-overlay"></div>
                </div>
            </div>

            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Seed'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="seeds index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['activation'] ?>"><?= Inflector::humanize('activation') ?></th>
                            <th data-db-type="<?= $typeMap['expiration'] ?>"><?= Inflector::humanize('expiration') ?></th>
                            <th data-db-type="<?= $typeMap['url'] ?>"><?= Inflector::humanize('token') ?></th>
                            <th data-db-type="<?= $typeMap['bids'] ?>"><?= Inflector::humanize('bids') ?></th>
                            <th data-db-type="<?= $typeMap['bid_limit'] ?>"><?= Inflector::humanize('bid_limit') ?></th>
                            <th data-db-type="<?= $typeMap['user_link'] ?>"><?= __('User ID') ?></th>
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
