<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FooIngredient $fooIngredient
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Foo Ingredient'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);

?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Foo Ingredients'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($fooIngredient->id) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Foo Ingredient'), ['action' => 'edit', $fooIngredient->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooIngredients view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Foo Recipe') ?></th>
                            <td><?= $fooIngredient->has('foo_recipe') ? $this->Html->link($fooIngredient->foo_recipe->name, ['controller' => 'FooRecipes', 'action' => 'view', $fooIngredient->foo_recipe->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Text') ?></th>
                            <td><?= h($fooIngredient->text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($fooIngredient->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rank') ?></th>
                            <td><?= $fooIngredient->rank === null ? '' : $this->Number->format($fooIngredient->rank) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($fooIngredient->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($fooIngredient->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



