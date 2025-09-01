<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Developer Tools', 'wrench');
$url = ['controller' => 'Developers'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
} ?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Code Watcher', 'code');
$url = ['controller' => 'CodeWatcherProjects'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
} ?>

<?php
$fooPages = [
    'Authors',
    'Recipes',
    'Ingredients',
    'Methods',
    'Tags',
];
foreach ($fooPages as $fooPage) {
    $title = $this->IconMaker->sideNavigationLinkIcon(ucwords($fooPage), 'card-checklist');
    $url = ['controller' => 'Foo' . $fooPage, 'action' => 'index'];
    $options = [
        'class' => 'nav-link',
        'escape' => false,
    ];
    if ($this->AuthUser->hasAccess($url)) {
        $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
    }
}
?>

<?php
//only echo out if there are links
if (!empty($linksMatrix)) {
    echo '<div class="sidenav-menu-heading">Developer</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
