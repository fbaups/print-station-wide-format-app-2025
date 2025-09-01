<?php
/**
 * @var \App\View\AppView $this
 *
 */

$this->assign('title', $this->get('title'));

//control what Libraries are loaded
$coreLib = [
    'base' => false,
    'bootstrap' => false,
    'datatables' => true,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);

?>

<div class="content-app p-4">
    <!-- START OF APPBAR -->
    <div class="app-bar d-flex align-items-center justify-content-between flex-wrap mb-5">
        <div class="app-bar-left">
            <div class="d-flex align-items-center mb-2 gap-3">
                <button type="button" class="icon d-lg-none p-0 text-2xl mb-1 fade-on-hover" id="sidebar-open-btn">
                    <?php
                    $options = [
                        'class' => 'icon',
                        'alt' => '',
                    ];
                    echo $this->Html->image('/interface/assets/images/icons/menu.svg', $options);
                    ?>
                </button>
                <p class="app-bar-ttl mb-0">Dashboard</p>
            </div>
        </div>
    </div>
    <!-- END OF APPBAR -->

    <!-- START OF CONTENT -->

    <!-- END OF CONTENT -->
</div>
