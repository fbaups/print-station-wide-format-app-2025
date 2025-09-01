<?php
/**
 * @var AppView $this
 * @var OutputProcessor $outputProcessor
 * @var array $outputProcessorTypes
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
use Cake\Utility\Inflector;

?>
<div class="sub-options fujifilm-xmf-press-ready-csv-hot-folders-options d-none">
    <?php
    $opts = [
        'label' => ['text' => 'Press Ready CSV Hot Folder', 'class' => 'form-check-label'],
        'class' => 'form-control my-0',
        'type' => 'select',
        'options' => $pressReadyCsvHotFolderOptionsList,
        'value' => $outputProcessor->parameters['pr-csv-hf-id-wf-id'] ?? '',
        'data-type' => 'string',
    ];
    ?>
    <div class="row mb-4">
        <div class="col-12">
            <?= $this->Form->control('pr-csv-hf-id-wf-id', $opts) ?>
        </div>
        <small class="form-text text-muted mt-1 mb-0">
            The CSV will be placed in the Hot Folder and Press Ready will allocate the CSV record to a Workflow based on
            conditional routing rules.
        </small>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <p class="mb-0">Press Ready CSV Schema</p>
            <div class="form-text text-muted mt-0 mb-2" id="conditional-routing-message">

            </div>
            <?php
            foreach ($pressReadyCsvCompiledHotFolders as $pressReadyCsvCompiledHotFolder) {
                $this->Form->switchToPressReadyCsvTemplate();
                $hf = $pressReadyCsvCompiledHotFolder['jobflow_hotfolder_oid'];
                $hfName = $pressReadyCsvCompiledHotFolder['jobflow_hotfolder_name'];
                $wf = $pressReadyCsvCompiledHotFolder['submit_workflow_oid'];
                $wfName = $pressReadyCsvCompiledHotFolder['workflow_name'];
                $htmlId = "{$hf}-{$wf}";
                $cssClass = "csv-schema csv-schema-{$hf} csv-schema-{$hf}-{$wf}";
                ?>
                <div class="row mx-5">
                    <div class="col-12">

                        <div class="row m-0 p-0 csv-schema-description <?= $cssClass ?> text-black">
                            <div class="col-12 m-0 p-0">
                                [Hot Folder] <?= $hfName ?> - [Workflow] <?= $wfName ?>
                            </div>
                        </div>
                        <?php
                        $pressReadyCsvCompiledHotFolderChecksum = $pressReadyCsvCompiledHotFolder['csv_parse_rule_checksum'] ?? '';
                        $workflowHotFolderChecksum = $outputProcessor->parameters['pr-csv-hf-schema'][$hf][$wf]['checksum'] ?? '';

                        if (!empty($workflowHotFolderChecksum)) {
                            if ($workflowHotFolderChecksum !== $pressReadyCsvCompiledHotFolderChecksum) {
                                echo '<div class="row mb-0"><div class="col-12"><div class="alert alert-warning pt-2 pb-1">
                                            The Hot Folder or Workflow settings have changed in Fujifilm XMF Press Ready.
                                            Please double-check all field values below.
                                            </div></div></div>';
                            }
                        }
                        ?>
                        <div class="row csv-schema-headings <?= $cssClass ?> pt-2 d-none">
                            <div class="col-sm-3 col-lg-2 text-black">Field Name</div>
                            <div class="col-sm-7 col-lg-8 text-black">Field Value</div>
                            <div class="col-sm-2 col-lg-2 text-black mt-1">Data Type</div>
                        </div>
                        <div id="<?= $htmlId ?>" class="row mb-2 csv-schema-fields <?= $cssClass ?> d-none">
                            <?php
                            //checksum of fields
                            $fieldName = "pr-csv-hf-schema.{$hf}.{$wf}.checksum";
                            $opts = [
                                'class' => 'form-control form-control-sm mb-2',
                                'value' => $pressReadyCsvCompiledHotFolderChecksum,
                            ];
                            echo $this->Form->hidden($fieldName, $opts);


                            //fields
                            foreach ($pressReadyCsvCompiledHotFolder['csv_schema'] as $item) {
                                $columnName = $item['columnNameSlug'];
                                $fieldName = "pr-csv-hf-schema.{$hf}.{$wf}.{$columnName}";

                                $opts = [
                                    'class' => 'form-control form-control-sm mb-2',
                                    'label' => ['class' => 'form-check form-label-sm mt-1 mb-0 ps-0', 'text' => $item['columnName']],
                                    'value' => $outputProcessor->parameters['pr-csv-hf-schema'][$hf][$wf][$columnName] ?? '',
                                    'data-type' => $item['dataType'],
                                    'templateVars' => ['dataType' => $item['dataType']],
                                ];

                                echo $this->Form->control($fieldName, $opts);
                            }
                            ?>
                            <pre><?php //print_r($pressReadyCsvCompiledHotFolder) ?></pre>
                        </div>

                    </div>
                </div>
                <?php
                $this->Form->switchBackTemplates();
            }
            ?>
        </div>
    </div>
</div>
