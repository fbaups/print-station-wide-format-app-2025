<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FooTag $fooTag
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Foo Tag'));
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
    <?= $this->Html->link(__('&larr; Back to Foo Tags'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($fooTag->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Foo Tag'), ['action' => 'edit', $fooTag->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooTags view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($fooTag->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($fooTag->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($fooTag->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($fooTag->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Foo Recipes') ?>
        </div>

        <div class="card-body">
            <div class="fooTags index content">
                <?php if (!empty($fooTag->foo_recipes)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Description') ?></th>
                                <th><?= __('Publish Date') ?></th>
                                <th><?= __('Ingredient Count') ?></th>
                                <th><?= __('Method Count') ?></th>
                                <th><?= __('Is Active') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($fooTag->foo_recipes as $fooRecipes) : ?>
                                <tr>
                                    <td><?= h($fooRecipes->id) ?></td>
                                    <td><?= h($fooRecipes->name) ?></td>
                                    <td><?= h($fooRecipes->description) ?></td>
                                    <td><?= h($fooRecipes->publish_date) ?></td>
                                    <td><?= h($fooRecipes->ingredient_count) ?></td>
                                    <td><?= h($fooRecipes->method_count) ?></td>
                                    <td><?= h($fooRecipes->is_active) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'FooRecipes', 'action' => 'view', $fooRecipes->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'FooRecipes', 'action' => 'edit', $fooRecipes->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'FooRecipes', 'action' => 'delete', $fooRecipes->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooRecipes->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Foo Recipes found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



