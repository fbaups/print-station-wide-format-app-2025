<?php
/**
 * @var App\View\AppView $this
 */
?>

<!-- Sidenav Heading -->
<div class="sidenav-menu-heading">Security</div>
<?php
$title = '<div class="nav-link-icon"><i data-feather="sun"></i></div> Seeds';
$url = ['controller' => 'Seeds', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);
?>

<?php
$title = '<div class="nav-link-icon"><i data-feather="list"></i></div> Application Logs';
$url = ['controller' => 'ApplicationLogs', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);
?>


<?php
$title = '<div class="nav-link-icon"><i data-feather="shield"></i></div> Auditable Events';
$url = ['controller' => 'Audits', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);
?>
