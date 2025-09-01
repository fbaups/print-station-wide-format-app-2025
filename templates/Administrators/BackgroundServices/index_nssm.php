<?php
/**
 * @var AppView $this
 * @var array $services
 */

use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __('house-gear'));
$this->set('headerTitle', __('Background Services'));
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
    <div class="card">

        <div class="card-header">
            <?= __('Important Information') ?>
        </div>

        <div class="card-body">
            <div class="background-services index content">
                <p>
                    To enable Background Services, <?= APP_NAME ?> needs to download and use a 'service manager'
                    application. <?= APP_NAME ?> uses <a href="https://nssm.cc/">NSSM</a> as the service manager and
                    you can find more information here <a href="https://nssm.cc/">https://nssm.cc/</a>.
                </p>
                <p>
                    If you choose to use Background Services, you agree to the terms and conditions of NSSM located
                    <a href="https://nssm.cc/download">here</a>.
                </p>
                <p>
                    <?php
                    $options = [
                        'class' => "btn btn-primary"
                    ];
                    echo $this->Html->link(
                        __('Download and Use NSSM'),
                        ['action' => 'download-nssm',],
                        $options
                    )
                    ?>
                </p>
            </div>
        </div>

    </div>
</div>
