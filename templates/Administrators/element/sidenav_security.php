<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Seeds', 'building-lock');
$url = ['controller' => 'Seeds', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Application Logs', 'list-columns-reverse');
$url = ['controller' => 'ApplicationLogs', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}
?>


<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Auditable Events', 'shield-lock');
$url = ['controller' => 'Audits', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}
?>


<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Integration Credentials', 'person-badge');
$url = ['controller' => 'IntegrationCredentials', 'action' => 'index'];
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
    echo '<div class="sidenav-menu-heading">Security</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
