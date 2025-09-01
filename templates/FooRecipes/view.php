<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FooRecipe $fooRecipe
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Foo Recipe'));
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
    <?= $this->Html->link(__('&larr; Back to Foo Recipes'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($fooRecipe->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Foo Recipe'), ['action' => 'edit', $fooRecipe->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($fooRecipe->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($fooRecipe->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Meta') ?></th>
                            <td><?= h($fooRecipe->meta) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($fooRecipe->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Ingredient Count') ?></th>
                            <td><?= $fooRecipe->ingredient_count === null ? '' : $this->Number->format($fooRecipe->ingredient_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Method Count') ?></th>
                            <td><?= $fooRecipe->method_count === null ? '' : $this->Number->format($fooRecipe->method_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($fooRecipe->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($fooRecipe->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Publish Date') ?></th>
                            <td><?= h($fooRecipe->publish_date) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Active') ?></th>
                            <td><?= $fooRecipe->is_active ? __('Yes') : __('No'); ?></td>
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
            <?= __('Related Foo Authors') ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes index content">
                <?php if (!empty($fooRecipe->foo_authors)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Name') ?></th>
                                <th><?= __('Is Active') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($fooRecipe->foo_authors as $fooAuthors) : ?>
                                <tr>
                                    <td><?= h($fooAuthors->id) ?></td>
                                    <td><?= h($fooAuthors->name) ?></td>
                                    <td><?= h($fooAuthors->is_active) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'FooAuthors', 'action' => 'view', $fooAuthors->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'FooAuthors', 'action' => 'edit', $fooAuthors->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'FooAuthors', 'action' => 'delete', $fooAuthors->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooAuthors->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Foo Authors found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Foo Tags') ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes index content">
                <?php if (!empty($fooRecipe->foo_tags)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Name') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($fooRecipe->foo_tags as $fooTags) : ?>
                                <tr>
                                    <td><?= h($fooTags->id) ?></td>
                                    <td><?= h($fooTags->name) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'FooTags', 'action' => 'view', $fooTags->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'FooTags', 'action' => 'edit', $fooTags->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'FooTags', 'action' => 'delete', $fooTags->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooTags->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Foo Tags found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Foo Ingredients') ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes index content">
                <?php if (!empty($fooRecipe->foo_ingredients)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Foo Recipe Id') ?></th>
                                <th><?= __('Rank') ?></th>
                                <th><?= __('Text') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($fooRecipe->foo_ingredients as $fooIngredients) : ?>
                                <tr>
                                    <td><?= h($fooIngredients->id) ?></td>
                                    <td><?= h($fooIngredients->foo_recipe_id) ?></td>
                                    <td><?= h($fooIngredients->rank) ?></td>
                                    <td><?= h($fooIngredients->text) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'FooIngredients', 'action' => 'view', $fooIngredients->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'FooIngredients', 'action' => 'edit', $fooIngredients->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'FooIngredients', 'action' => 'delete', $fooIngredients->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooIngredients->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Foo Ingredients found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Foo Methods') ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes index content">
                <?php if (!empty($fooRecipe->foo_methods)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Foo Recipe Id') ?></th>
                                <th><?= __('Rank') ?></th>
                                <th><?= __('Text') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($fooRecipe->foo_methods as $fooMethods) : ?>
                                <tr>
                                    <td><?= h($fooMethods->id) ?></td>
                                    <td><?= h($fooMethods->foo_recipe_id) ?></td>
                                    <td><?= h($fooMethods->rank) ?></td>
                                    <td><?= h($fooMethods->text) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'FooMethods', 'action' => 'view', $fooMethods->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'FooMethods', 'action' => 'edit', $fooMethods->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'FooMethods', 'action' => 'delete', $fooMethods->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooMethods->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Foo Methods found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Foo Ratings') ?>
        </div>

        <div class="card-body">
            <div class="fooRecipes index content">
                <?php if (!empty($fooRecipe->foo_ratings)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Foo Recipe Id') ?></th>
                                <th><?= __('Score') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($fooRecipe->foo_ratings as $fooRatings) : ?>
                                <tr>
                                    <td><?= h($fooRatings->id) ?></td>
                                    <td><?= h($fooRatings->foo_recipe_id) ?></td>
                                    <td><?= h($fooRatings->score) ?></td>
                                    <td class="actions">
                                        <?= $this->Html->link(__('View'), ['controller' => 'FooRatings', 'action' => 'view', $fooRatings->id]) ?>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'FooRatings', 'action' => 'edit', $fooRatings->id]) ?>
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'FooRatings', 'action' => 'delete', $fooRatings->id], ['confirm' => __('Are you sure you want to delete # {0}?', $fooRatings->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Foo Ratings found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>



