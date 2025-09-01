<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Users', 'people');
$url = ['controller' => 'Users', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Invite', 'person-plus');
$url = ['controller' => 'Users', 'action' => 'invite'];
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
    echo '<div class="sidenav-menu-heading">Users</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
