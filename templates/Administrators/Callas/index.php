<?php
/**
 * @var AppView $this
 * @var string $cliVersion
 * @var array $cliStatus
 */

use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __('file-earmark-pdf'));
$this->set('headerTitle', __('Callas pdfToolbox CLI'));
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

<div class="container-fluid px-4">

    <div class="card mb-5">
        <div class="card-header">
            <?= __('Calls pdfToolbox CLI Tasks') ?>
        </div>
        <div class="card-body">
            <div class="background-services index content">
                <p>
                    <?php
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 200px !important"
                    ];
                    echo $this->Html->link(
                        __('Request Activation'),
                        ['action' => 'request-activation',],
                        $options
                    )
                    ?>
                    Request an Activation PDF File
                </p>
                <p>
                    <?php
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 200px !important"
                    ];
                    echo $this->Html->link(
                        __('Request Trial Activation'),
                        ['action' => 'request-trial',],
                        $options
                    )
                    ?>
                    Request a Trial Activation PDF File
                </p>
                <p>
                    <?php
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 200px !important"
                    ];
                    echo $this->Html->link(
                        __('Activate License'),
                        ['action' => 'activate',],
                        $options
                    )
                    ?>
                    License Callas pdfToolbox CLI using an Activation PDF File
                </p>
            </div>
        </div>
    </div>


    <div class="card mb-5">
        <div class="card-header">
            <?= __('Calls pdfToolbox CLI Information') ?>
        </div>
        <div class="card-body">
            <h3>Calls pdfToolbox CLI Version</h3>
            <p><?= $cliVersion ?></p>

            <h3>Calls pdfToolbox CLI Status</h3>
            <pre><?php print_r($cliStatus) ?></pre>
        </div>
    </div>

</div>
