<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = '<div class="nav-link-icon"><i data-feather="users"></i></div> Users';
$url = ['controller' => 'Users', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}
?>

<?php
$title = '<div class="nav-link-icon"><i data-feather="user-plus"></i></div> Invite';
$url = ['controller' => 'Users', 'action' => 'invite'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}
?>

<?php
$title = '<div class="nav-link-icon"><i data-feather="list"></i></div> Roles';
$url = ['controller' => 'Roles', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}
?>

<?php
//only echo out if there are links
if (!empty($linksMatrix)) {
    echo '<div class="sidenav-menu-heading">Users</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
