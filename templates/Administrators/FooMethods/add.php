<?php
/**
 * @var AppView $this
 * @var FooMethod $fooMethod
 * @var CollectionInterface|string[] $fooRecipes
 */

use App\Model\Entity\FooMethod;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Foo Method'));
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
    <?= $this->Form->create($fooMethod) ?>
    <div class="card">

        <div class="card-header">
            <?= h($fooMethod->name) ?? "Foo Method Details" ?>
        </div>

        <div class="card-body">
            <div class="fooMethods form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                        'options' => $fooRecipes,
                        'empty' => true,
                    ];
                    echo $this->Form->control('foo_recipe_id', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                    ];
                    echo $this->Form->control('rank', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('text', $opts);

                    ?>
                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'foo-methods'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
        </div>

    </div>
    <?= $this->Form->end() ?>
</div>
