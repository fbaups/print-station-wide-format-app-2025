<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var FooIngredient[]|CollectionInterface $fooIngredients
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\FooIngredient;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Foo Ingredients'));
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
    'foo_recipe_id',
    'rank',
    'text',
    'actions',
];

$rows = [];
$counter = 0;
?>
<?php foreach ($fooIngredients as $fooIngredient): ?>
    <?php $rows[$counter][] = $this->Number->format($fooIngredient->id) ?>
    <?php $rows[$counter][] = $fooIngredient->has('foo_recipe') ? $this->Html->link($fooIngredient->foo_recipe->name, ['controller' => 'FooRecipes', 'action' => 'view', $fooIngredient->foo_recipe->id]) : '' ?>
    <?php $rows[$counter][] = $fooIngredient->rank === null ? '' : $this->Number->format($fooIngredient->rank) ?>
    <?php $rows[$counter][] = h($fooIngredient->text) ?>
    <?php
    $appendName = ($fooIngredient->name) ? ": $fooIngredient->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$fooIngredient->id}{$appendName}",
        'data-record-id' => $fooIngredient->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $fooIngredient->id]) . " | " .
        $this->Html->link(__('Edit'), ['action' => 'edit', $fooIngredient->id]) . " | " .
        $this->Form->postLink(__('Delete'), ['action' => 'delete', $fooIngredient->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooIngredient->id)]);
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
            <?= __('Foo Ingredient Listing') ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;New Foo Ingredient'), ['action' => 'add'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooIngredients index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['foo_recipe_id'] ?>"><?= Inflector::humanize('foo_recipe_id') ?></th>
                            <th data-db-type="<?= $typeMap['rank'] ?>"><?= Inflector::humanize('rank') ?></th>
                            <th data-db-type="<?= $typeMap['text'] ?>"><?= Inflector::humanize('text') ?></th>
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
