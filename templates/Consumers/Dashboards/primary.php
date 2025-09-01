<?php
/**
 * @var AppView $this
 */

use App\View\AppView;
use Cake\Chronos\Chronos;

$this->assign('title', 'Primary Dashboard');

?>
<div class="container-xl px-4 mt-5">

    <div class="d-flex justify-content-between align-items-sm-center flex-column flex-sm-row mb-4">
        <div class="me-4 mb-3 mb-sm-0">
            <h1 class="mb-0"><?= APP_NAME ?> Home Page</h1>
            <div class="small">
                <?php
                $datetimeObject = (new Chronos())->setTimezone(LCL_TZ);
                ?>
                <span class="fw-500 text-primary"><?= $datetimeObject->format("l") ?></span>
                · <?= $datetimeObject->format("F j, Y · H:i A") ?>
            </div>
        </div>
    </div>

    <div class="card card-waves mb-4 mt-5">
        <div class="card-body p-5">
            <div class="row align-items-center justify-content-between">
                <div class="col">
                    <h2 class="text-primary">Welcome, your Application is ready!</h2>
                    <p class="text-gray-700">
                        Select an option from the panel on the left and get started.
                    </p>
                    <?php
                    $icon = $this->IconMaker->bootstrapIcon('arrow-right', additionalClasses: 'ms-2');
                    $link = 'Your Profile' . $icon;
                    $url = ['controller' => 'UserManagement', 'action' => 'profile'];
                    $options = [
                        'class' => 'btn btn-primary p-3',
                        'escape' => false
                    ];
                    echo $this->Html->link($link, $url, $options);
                    ?>
                </div>
                <div class="col d-none d-lg-block mt-xxl-n4">
                    <?php
                    $opts = [
                        'class' => 'img-fluid px-xl-4 mt-xxl-n5',
                        'alt' => 'Statistics graphic',
                    ];
                    echo $this->Html->image("/assets/img/illustrations/statistics.svg", $opts);
                    ?>
                </div>
            </div>
        </div>
    </div>

</div>
