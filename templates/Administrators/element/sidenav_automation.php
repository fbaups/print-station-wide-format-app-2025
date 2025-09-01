<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Data Receivers', 'database-add');
$url = ['controller' => 'Seeds', 'action' => 'data-receiver-tokens'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Input Hot Folders', 'folder');
$url = ['controller' => 'HotFolders', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Output Processors', 'box-arrow-right');
$url = ['controller' => 'OutputProcessors', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Scheduled Tasks', 'clock');
$url = ['controller' => 'ScheduledTasks', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Errands', 'repeat');
$url = ['controller' => 'Errands', 'action' => 'index'];
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
    echo '<div class="sidenav-menu-heading">Automation</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
