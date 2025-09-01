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
<?php echo $this->element('Pages/navbar', options: ['ignoreMissing' => true]); ?>
<div id="layoutSidenav">

    <?php echo $this->element('Pages/sidenav', options: ['ignoreMissing' => true]); ?>

    <div id="layoutSidenav_content" class="ps-0 pe-0">
        <main>
            <!-- Main page content-->
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>

        </main>

        <?php echo $this->element('Pages/footer', options: ['ignoreMissing' => true]); ?>
    </div>
</div>

<!-- Plugin scripts sent by the view template -->
<?php echo $this->fetch('viewPluginScripts'); ?>

<!-- Custom scripts sent by the view template -->
<?php echo $this->fetch('viewCustomScripts'); ?>
</body>
</html>
