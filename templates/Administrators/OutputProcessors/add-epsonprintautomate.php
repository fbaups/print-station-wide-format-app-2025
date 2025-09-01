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
