<?php
/**
 * @var AppView $this
 * @var FooMethod $fooMethod
 */

use App\Model\Entity\FooMethod;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Foo Method'));
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
    <?= $this->Html->link(__('&larr; Back to Foo Methods'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($fooMethod->id) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Foo Method'), ['action' => 'edit', $fooMethod->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="fooMethods view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Foo Recipe') ?></th>
                            <td><?= $fooMethod->has('foo_recipe') ? $this->Html->link($fooMethod->foo_recipe->name, ['controller' => 'FooRecipes', 'action' => 'view', $fooMethod->foo_recipe->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Text') ?></th>
                            <td><?= h($fooMethod->text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($fooMethod->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rank') ?></th>
                            <td><?= $fooMethod->rank === null ? '' : $this->Number->format($fooMethod->rank) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($fooMethod->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($fooMethod->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



