<?php
/**
 * @var App\View\AppView $this
 */

use Cake\Utility\Inflector;

?>
<div id="layoutSidenav_nav">
    <nav class="sidenav shadow-right sidenav-light">
        <div class="sidenav-menu">
            <div class="nav accordion" id="accordionSidenav">

                <?= $this->element('sidenav_dashboards') ?>

                <?= $this->element('sidenav_users') ?>

                <?= $this->element('sidenav_app_data') ?>

            </div>
        </div>
        <!-- Sidenav Footer-->
        <div class="sidenav-footer">
            <div class="sidenav-footer-content">
                <?php
                $roleGroups = $this->AuthUser->user('role_groupings_list');
                if ($roleGroups && isset($roleGroups[0])) {
                    $privilege = Inflector::singularize($roleGroups[0]);
                    $privilegeText = "Logged in as {$privilege}:";
                } else {
                    $privilegeText = "Logged in as:";
                }
                ?>
                <div class="sidenav-footer-subtitle"><?= $privilegeText ?></div>
                <div class="sidenav-footer-title">
                    <?php
                    if ($this->AuthUser) {
                        echo $this->AuthUser->getFulName();
                    }
                    ?>
                </div>
            </div>
        </div>
    </nav>
</div>
