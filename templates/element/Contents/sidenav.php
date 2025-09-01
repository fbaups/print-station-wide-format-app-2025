<?php
/**
 * @var App\View\AppView $this
 */
?>
<div id="layoutSidenav_nav">
    <nav class="sidenav shadow-right sidenav-light">
        <div class="sidenav-menu">
            <div class="nav accordion" id="accordionSidenav">

                <?= $this->element('Contents/sidenav_help_pages') ?>

                <?= $this->element('Contents/sidenav_policy_pages') ?>
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
