<?php
/**
 * @var App\View\AppView $this
 */
?>
<div id="layoutSidenav_nav">
    <nav class="sidenav shadow-right sidenav-light">
        <div class="sidenav-menu">
            <div class="nav accordion" id="accordionSidenav">

                <?= $this->element('sidenav_dashboards') ?>

                <?= $this->element('sidenav_users') ?>

                <?= $this->element('sidenav_app_data') ?>

                <?= $this->element('sidenav_automation') ?>

                <?= $this->element('sidenav_messaging') ?>

                <?php
                if ($this->AuthUser) {
                    if ($this->AuthUser->hasRoles(['superadmin', 'admin'])) {
                        echo $this->element('sidenav_system_admin');
                    }
                }
                ?>

                <?php
                if ($this->AuthUser) {
                    if ($this->AuthUser->hasRoles(['superadmin'])) {
                        echo $this->element('sidenav_security');
                    }
                }
                ?>

                <?php
                if ($this->AuthUser) {
                    if ($this->AuthUser->hasRoles(['superadmin'])) {
                        echo $this->element('sidenav_developer', [], ['ignoreMissing' => true]);
                    }
                }
                ?>

            </div>
        </div>
        <!-- Sidenav Footer-->
        <div class="sidenav-footer">
            <div class="sidenav-footer-content">
                <div class="sidenav-footer-subtitle">Logged in as:</div>
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
