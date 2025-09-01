<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Background Services', 'house-gear');
$url = ['controller' => 'BackgroundServices', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Heartbeats', 'activity');
$url = ['controller' => 'Heartbeats', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Updates', 'cloud-download');
$url = ['controller' => 'Instance', 'action' => 'updates'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Settings', 'tools');
$url = ['controller' => 'Settings', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('System Info', 'info-circle');
$url = ['controller' => 'Instance', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Load Tests', 'graph-up-arrow');
$url = ['controller' => 'LoadTests', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

?>

<?php
//only echo out if there are links
if (!empty($linksMatrix)) {
    echo '<div class="sidenav-menu-heading">System Admin</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
