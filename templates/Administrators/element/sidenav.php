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

                <?= $this->element('sidenav_debug_user_session') ?>

                <?= $this->element('sidenav_dashboards') ?>

                <?= $this->element('sidenav_users') ?>

                <?= $this->element('sidenav_app_data') ?>

                <?= $this->element('sidenav_media_storage') ?>

                <?= $this->element('sidenav_automation') ?>

                <?php
                if ($this->AuthUser) {
                    if ($this->AuthUser->hasRoles(['superadmin', 'admin'])) {
                        $Session = $this->request->getSession();
                        $xmpCredentialsCount = ($Session->read('IntegrationCredentials.XMPie-uProduce.count'));
                        if ($xmpCredentialsCount > 0) {
                            echo $this->element('sidenav_xmpie');
                        }
                    }
                }
                ?>

                <?= $this->element('sidenav_messaging') ?>

                <?php
                if ($this->AuthUser) {
                    if ($this->AuthUser->hasRoles(['superadmin', 'admin'])) {
                        echo $this->element('sidenav_system_admin');
                        echo $this->element('sidenav_security');
                        echo $this->element('sidenav_release_builder', [], ['ignoreMissing' => true]);
                        echo $this->element('sidenav_developer', [], ['ignoreMissing' => true]);
                    }
                }
                ?>

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
