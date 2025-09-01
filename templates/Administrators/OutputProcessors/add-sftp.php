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
