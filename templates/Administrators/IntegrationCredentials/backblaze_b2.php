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

                    $opts_b2_key_id = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Key ID'],
                    ];
                    $b2_key_id = $this->Form->control('parameters.b2_key_id', $opts_b2_key_id);

                    $opts_b2_key = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'password',
                        'label' => ['text' => 'Key'],
                    ];
                    $b2_key = $this->Form->control('parameters.b2_key', $opts_b2_key);

                    $opts_b2_path = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'type' => 'text',
                        'label' => ['text' => 'Path'],
                    ];
                    $b2_path = $this->Form->control('parameters.b2_path', $opts_b2_path);

                    $this->Form->setTemplates($templates);

                    $opts_b2_bucket = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'text',
                        'type' => 'text',
                        'disabled' => 'disabled',
                        'label' => ['text' => 'Bucket ID'],
                        'templateVars' => ['help' => 'Determined automatically.'],
                    ];
                    $b2_bucket = $this->Form->control('parameters.b2_bucket', $opts_b2_bucket);

                    $opts_b2_bucket_name = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'text',
                        'type' => 'text',
                        'disabled' => 'disabled',
                        'label' => ['text' => 'Bucket Name'],
                        'templateVars' => ['help' => 'Determined automatically.'],
                    ];
                    $b2_bucket_name = $this->Form->control('parameters.b2_bucket_name', $opts_b2_bucket_name);

                    $opts_b2_http_host = [
                        'class' => 'form-control mb-0',
                        'data-type' => 'text',
                        'type' => 'text',
                        'disabled' => 'disabled',
                        'label' => ['text' => 'HTTP Host'],
                        'templateVars' => ['help' => 'Determined automatically. Files uploaded to the B2 Bucket can be read back via this URL.'],
                    ];
                    $b2_http_host = $this->Form->control('parameters.http_host', $opts_b2_http_host);

                    $this->Form->resetTemplates();
                    ?>

                    <div class="sftp-options">
                        <div class="row">
                            <div class="col-12 col-md-4 col-xxl-4">
                                <?= $b2_key_id ?>
                            </div>
                            <div class="col-12 col-md-4 col-xxl-4">
                                <?= $b2_key ?>
                            </div>
                            <div class="col-12 col-md-4 col-xxl-4">
                                <?= $b2_path ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 col-xxl-6">
                                <?= $b2_bucket_name ?>
                            </div>
                            <div class="col-12 col-md-6 col-xxl-6">
                                <?= $b2_bucket ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <?= $b2_http_host ?>
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
