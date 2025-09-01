<?php
/**
 * @var AppView $this
 * @var HotFolder $hotFolder
 * @var array $workflows
 */

use App\Model\Entity\HotFolder;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Hot Folder'));
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
?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Hot Folders'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($hotFolder->name) ?? "Hot Folder Details" ?>
        </div>

        <div class="card-body">
            <div class="hotFolders form content">
                <?= $this->Form->create($hotFolder) ?>
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
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('path', $opts);

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('is_enabled', $opts);
                    $this->Form->switchBackTemplates();

                    echo '<div class="input mb-4">';
                    $opts = [
                        'class' => 'form-check-input mb-0',
                        'label' => ['class' => 'form-check-label mb-0', 'text' => 'Allow Web Submission'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('submit_url_enabled', $opts);
                    $this->Form->switchBackTemplates();
                    if ($hotFolder->submit_url_enabled) {
                        $showState = '';
                    } else {
                        $showState = ' d-none';
                    }
                    $fullSubmitUrl = \Cake\Routing\Router::fullBaseUrl() . "/hot-folders/submit/" . $hotFolder->submit_url;
                    echo "<p class=\"form-text text-muted submit-url-enabled-help-text{$showState} mb-0 ps-4\">
Web submission enabled at <u><span class=\"fw-bold submit-url-text\">{$fullSubmitUrl}</span></u> with GET/POST/PATCH/PUT requests.
<br/>
Make sure you include a Bearer Token in the Headers e.g. <code>Authorization: Bearer token_value</code>
<br/>
Or in the Query String e.g. <code><span class=\"submit-url-text\">{$fullSubmitUrl}</span>?BearerToken=token_value</code>
</p>";
                    echo '</div>';

                    echo '<div class="row workflow parameters">';

                    echo '<div class="col-md-4">';
                    $opts = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'integer',
                        'default' => 5,
                        'options' => [
                            5 => '5 Seconds',
                            10 => '10 Seconds',
                            15 => '15 Seconds',
                            20 => '20 Seconds',
                            30 => '30 Seconds',
                            60 => '1 Minute',
                            120 => '2 Minutes',
                            300 => '5 Minutes',
                        ],
                        'templateVars' => ['help' => 'Polling interval on the folder.'],
                    ];
                    $this->Form->setTemplates($templates);
                    echo $this->Form->control('polling_interval', $opts);
                    $this->Form->resetTemplates();
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    $opts = [
                        'class' => 'form-control',
                        'data-type' => 'integer',
                        'default' => 4,
                        'options' => [
                            1 => '1 Second',
                            2 => '2 Seconds',
                            3 => '3 Seconds',
                            4 => '4 Seconds',
                            5 => '5 Seconds',
                            10 => '10 Seconds',
                            15 => '15 Seconds',
                            20 => '20 Seconds',
                            30 => '30 Seconds',
                        ],
                        'templateVars' => ['help' => 'Delay to make sure there are no changes to the items in the Hot Folder (e.g. slow network copying).'],
                    ];
                    $this->Form->setTemplates($templates);
                    echo $this->Form->control('stable_interval', $opts);
                    $this->Form->resetTemplates();
                    echo '</div>';

                    echo '</div>';

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
                echo $this->Html->link(__('Back'), ['controller' => 'hot-folders'], $options);

                $options = [
                    'class' => 'btn btn-primary submit-hot-folder'
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
<?= $this->element('form_validation_hot_folders') ?>
<?php
$this->end();
?>
