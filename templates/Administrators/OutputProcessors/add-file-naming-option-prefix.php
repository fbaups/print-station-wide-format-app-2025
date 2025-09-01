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
<div id="file-naming-option-prefix" class="d-none file-naming-option row border mt-3 ms-0 me-0 p-2 pb-3">
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
