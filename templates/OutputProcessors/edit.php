<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OutputProcessor $outputProcessor
 * @var array $outputProcessorTypes
 * @var array $epsonPresets
 * @var array $epsonPresetsByUser
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Output Processor'));
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
    <?= $this->Html->link(__('&larr; Back to Output Processors'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($outputProcessor->name) ?? "Output Processor Details" ?>
        </div>

        <div class="card-body">
            <div class="outputProcessors form content">
                <?= $this->Form->create($outputProcessor) ?>
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
                        'class' => 'form-control mb-4',
                        'type' => 'select',
                        'options' => $outputProcessorTypes,
                        'empty' => false,
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('type', $opts);
                    ?>

                    <div class="sub-options folder-options d-none">
                        <?php
                        $opts = [
                            'label' => ['text' => 'Output Folder Path', 'class' => 'form-check-label'],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['fso_path'] ?? '',
                            'data-type' => 'string',
                        ];
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <?= $this->Form->control('fso-path', $opts) ?>
                            </div>
                        </div>
                    </div>

                    <div class="sub-options sftp-options d-none">
                        <?php
                        $opts_sftp_host = [
                            'label' => ['text' => 'sFTP Host', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['sftp_host'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_sftp_port = [
                            'label' => ['text' => 'sFTP Port', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['sftp_port'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_sftp_username = [
                            'label' => ['text' => 'sFTP Username', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['sftp_username'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_sftp_password = [
                            'label' => ['text' => 'sFTP Password', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'type' => 'password',
                            'value' => $outputProcessor->parameters['sftp_password'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_sftp_timeout = [
                            'label' => ['text' => 'sFTP Timeout', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'type' => 'select',
                            'options' => [
                                2 => '2 Seconds',
                                4 => '4 Seconds',
                                6 => '6 Seconds',
                                8 => '8 Seconds',
                                10 => '10 Seconds',
                            ],
                            'value' => $outputProcessor->parameters['sftp_timeout'] ?? 6,
                            'data-type' => 'string',
                        ];
                        $opts_sftp_path = [
                            'label' => ['text' => 'sFTP Path', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['sftp_path'] ?? '',
                            'data-type' => 'string',
                        ];

                        ?>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $this->Form->control('sftp-host', $opts_sftp_host) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $this->Form->control('sftp-port', $opts_sftp_port) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $this->Form->control('sftp-username', $opts_sftp_username) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $this->Form->control('sftp-password', $opts_sftp_password) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $this->Form->control('sftp-timeout', $opts_sftp_timeout) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
                                <?= $this->Form->control('sftp-path', $opts_sftp_path) ?>
                            </div>
                        </div>
                    </div>

                    <div class="sub-options backblaze-options d-none">
                        <?php
                        $opts_b2_key_id = [
                            'label' => ['text' => 'Key ID', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['b2_key_id'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_b2_key = [
                            'label' => ['text' => 'Key', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'type' => 'password',
                            'value' => $outputProcessor->parameters['b2_key'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_b2_bucket = [
                            'label' => ['text' => 'Bucket ID', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['b2_bucket'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_b2_path = [
                            'label' => ['text' => 'Path', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'value' => $outputProcessor->parameters['b2_path'] ?? '',
                            'data-type' => 'string',
                        ];
                        ?>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-3">
                                <?= $this->Form->control('b2-key-id', $opts_b2_key_id) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <?= $this->Form->control('b2-key', $opts_b2_key) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <?= $this->Form->control('b2-bucket', $opts_b2_bucket) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-3">
                                <?= $this->Form->control('b2-path', $opts_b2_path) ?>
                            </div>
                        </div>
                    </div>

                    <div class="sub-options epsonprintautomate-options d-none">
                        <?php
                        $presetNames = [];
                        foreach ($epsonPresets as $epsonPreset) {
                            $presetNames[$epsonPreset['PresetName']] = $epsonPreset['PresetName'];
                        }
                        $opts_epa_preset = [
                            'label' => ['text' => 'Epson Print Automate Preset', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'type' => 'select',
                            'options' => $epsonPresetsByUser,
                            'value' => $outputProcessor->parameters['epa_preset'] ?? 6,
                            'data-type' => 'string',
                        ];
                        $opts_epa_username = [
                            'label' => ['text' => 'Windows Username', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'readonly' => 'readonly',
                            'value' => $outputProcessor->parameters['epa_username'] ?? '',
                            'data-type' => 'string',
                        ];
                        $opts_epa_password = [
                            'label' => ['text' => 'Windows Password', 'class' => ''],
                            'class' => 'form-control mb-4',
                            'type' => 'password',
                            'value' => $outputProcessor->parameters['epa_password'] ?? '',
                            'data-type' => 'string',
                        ];

                        ?>
                        <div class="row">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <?= $this->Form->control('epa-preset', $opts_epa_preset) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <?= $this->Form->control('epa-username', $opts_epa_username) ?>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <?= $this->Form->control('epa-password', $opts_epa_password) ?>
                            </div>
                        </div>
                    </div>

                    <div class="file-naming-options">
                        <?php
                        $customFilenameOptions = [
                            'original' => 'None - Use the Original File Name',
                            'prefix' => 'Add a Prefix to the File Name',
                            'builder' => 'Custom File Name',
                        ];

                        $optsCustomFilename = [
                            'label' => ['text' => 'File Name Options', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $customFilenameOptions,
                            'value' => $outputProcessor->parameters['filenameOptions'] ?? 'original',
                            'data-type' => 'string',
                        ];
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <?= $this->Form->control('file-naming-options', $optsCustomFilename) ?>
                            </div>
                        </div>
                    </div>

                    <div id="file-naming-option-builder"
                         class="d-none file-naming-option row border mt-3 ms-0 me-0 p-2 pb-3">
                        <div class="col-12">
                            <h5 class="mb-2 pt-2">Custom File Name Builder</h5>
                            <p class="text-muted mb-0">At minimum use {{DocumentFileName}}.{{DocumentFileExtension}} to
                                output
                                as the original filename.</p>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <?= $this->Html->outputProcessorFileBuilderSpan('OrderID') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('JobID') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DocumentID') ?>
                                    <br>

                                    <?= $this->Html->outputProcessorFileBuilderSpan('ExternalOrderNumber') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('ExternalJobNumber') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('ExternalDocumentNumber') ?>
                                    <br>

                                    <?= $this->Html->outputProcessorFileBuilderSpan('OrderName') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('JobName') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DocumentName') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DocumentFileName') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DocumentFileExtension') ?>
                                    <br>

                                    <?= $this->Html->outputProcessorFileBuilderSpan('OrderDescription') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('JobDescription') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DocumentDescription') ?>
                                    <br>

                                    <?= $this->Html->outputProcessorFileBuilderSpan('Counter') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('RandomNumber6') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('RandomString6') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('GUID') ?>
                                    <br>

                                    <?= $this->Html->outputProcessorFileBuilderSpan('DateTimeStamp') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DateStamp') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('TimeStamp') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('YearNumber') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('MonthNumber') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DayNumber') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('MonthName') ?>
                                    <?= $this->Html->outputProcessorFileBuilderSpan('DayName') ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <?php
                                    $optsPrefix = [
                                        'label' => ['text' => 'Filename Builder', 'class' => 'form-check-label'],
                                        'class' => 'form-control mb-0',
                                        'value' => !empty($outputProcessor->parameters['filenameBuilder']) ? $outputProcessor->parameters['filenameBuilder'] : '',
                                        'data-type' => 'string',
                                    ];
                                    echo $this->Form->control('filename-builder', $optsPrefix);
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="file-naming-option-prefix"
                         class="d-none file-naming-option row border mt-3 ms-0 me-0 p-2 pb-3">
                        <?php
                        $trueFalseOptions = [
                            1 => 'Yes',
                            0 => 'No',
                        ];

                        $optsOrder = [
                            'label' => ['text' => 'Order ID', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $trueFalseOptions,
                            'value' => $outputProcessor->parameters['prefixOrderId'],
                            'data-type' => 'bool',
                        ];

                        $optsJob = [
                            'label' => ['text' => 'Job ID', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $trueFalseOptions,
                            'value' => $outputProcessor->parameters['prefixJobId'],
                            'data-type' => 'bool',
                        ];

                        $optsDocument = [
                            'label' => ['text' => 'Document ID', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $trueFalseOptions,
                            'value' => $outputProcessor->parameters['prefixDocumentId'],
                            'data-type' => 'bool',
                        ];

                        $optsExternalOrder = [
                            'label' => ['text' => 'External Order Number', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $trueFalseOptions,
                            'value' => $outputProcessor->parameters['prefixExternalOrderNumber'],
                            'data-type' => 'bool',
                        ];

                        $optsExternalJob = [
                            'label' => ['text' => 'External Job Number', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $trueFalseOptions,
                            'value' => $outputProcessor->parameters['prefixExternalJobNumber'],
                            'data-type' => 'bool',
                        ];

                        $optsExternalDocument = [
                            'label' => ['text' => 'External Document Number', 'class' => 'form-check-label'],
                            'class' => 'form-control',
                            'type' => 'select',
                            'options' => $trueFalseOptions,
                            'value' => $outputProcessor->parameters['prefixExternalDocumentNumber'],
                            'data-type' => 'bool',
                        ];
                        ?>
                        <div class="col-12">
                            <h5 class="mb-2 pt-2">Order/Job/Document Prefixes</h5>
                            <div class="row">
                                <div class="col-12">
                                    <div class="row">
                                        <p class="text-muted col-12 m-0">
                                            Prefix the filename with the <?= APP_NAME ?> Order/Job/Document ID.
                                        </p>
                                        <div class="col-12 col-md-4 col-xl-4">
                                            <?= $this->Form->control('prefix-order-id', $optsOrder) ?>
                                        </div>
                                        <div class="col-12 col-md-4 col-xl-4">
                                            <?= $this->Form->control('prefix-job-id', $optsJob) ?>
                                        </div>
                                        <div class="col-12 col-md-4 col-xl-4">
                                            <?= $this->Form->control('prefix-document-id', $optsDocument) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="row">
                                        <p class="text-muted col-12 m-0">
                                            Prefix the filename with the External Order/Job/Document number.
                                        </p>
                                        <div class="col-12 col-md-4 col-xl-4">
                                            <?= $this->Form->control('prefix-external-order-number', $optsExternalOrder) ?>
                                        </div>
                                        <div class="col-12 col-md-4 col-xl-4">
                                            <?= $this->Form->control('prefix-external-job-number', $optsExternalJob) ?>
                                        </div>
                                        <div class="col-12 col-md-4 col-xl-4">
                                            <?= $this->Form->control('prefix-external-document-number', $optsExternalDocument) ?>
                                        </div>
                                    </div>
                                </div>
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
                echo $this->Html->link(__('Back'), ['controller' => 'outputProcessors'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
            <?= $this->Form->end() ?>
        </div>

    </div>
</div>

<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');

echo $this->Html->script('output_processor');

?>
<script>
    $(document).ready(function () {
        OutputProcessor.formEditor();
    });
</script>
<?php
$this->end();
?>

