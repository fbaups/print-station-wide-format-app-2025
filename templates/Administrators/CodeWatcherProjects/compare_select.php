<?php
/**
 * @var AppView $this
 * @var CodeWatcherProject[]|CollectionInterface|Query $codeWatcherProjects
 */

use App\Model\Entity\CodeWatcherProject;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\ORM\Query;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Compare Projects'));
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
    <?= $this->Html->link(__('&larr; Back to Code Watcher Projects'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <?= $this->Form->create(null) ?>
    <div class="card">

        <div class="card-header">
            Select Projects
        </div>

        <div class="card-body">
            <div class="codeWatcherProjects view content">
                <div class="projects form large-9 medium-8 columns content">
                    <fieldset>
                        <legend><?= __('Compare Projects') ?></legend>
                        <?php

                        $optsLeft = [
                            'label' => [
                                'class' => 'col-sm-2 col-form-label'
                            ],
                            'class' => 'form-control mb-4',
                            'data-type' => 'string',
                            'options' => (clone $codeWatcherProjects)->find('list'),
                            'default' => (clone $codeWatcherProjects)->orderBy(['id'])->first()->id,
                        ];

                        $optsRight = [
                            'label' => [
                                'class' => 'col-sm-2 col-form-label'
                            ],
                            'class' => 'form-control mb-4',
                            'data-type' => 'string',
                            'options' => (clone $codeWatcherProjects)->find('list'),
                            'default' => (clone $codeWatcherProjects)->orderBy(['id'])->limit(1)->offset(1)->first()->id,
                        ];

                        ?>

                        <div class="row">
                            <div class="col-6">
                                <?php
                                echo $this->Form->control('left-project', $optsLeft);
                                ?>
                            </div>
                            <div class="col-6">
                                <?php
                                echo $this->Form->control('right-project', $optsRight);
                                ?>
                            </div>
                        </div>

                    </fieldset>
                    <?php
                    $options = [
                        'class' => 'btn btn-primary'
                    ];
                    ?>
                    <?= $this->Form->button(__('Compare'), $options) ?>
                </div>
            </div>
        </div>

    </div>
    <?= $this->Form->end() ?>
</div>




