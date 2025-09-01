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
