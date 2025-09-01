<?php
/**
 * @var App\View\AppView $this
 */
?>
<nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
     id="sidenavAccordion">

    <?php
    if (empty(APP_LOGO)) {
        ?>
        <a class="navbar-brand pe-3 ps-4 ps-lg-2 ms-3" href="<?= APP_LINK_HOME ?>"><?= APP_NAME ?></a>
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


    </ul>
</nav>
