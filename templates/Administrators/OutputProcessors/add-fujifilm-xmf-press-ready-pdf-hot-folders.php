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

?>
<div class="sub-options fujifilm-xmf-press-ready-pdf-hot-folders-options d-none">
    <?php
    $opts = [
        'label' => ['text' => 'Press Ready PDF Hot Folder', 'class' => 'form-check-label'],
        'class' => 'form-control my-0',
        'type' => 'select',
        'options' => $pressReadyPdfHotFolderOptionsList,
        'value' => $outputProcessor->parameters['pr-pdf-hf-id-wf-id'] ?? '',
        'data-type' => 'string',
    ];
    ?>
    <div class="row mb-4">
        <div class="col-12">
            <?= $this->Form->control('pr-pdf-hf-id-wf-id', $opts) ?>
        </div>
        <small class="form-text text-muted mt-1 mb-0">
            The PDF will be placed in the Hot Folder and Press Ready will allocate the PDF to a Workflow based on
            conditional routing rules.
        </small>
    </div>
</div>
