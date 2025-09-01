<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = '<div class="nav-link-icon"><i data-feather="folder"></i></div> Input Hot Folders';
$url = ['controller' => 'HotFolders', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}

$title = '<div class="nav-link-icon"><i data-feather="play-circle"></i></div> Output Processors';
$url = ['controller' => 'OutputProcessors', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}

$title = '<div class="nav-link-icon"><i data-feather="clock"></i></div> Scheduled Tasks';
$url = ['controller' => 'ScheduledTasks', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}

$title = '<div class="nav-link-icon"><i data-feather="repeat"></i></div> Errands';
$url = ['controller' => 'Errands', 'action' => 'index'];
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
    echo '<div class="sidenav-menu-heading">Automation</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
