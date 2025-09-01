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
        $img = '<img src="' . $userImage . '" class="img-fluid" alt="User profile image">';
        echo $img;
        ?>
    </a>
    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
         aria-labelledby="navbarDropdownUserImage">
        <h6 class="dropdown-header d-flex align-items-center">
            <?php
            $img = '<img src="' . $userImage . '" class="dropdown-user-img" alt="User profile image">';
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
        $icon = $this->IconMaker->bootstrapIcon('person-check');
        $profileText = '<div class="dropdown-item-icon">' . $icon . '</div>Profile';
        $profileOpts = [
            'class' => 'dropdown-item',
            'escape' => false,
        ];
        echo $this->Html->link($profileText, ['controller' => 'UserManagement', 'action' => 'profile'], $profileOpts);

        $icon = $this->IconMaker->bootstrapIcon('box-arrow-right');
        $logoutText = '<div class="dropdown-item-icon">' . $icon . '</div>Logout';
        $logoutOpts = [
            'class' => 'dropdown-item',
            'escape' => false,
        ];
        echo $this->Html->link($logoutText, ['prefix' => false, 'controller' => 'Logout'], $logoutOpts);
        ?>
    </div>
</li>
