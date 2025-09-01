<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var FooRecipe[]|CollectionInterface $fooRecipes
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\FooRecipe;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Foo Recipes'));
$this->set('headerSubTitle', __("Test pages to see how the application reacts to masses of Data."));

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
    'publish_date',
    'ingredient_count',
    'method_count',
    'is_active',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($fooRecipes as $fooRecipe): ?>
    <?php $rows[$counter][] = $this->Number->format($fooRecipe->id) ?>
    <?php $rows[$counter][] = h($fooRecipe->name) ?>
    <?php $rows[$counter][] = $this->Text->truncate(h($fooRecipe->description) ?? '', 50) ?>
    <?php $rows[$counter][] = $this->Time->format($fooRecipe->publish_date) ?>
    <?php $rows[$counter][] = $fooRecipe->ingredient_count === null ? '' : $this->Number->format($fooRecipe->ingredient_count) ?>
    <?php $rows[$counter][] = $fooRecipe->method_count === null ? '' : $this->Number->format($fooRecipe->method_count) ?>
    <?php $rows[$counter][] = $this->Text->boolToWord($fooRecipe->is_active) ?>
    <?php
    $appendName = ($fooRecipe->name) ? ": $fooRecipe->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$fooRecipe->id}{$appendName}",
        'data-record-id' => $fooRecipe->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $fooRecipe->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $fooRecipe->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $fooRecipe->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooRecipe->id)]);
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
            <?= __('Foo Recipe Listing') ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Foo Recipe'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['name'] ?>"><?= Inflector::humanize('name') ?></th>
                            <th data-db-type="<?= $typeMap['description'] ?>"><?= Inflector::humanize('description') ?></th>
                            <th data-db-type="<?= $typeMap['publish_date'] ?>"><?= Inflector::humanize('publish_date') ?></th>
                            <th data-db-type="<?= $typeMap['ingredient_count'] ?>"><?= Inflector::humanize('ingredient_count') ?></th>
                            <th data-db-type="<?= $typeMap['method_count'] ?>"><?= Inflector::humanize('method_count') ?></th>
                            <th data-db-type="<?= $typeMap['is_active'] ?>"><?= Inflector::humanize('is_active') ?></th>
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
        //DataTablesManager.autoRefresh = 10000; //every 10 seconds
        DataTablesManager.run();
    });
</script>
<?php
$this->end();
?>
