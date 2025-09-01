<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php

$title = $this->IconMaker->sideNavigationLinkIcon('Artifacts Repository', 'archive');
$url = ['controller' => 'Artifacts', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Clips', 'collection-play');
$url = ['controller' => 'MediaClips', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Articles', 'newspaper');
$url = ['controller' => 'Articles', 'action' => 'index'];
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
    echo '<div class="sidenav-menu-heading">Media Storage</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
