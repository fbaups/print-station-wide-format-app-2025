<?php

use Cake\Routing\Router;

?>
<div>
    <!-- START OF SIDEBAR -->
    <div class="content-sidebar">
        <div class="sidebar-top d-flex">
            <div class="d-inline-flex align-items-center gap-3 justify-content-between"></div>
        </div>
        <div class="sidebar-body d-flex flex-column">

            <button type="button" class="btn p-0 icon text-2xl border-0 d-lg-none fade-on-hover" id="sidebar-close-btn">
                <?php
                $options = [
                    'class' => 'icon-16',
                    'alt' => '',
                ];
                echo $this->Html->image('/interface/assets/images/icons/close.svg', $options);
                ?>
            </button>

            <div class="sidebar-menu font-poppins">

                <div class="sidebar-menu-item">
                    <div class="menu-item-head">Jobs</div>
                    <div class="menu-item-list">
                        <div class="menu-item">
                            <?php
                            $url = Router::url(['controller' => 'production-queue', 'action' => 'index'])
                            ?>
                            <a href="<?= $url ?>" class="menu-link d-flex align-items-center">
                                <span class="menu-link-icon d-flex align-items-center justify-content-center">
                                    <?php
                                    $options = [
                                        'class' => '',
                                    ];
                                    echo $this->Html->image('/interface/assets/images/icons/briefcase.svg', $options);
                                    ?>
                                </span>
                                <span class="menu-link-text text-sm">Job Queue</span>
                            </a>
                        </div>
                        <div class="menu-item d-none">
                            <?php
                            $url = Router::url(['controller' => 'production-logs', 'action' => 'index'])
                            ?>
                            <a href="<?= $url ?>" class="menu-link d-flex align-items-center">
                                <span class="menu-link-icon d-flex align-items-center justify-content-center">
                                    <?php
                                    $options = [
                                        'class' => '',
                                    ];
                                    echo $this->Html->image('/interface/assets/images/icons/logs.svg', $options);
                                    ?>
                                </span>
                                <span class="menu-link-text text-sm">Job Logs</span>
                            </a>
                        </div>
                        <div class="menu-item d-none">
                            <?php
                            $url = Router::url(['controller' => 'production-dashboard', 'action' => 'index'])
                            ?>
                            <a href="<?= $url ?>" class="menu-link d-flex align-items-center">
                                <span class="menu-link-icon d-flex align-items-center justify-content-center">
                                    <?php
                                    $options = [
                                        'class' => '',
                                    ];
                                    echo $this->Html->image('/interface/assets/images/icons/box_grid.svg', $options);
                                    ?>
                                </span>
                                <span class="menu-link-text text-sm">Dashboard</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu-item d-none">
                    <div class="menu-item-head">System</div>
                    <div class="menu-item-list">
                        <div class="menu-item">
                            <?php
                            $url = Router::url(['controller' => 'production-settings', 'action' => 'index'])
                            ?>
                            <a href="<?= $url ?>" class="menu-link d-flex align-items-center">
                                <span class="menu-link-icon d-flex align-items-center justify-content-center">
                                    <?php
                                    $options = [
                                        'class' => '',
                                    ];
                                    echo $this->Html->image('/interface/assets/images/icons/setting.svg', $options);
                                    ?>
                                </span>
                                <span class="menu-link-text text-sm">Settings</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <?php
                            $url = Router::url(['controller' => 'docs', 'action' => 'index'])
                            ?>
                            <a href="<?= $url ?>" class="menu-link d-flex align-items-center" target="_blank">
                                <span class="menu-link-icon d-flex align-items-center justify-content-center">
                                    <?php
                                    $options = [
                                        'class' => '',
                                    ];
                                    echo $this->Html->image('/interface/assets/images/icons/support.svg', $options);
                                    ?>
                                </span>
                                <span class="menu-link-text text-sm">Help</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="d-flex flex-column align-items-start sidebar-bottom">
            <p class="text-xs text-gray mb-0">
                Powered By
                <br>
                Fujifilm Business Innovation Australia
                <br>
                GC Professional Services
            </p>
        </div>
    </div>
    <!-- END OF SIDEBAR -->
</div>
