<?php
/**
 * @var \App\View\AppView $this
 * @var int $cycles
 * @var int $sizeMb
 * @var array $performance
 * @var array $repoCheckResult
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Repository Performance'));
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

    <?php
    $showAlert = false;
    if ($this->AuthUser->hasRole('superadmin')) {
        $editRepoSettingsUrl = ['controller' => 'settings', 'action' => 'edit-group', 'repository'];
        $showAlert = false;

        if (!$repoCheckResult['isURL']) {
            $showAlert = true;
        }

        if (!$repoCheckResult['isSFTP'] && !$repoCheckResult['isUNC']) {
            $showAlert = true;
        }

        if ($showAlert) {
            ?>
            <div class="alert alert-danger">
                <?php echo __("Could not connect to the Repository. Please configure the") ?>
                <?php echo $this->Html->link("Repository Settings", $editRepoSettingsUrl) ?>.
            </div>
            <?php
        }
    }
    ?>

    <?php
    if (!$showAlert) {
        ?>

        <div class="card mb-5 col-md-8 col-xxl-6 ms-auto me-auto">
            <div class="card-header">
                <?= __('Test Parameters') ?>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="form large-9 medium-8 columns content pt-1 pb-3 pl-3 pr-3">
                        <?php
                        $formOpts = [
                        ];

                        $labelClass = 'form-control-label';
                        $inputClass = 'form-control mb-4';
                        $checkboxClass = 'mr-2 mb-4';

                        $defaultOptions = [
                            'label' => [
                                'class' => $labelClass,
                            ],
                            'options' => null,
                            'class' => $inputClass,
                            'type' => 'select'
                        ];

                        ?>
                        <?= $this->Form->create(null, $formOpts) ?>
                        <fieldset>
                            <?php
                            $cyclesOptions = $defaultOptions;
                            $cyclesOptions['options'] = [
                                10 => '10 Cycles',
                                20 => '20 Cycles',
                                30 => '30 Cycles',
                                40 => '40 Cycles',
                                50 => '50 Cycles',
                                100 => '100 Cycles',
                                200 => '200 Cycles',
                                300 => '300 Cycles',
                                500 => '500 Cycles',
                            ];
                            $cyclesOptions['default'] = $cycles;

                            $sizeOptions = $defaultOptions;
                            $sizeOptions['options'] = [

                                '0.1' => '0.1 Mb',
                                '0.2' => '0.2 Mb',
                                '0.3' => '0.3 Mb',
                                '0.4' => '0.4 Mb',
                                '0.5' => '0.5 Mb',
                                '1' => '1 Mb',
                                '2' => '2 Mb',
                                '3' => '3 Mb',
                                '4' => '4 Mb',
                                '5' => '5 Mb',
                                '10' => '10 Mb',
                            ];
                            $sizeOptions['default'] = $sizeMb;

                            echo $this->Form->control('cycles', $cyclesOptions);
                            echo $this->Form->control('sizeMb', $sizeOptions);
                            ?>

                            <?= $this->Html->link(__('Cancel'), ['controller' => 'load-tests'], ['class' => 'btn btn-secondary float-left']) ?>
                            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary float-right']) ?>
                            <?= $this->Form->end() ?>
                        </fieldset>
                    </div>
                </div>

            </div>
        </div>


        <?php
        if (isset($performance)) {
            ?>
            <div class="card mb-5 col-md-8 col-xxl-6 ms-auto me-auto">
                <div class="card-header">
                    <?= __('Test Results') ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg ml-auto mr-auto">
                        <pre><?php
                            if (isset($performance)) {
                                print_r($performance);
                            }
                            ?></pre>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }//$performance
        ?>

        <?php
    }//$showAlert
    ?>

</div>
