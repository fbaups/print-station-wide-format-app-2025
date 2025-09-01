<?php
/**
 * @var AppView $this
 * @var Role $role
 * @var string[]|CollectionInterface $users
 */

use App\Model\Entity\Role;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Role'));
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
    <?= $this->Html->link(__('&larr; Back to Roles'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <?= $this->Form->create($role) ?>
    <div class="card">

        <div class="card-header">
            <?= h($role->name) ?? "Role Details" ?>
        </div>

        <div class="card-body">
            <div class="roles form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                        'disabled' => true,
                    ];
                    echo $this->Form->control('name', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                        'disabled' => true,
                    ];
                    echo $this->Form->control('description', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                        'disabled' => true,
                    ];
                    echo $this->Form->control('alias', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                    ];
                    echo $this->Form->control('session_timeout', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                    ];
                    echo $this->Form->control('inactivity_timeout', $opts);

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
                echo $this->Html->link(__('Back'), ['controller' => 'roles'], $options);

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
