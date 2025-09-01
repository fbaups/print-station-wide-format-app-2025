<?php
/**
 * @var AppView $this
 * @var OutputProcessor $outputProcessor
 * @var array $outputProcessorTypes
 * @var array $epsonPresets
 * @var array $epsonPresetsByUser
 *
 * @var array $pressReadyPdfHotFolders
 * @var array $pressReadyPdfHotFolderOptionsList
 *
 * @var array $pressReadyCsvHotFolders
 * @var array $pressReadyCsvHotFolderOptionsList
 *
 * @var array $pressReadyCsvCompiledHotFolders
 */

use App\Model\Entity\OutputProcessor;
use App\View\AppView;

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
    <?= $this->Form->create($outputProcessor) ?>
    <div class="card">

        <div class="card-header">
            <?= h($outputProcessor->name) ?? "Output Processor Details" ?>
        </div>

        <div class="card-body">
            <div class="outputProcessors form content">
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

                    <?php require_once('add-folder.php') ?>

                    <?php require_once('add-sftp.php') ?>

                    <?php require_once('add-backblaze.php') ?>

                    <?php require_once('add-epsonprintautomate.php') ?>

                    <?php require_once('add-fujifilm-xmf-press-ready-pdf-hot-folders.php') ?>

                    <?php require_once('add-fujifilm-xmf-press-ready-csv-hot-folders.php') ?>

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

                    <?php require_once('add-file-naming-option-builder.php') ?>

                    <?php require_once('add-file-naming-option-prefix.php') ?>

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
        </div>

    </div>
    <?= $this->Form->end() ?>
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

