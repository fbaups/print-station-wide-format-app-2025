<?php
/**
 * @var App\View\AppView $this
 */
?>
<nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
     id="sidenavAccordion">
    <!-- Sidenav Toggle Button-->
    <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
        <?= $this->IconMaker->bootstrapIcon('list', size: 1.8, additionalStyles: "margin-top: 5px;"); ?>
    </button>

    <?php
    if (empty(APP_LOGO)) {
        ?>
        <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="<?= APP_LINK_HOME ?>"><?= APP_NAME ?></a>
        <?php
    } else {
        ?>
        <a class="h-100" href="<?= APP_LINK_HOME ?>">
            <?php
            $options = [
                'class' => "h-100 p-1",
                'alt' => APP_NAME . ' Dashboard',
            ];
            echo $this->Html->image(APP_LOGO, $options)
            ?>
        </a>
        <?php
    }
    ?>

    <!-- Navbar Items-->
    <ul class="navbar-nav align-items-center ms-auto">

        <?php echo $this->element('navbar_user_messages'); ?>

        <?php echo $this->element('navbar_user_links'); ?>

        <?php echo $this->element('navbar_user_alerts'); ?>

        <?php echo $this->element('navbar_user_dropdown'); ?>

    </ul>
</nav>
