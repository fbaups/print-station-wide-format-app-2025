<?php
/**
 * @var AppView $this
 * @var bool $headerShow
 * @var string $headerIcon
 * @var string $headerTitle
 * @var string $headerSubTitle
 * @var User $user
 */

use App\Model\Entity\User;
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
    echo $this->element('corelib_user_interface_header');
    echo $this->element('corelib');
    echo $this->element('environment');
    ?>

</head>
<body class="bg-primary-soft">
<?php echo $this->element('mode-banner'); ?>

<main>
    <!-- Main page content-->
    <?php echo $this->fetch('content') ?>
</main>

<!-- Plugin scripts sent by the view template -->
<?php echo $this->fetch('viewPluginScripts'); ?>

<!-- Custom scripts sent by the view template -->
<?php echo $this->fetch('viewCustomScripts'); ?>

<!-- User Interface custom libraries -->
<?php echo $this->element('corelib_user_interface_footer'); ?>
</body>
</html>
