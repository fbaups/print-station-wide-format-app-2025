<?php
/**
 * @var AppView $this
 * @var Artifact $artifact
 */

use App\Model\Entity\Artifact;
use App\View\AppView;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Artifact'));
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
$activation = (new DateTime())->second(0);
$months = intval(Configure::read("Settings.repo_purge"));
$expiration = (clone $activation)->addMonths($months)->second(0);

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4 mb-md-0"><small class="form-text text-muted">{{help}}</small></div></div>',
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
    <?php
    $formOpts = ['type' => 'file'];
    echo $this->Form->create($artifact, $formOpts);
    ?>
    <div class="card">

        <div class="card-header">
            <?= h($artifact->name) ?? "Artifact Details" ?>
        </div>

        <div class="card-body">
            <div class="artifacts form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <p>There is an upload limit of <?= ini_get('upload_max_filesize') ?></p>
                    <?php
                    $files = range(0, 0);
                    $fileControl = '';
                    foreach ($files as $file) {
                        $opts = [
                            'class' => 'form-control mb-4',
                            'label' => false,
                            'type' => 'file',
                            'data-type' => 'file',
                        ];
                        echo $this->Form->control('files.' . $file, $opts);
                    }

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('description', $opts);

                    $this->Form->setTemplates($templates);
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                        'default' => $activation,
                    ];
                    $activationControl = $this->Form->control('activation', $opts);

                    $opts = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'datetime',
                        'empty' => true,
                        'default' => $expiration,
                        'templateVars' => ['help' => "Default expiration of $months months."],
                    ];
                    $expirationControl = $this->Form->control('expiration', $opts);
                    $this->Form->resetTemplates();

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4', 'text' => __('Auto Delete on Expiration')],
                        'data-type' => 'boolean',
                        'default' => true,
                    ];
                    $this->Form->switchToCheckboxTemplate("mt-0 mt-md-4");
                    $autoDeleteControl = $this->Form->control('auto_delete', $opts);
                    $this->Form->switchBackTemplates();

                    ?>

                    <div class="row">
                        <div class="col-12 col-md-4"><?= $activationControl ?></div>
                        <div class="col-12 col-md-4"><?= $expirationControl ?></div>
                        <div class="col-12 col-md-4"><?= $autoDeleteControl ?></div>
                    </div>

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
