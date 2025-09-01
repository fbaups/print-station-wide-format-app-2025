<?php
/**
 * @var AppView $this
 * @var OutputProcessor $outputProcessor
 * @var array $outputProcessorTypes
 * @var array $epsonPresets
 * @var array $epsonPresetsByUser
 */

use App\Model\Entity\OutputProcessor;
use App\View\AppView;

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
