<?php
/**
 * @var App\View\AppView $this
 */
?>

<!-- Sidenav Heading -->
<div class="sidenav-menu-heading">System Admin</div>
<?php
$title = '<div class="nav-link-icon"><i data-feather="settings"></i></div> Background Services';
$url = ['controller' => 'BackgroundServices', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

$title = '<div class="nav-link-icon"><i data-feather="activity"></i></div> Heartbeats';
$url = ['controller' => 'Heartbeats', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

$title = '<div class="nav-link-icon"><i data-feather="code"></i></div> Updates';
$url = ['controller' => 'Instance', 'action' => 'updates'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

$title = '<div class="nav-link-icon"><i data-feather="tool"></i></div> Settings';
$url = ['controller' => 'Settings', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

$title = '<div class="nav-link-icon"><i data-feather="info"></i></div> System Info';
$url = ['controller' => 'Instance', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

$title = '<div class="nav-link-icon"><i data-feather="trending-up"></i></div> Load Tests';
$url = ['controller' => 'LoadTests', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

?>
