<?php
/**
 * @var App\View\AppView $this
 */

if ($this->AuthUser->user('gravatar_url')) {
    $userImage = $this->AuthUser->user('gravatar_url');
} else {
    $userImage = "/assets/img/user-placeholder.svg";
}
?>
<!-- User Dropdown-->
<li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage"
       href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
       aria-expanded="false">
        <?php
        $opts = [
            'class' => 'img-fluid',
            'alt' => 'User profile image',
        ];
        $img = $this->Html->image('/#', $opts);
        $img = str_replace("/#", $userImage, $img);
        echo $img;
        ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
         aria-labelledby="navbarDropdownUserImage">
        <h6 class="dropdown-header d-flex align-items-center">
            <?php
            $opts = [
                'class' => 'dropdown-user-img',
                'alt' => 'User profile image',
            ];
            $img = $this->Html->image('/#', $opts);
            $img = str_replace("/#", $userImage, $img);
            echo $img;
            ?>
            <div class="dropdown-user-details">
                <div class="dropdown-user-details-name"><?php
                    if ($this->AuthUser) {
                        echo $this->AuthUser->getFulName();
                    }
                    ?></div>
                <div class="dropdown-user-details-email"><?php
                    if ($this->AuthUser) {
                        echo $this->AuthUser->user('email');
                    }
                    ?></div>
            </div>
        </h6>
        <div class="dropdown-divider"></div>
        <?php
        $profileText = '<div class="dropdown-item-icon"><i data-feather="user-check"></i></div>Profile';
        $profileOpts = [
            'class' => 'dropdown-item',
            'escape' => false,
        ];
        echo $this->Html->link($profileText, ['controller' => 'Profile'], $profileOpts);

        $logoutText = '<div class="dropdown-item-icon"><i data-feather="log-out"></i></div>Logout';
        $logoutOpts = [
            'class' => 'dropdown-item',
            'escape' => false,
        ];
        echo $this->Html->link($logoutText, ['controller' => 'Logout'], $logoutOpts);
        ?>
    </div>
</li>
