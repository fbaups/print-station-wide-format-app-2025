<?php
/**
 * @var AppView $this
 * @var IntegrationCredential $integrationCredential
 */

use App\Model\Entity\IntegrationCredential;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\View\AppView;

$BIC = new BaseIntegrationCredentials();
$icTypes = $BIC->getIntegrationTypes();

if (isset($integrationCredential->parameters) && !empty($integrationCredential->parameters)) {
    $addEditText = 'Edit';
} else {
    $addEditText = 'Add';
}

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('{0} {1} Integration Credential', $addEditText, $icTypes[$integrationCredential->type]));
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
    <?= $this->Html->link(__('&larr; Back to Integration Credentials'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <?= $this->Form->create($integrationCredential) ?>
    <div class="card">

        <div class="card-header">
            <?= h($integrationCredential->name) ?? "Integration Credential Details" ?>
        </div>

        <div class="card-body">
            <div class="integrationCredentials form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'type' => 'hidden',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('type', $opts);

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

                    $opts_uproduce_host = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'uProduce Host'],
                        'templateVars' => ['help' => 'HTTPS URL of the uProduce Server'],
                    ];
                    $this->Form->setTemplates($templates);
                    $uproduce_host = $this->Form->control('parameters.uproduce_host', $opts_uproduce_host);
                    $this->Form->resetTemplates();

                    $opts = [
                        'class' => 'form-control mb-0',
                        'label' => ['text' => 'SSL Validation'],
                        'data-type' => 'select',
                        'type' => 'select',
                        'options' => [
                            1 => 'Strict - Certificates must be from a valid CA',
                            0 => 'Relaxed - Allow self-signed certificates',
                        ]
                    ];
                    $relax_ssl_validation = $this->Form->control('parameters.ssl_validation', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'uProduce Admin Username'],
                    ];
                    $uproduce_admin_username = $this->Form->control('parameters.uproduce_admin_username', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'password',
                        'label' => ['text' => 'uProduce Admin Password'],
                    ];
                    $uproduce_admin_password = $this->Form->control('parameters.uproduce_admin_password', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Username'],
                    ];
                    $uproduce_username = $this->Form->control('parameters.uproduce_username', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'password',
                        'label' => ['text' => 'Password'],
                    ];
                    $uproduce_password = $this->Form->control('parameters.uproduce_password', $opts);
                    ?>

                    <div class="xmpie-uproduce-options">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <?= $uproduce_host ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $relax_ssl_validation ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <?= $uproduce_admin_username ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $uproduce_admin_password ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <?= $uproduce_username ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $uproduce_password ?>
                            </div>
                        </div>
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
                echo $this->Html->link(__('Back'), ['controller' => 'integrationCredentials'], $options);

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
