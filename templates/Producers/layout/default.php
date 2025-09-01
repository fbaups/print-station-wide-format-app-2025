<?php
/**
 * @var AppView $this
 * @var bool $headerShow
 * @var string $headerIcon
 * @var string $headerTitle
 * @var string $headerSubTitle
 */

use App\View\AppView;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta name="description" content=""/>
    <meta name="author" content=""/>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->meta('icon', '/assets/img/favicon.png'); ?>
    <?= $this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken')); ?>

    <title><?= $this->fetch('title') ?></title>

    <?php
    echo $this->element('corelib');
    echo $this->element('environment');
    ?>

</head>
<body class="nav-fixed">
<?php echo $this->element('mode-banner'); ?>
<?php echo $this->element('navbar'); ?>
<div id="layoutSidenav">

    <?php echo $this->element('sidenav'); ?>

    <div id="layoutSidenav_content">
        <main>
            <?= $this->fetch('backLink') ?>
            <?php if ($headerShow) { ?>
                <header class="page-header page-header-light bg-light mb-0">
                    <div class="container-fluid px-4">
                        <div class="page-header-content pt-4">
                            <div class="row align-items-center justify-content-between">
                                <div class="col-auto mt-4">
                                    <h1 class="page-header-title">
                                        <?php if ($headerIcon) { ?>
                                            <div class="page-header-icon">
                                                <?= $this->IconMaker->bootstrapIcon($headerIcon, size: 2.2); ?>
                                            </div>
                                        <?php } ?>
                                        <?= $headerTitle ?>
                                    </h1>
                                    <div class="page-header-subtitle">
                                        <?= $headerSubTitle ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            <?php } else { ?>
                <div class="spacer-20"></div>
            <?php } ?>

            <!-- Main page content-->
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>

        </main>

        <?php echo $this->element('footer'); ?>
    </div>
</div>

<!-- Plugin scripts sent by the view template -->
<?php echo $this->fetch('viewPluginScripts'); ?>

<!-- Custom scripts sent by the view template -->
<?php echo $this->fetch('viewCustomScripts'); ?>
</body>
</html>
