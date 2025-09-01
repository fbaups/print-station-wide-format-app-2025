<?php
/**
 * @var AppView $this
 * @var ScheduledTask $scheduledTask
 * @var array $workflows
 */

use App\Model\Entity\ScheduledTask;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Scheduled Task'));
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
$templateParameters = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4"><small class="form-text json-validation"></small> <small class="form-text text-muted">{{help}}</small></div></div>',
];
$templateSchedule = [
    'inputContainer' => '
<div class="input">
<label for="schedule">Schedule</label>
<div class="input-group settings {{type}}{{required}}">
{{content}}
<span class="input-group-text cron-expression-result pb-0 pt-0 mb-0"> </span>
</div>
<div class="mb-4"><small class="form-text text-muted">{{help}}</small></div>
</div>',
];
?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Scheduled Tasks'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($scheduledTask->name) ?? "Scheduled Task Details" ?>
        </div>

        <div class="card-body">
            <div class="scheduledTasks form content">
                <?= $this->Form->create($scheduledTask) ?>
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
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('is_enabled', $opts);
                    $this->Form->switchBackTemplates();

                    $opts = [
                        'label' => false,
                        'class' => 'form-control mb-0',
                        'data-type' => 'string',
                        'templateVars' => ['help' => 'Schedule of when to run the workflow, written as a cron expression.'],
                    ];
                    $this->Form->setTemplates($templateSchedule);
                    echo $this->Form->control('schedule', $opts);
                    $this->Form->resetTemplates();

                    echo '<div class="row workflow parameters">';
                    echo '<div class="col-md-4">';
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                        'options' => $workflows,
                    ];
                    echo $this->Form->control('workflow', $opts);
                    echo '</div>';

                    echo '<div class="col-md-8">';
                    $opts = [
                        'class' => 'form-control mb-0 p-3',
                        'data-type' => 'text',
                        'templateVars' => ['help' => 'An optional JSON object that will be passed to the workflow as parameters.'],
                    ];
                    $this->Form->setTemplates($templateParameters);
                    echo $this->Form->control('parameters', $opts);
                    $this->Form->resetTemplates();
                    echo '</div>';

                    echo '</div>';

                    echo '<div class="row activation expiration auto-delete">';

                    echo '<div class="col-md-4">';
                    $opts = [
                        'label' => ['text' => 'Activation <span class="activation-date-clear link-blue pointer">[Clear]</span>', 'escape' => false],
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                    ];
                    echo $this->Form->control('activation', $opts);
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    $opts = [
                        'label' => ['text' => 'Expiration <span class="expiration-date-clear link-blue pointer">[Clear]</span>', 'escape' => false],
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                    ];
                    echo $this->Form->control('expiration', $opts);
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<div class="d-none d-md-block" style="height: 33px"></div>';
                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('auto_delete', $opts);
                    $this->Form->switchBackTemplates();
                    echo '</div>';

                    echo '</div>';
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
                echo $this->Html->link(__('Back'), ['controller' => 'scheduledTasks'], $options);

                $options = [
                    'class' => 'btn btn-primary submit-scheduled-task'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
            <?= $this->Form->end() ?>
        </div>

    </div>
</div>

<?php
$this->append('viewCustomScripts');
?>
<?= $this->element('form_validation_scheduled_tasks') ?>
<?php
$this->end();
?>
