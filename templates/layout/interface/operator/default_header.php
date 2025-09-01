<div class="page-header bg-sea-green d-flex align-items-center justify-content-between gap-2">
    <div class="site-name-wrapper d-flex align-items-lg-center flex-wrap">
        <h2 class="site-name mb-0">
            <span>
                <?php
                $options = [
                    'class' => 'logo-light-mode',
                ];
                echo $this->Html->image('/interface/assets/images/fujifilm_white_negative.svg', $options);
                $options = [
                    'class' => 'logo-dark-mode',
                ];
                echo $this->Html->image('/interface/assets/images/fujifilm_white_negative.svg', $options);
                ?>
            </span>
        </h2>
        <div class="vert-line"></div>
    </div>
    <div>
        <p class="m-0 text-white text-xl font-semibold d-none d-xl-inline-flex">
            <?= APP_NAME ?>
        </p>
    </div>
    <div class="page-header-right d-flex align-items-center">
        <div class="page-header-switch custom-switch d-inline-flex align-items-center">
            <label class="switch">
                <input type="checkbox" id="modeToggle">
                <span class="slider round"></span>
            </label>
        </div>

        <div class="horiz-sep-line"></div>

        <div class="d-flex align-items-center">
            <?php
            //extra menu if needed
            if (1 === 2) {
                ?>
                <div class="toolbar d-flex align-items-center">
                    <div class="dropdown customized-dropdown">
                        <div
                            class="dropdown-toggle d-flex align-items-center cursor-pointer fade-on-hover border-radius-100vh"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="rounded-circle overflow-hidden">
                                <?php
                                $options = [
                                    'class' => '',
                                    'alt' => 'Import Data',
                                ];
                                echo $this->Html->image('/interface/assets/images/icons/setting.svg', $options);
                                ?>
                            </div>
                        </div>
                        <ul class="dropdown-menu">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center">
                                    <span class="dropdown-item-text">Clear completed jobs</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="horiz-sep-line"></div>
                <?php
            }
            ?>

            <div class="dropdown profile-dropdown">
                <div
                    class="dropdown-toggle d-flex align-items-center cursor-pointer fade-on-hover p-2 border-radius-100vh"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="rounded-circle overflow-hidden">
                        <?php
                        $options = [
                            'class' => 'icon-24',
                            'alt' => '',
                        ];
                        echo $this->Html->image('/interface/assets/images/icons/user.svg', $options);
                        ?>
                    </div>
                    <span class="dropdown-toggle-icon d-inline-flex align-items-center justify-content-center">
                        <?php
                        $options = [
                            'class' => 'icon',
                            'alt' => '',
                        ];
                        echo $this->Html->image('/interface/assets/images/icons/chevron_down.svg', $options);
                        ?>
                    </span>
                </div>
                <ul class="dropdown-menu">
                    <li>
                        <?php
                        $options = [
                            'class' => '',
                        ];
                        $image = $this->Html->image('/interface/assets/images/icons/arrow_left.svg', $options);

                        $logoutText = '
                            <span class="dropdown-item-icon icon">
                                ' . $image . '
                            </span>
                            <span class="dropdown-item-text">Logout</span>
                        ';

                        $options = [
                            'class' => 'dropdown-item d-flex align-items-center',
                            'escapeTitle' => false
                        ];
                        echo $this->Html->link($logoutText, ['controller' => 'logout'], $options);
                        ?>
                    </li>
                    <li>
                        <?php
                        $options = [
                            'class' => '',
                        ];
                        $image = $this->Html->image('/interface/assets/images/icons/box_grid.svg', $options);

                        $logoutText = '
                            <span class="dropdown-item-icon icon">
                                ' . $image . '
                            </span>
                            <span class="dropdown-item-text">Profile</span>
                        ';

                        $options = [
                            'class' => 'dropdown-item d-flex align-items-center',
                            'escapeTitle' => false
                        ];
                        echo $this->Html->link($logoutText, ['controller' => 'users', 'action' => 'profile',], $options);
                        ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
