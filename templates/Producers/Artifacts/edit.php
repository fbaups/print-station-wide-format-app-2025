<?php
/**
 * @var AppView $this
 * @var Artifact $artifact
 */

use App\Model\Entity\Artifact;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Artifact'));
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

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4"><small class="form-text text-muted">{{help}}</small></div></div>',
];
?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Artifacts'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <?= $this->Form->create($artifact) ?>
    <div class="card">

        <div class="card-header">
            <?= h($artifact->name) ?? "Artifact Details" ?>
        </div>

        <div class="card-body">
            <div class="artifacts form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'string',
                        'disabled' => 'disabled',
                        'templateVars' => ['help' => 'Artifact names cannot be changed.'],
                    ];
                    $this->Form->setTemplates($templates);
                    echo $this->Form->control('name', $opts);
                    $this->Form->resetTemplates();

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('description', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                    ];
                    echo $this->Form->control('activation', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                    ];
                    echo $this->Form->control('expiration', $opts);

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('auto_delete', $opts);
                    $this->Form->switchBackTemplates();

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
                echo $this->Html->link(__('Back'), ['controller' => 'artifacts'], $options);

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
