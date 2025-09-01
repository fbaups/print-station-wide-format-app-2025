<?php
/**
 * @var AppView $this
 * @var Subscription $subscription
 * @var CollectionInterface|string[] $roles
 * @var CollectionInterface|string[] $users
 */

use App\Model\Entity\Subscription;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Subscription'));
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
    <?= $this->Html->link(__('&larr; Back to Subscriptions'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <?= $this->Form->create($subscription) ?>
    <div class="card">

        <div class="card-header">
            <?= h($subscription->name) ?? "Subscription Details" ?>
        </div>

        <div class="card-body">
            <div class="subscriptions form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('name', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('description', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                    ];
                    echo $this->Form->control('priority', $opts);

                    $opts = [
                        'label' => ['text' => 'Available to Roles'],
                        'class' => 'form-control mb-4',
                        'size' => 7,
                        'data-type' => 'string',
                        'options' => $roles,
                    ];
                    echo $this->Form->control('roles._ids', $opts);
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
                echo $this->Html->link(__('Back'), ['controller' => 'subscriptions'], $options);

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
