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

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Host'],
                    ];
                    $sftp_host = $this->Form->control('parameters.sftp_host', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Port'],
                    ];
                    $sftp_port = $this->Form->control('parameters.sftp_port', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Username'],
                    ];
                    $sftp_username = $this->Form->control('parameters.sftp_username', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'password',
                        'label' => ['text' => 'Password'],
                    ];
                    $sftp_password = $this->Form->control('parameters.sftp_password', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'select',
                        'options' => [
                            2 => '2 Seconds',
                            4 => '4 Seconds',
                            6 => '6 Seconds',
                            8 => '8 Seconds',
                            10 => '10 Seconds',
                        ],
                        'label' => ['text' => 'Timeout'],
                    ];
                    $sftp_timeout = $this->Form->control('parameters.sftp_timeout', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Path'],
                    ];
                    $sftp_path = $this->Form->control('parameters.sftp_path', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'textarea',
                        'label' => ['text' => 'Private Key'],
                    ];
                    $sftp_privateKey = $this->Form->control('parameters.sftp_privateKey', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'textarea',
                        'label' => ['text' => 'Public Key'],
                    ];
                    $sftp_publicKey = $this->Form->control('parameters.sftp_publicKey', $opts);

                    $opts_sftp_http_host = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'HTTP Host'],
                        'templateVars' => ['help' => 'If provided, files uploaded to the sFTP Server can be read back via this URL.'],
                    ];
                    $this->Form->setTemplates($templates);
                    $sftp_http_host = $this->Form->control('parameters.http_host', $opts_sftp_http_host);
                    $this->Form->resetTemplates();
                    ?>

                    <div class="sftp-options">
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $sftp_host ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $sftp_port ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $sftp_username ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $sftp_password ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $sftp_timeout ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $sftp_path ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-6 col-xxl-6">
                                <?= $sftp_privateKey ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-6 col-xxl-6">
                                <?= $sftp_publicKey ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <?= $sftp_http_host ?>
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
