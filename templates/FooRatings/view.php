<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FooRating $fooRating
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Foo Rating'));
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
    <?= $this->Html->link(__('&larr; Back to Foo Ratings'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($fooRating->id) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Foo Rating'), ['action' => 'edit', $fooRating->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooRatings view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Foo Recipe') ?></th>
                            <td><?= $fooRating->has('foo_recipe') ? $this->Html->link($fooRating->foo_recipe->name, ['controller' => 'FooRecipes', 'action' => 'view', $fooRating->foo_recipe->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($fooRating->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Score') ?></th>
                            <td><?= $fooRating->score === null ? '' : $this->Number->format($fooRating->score) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($fooRating->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($fooRating->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



