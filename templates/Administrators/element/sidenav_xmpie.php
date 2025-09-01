<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('uProduce Composer', 'file-richtext');
$url = ['controller' => 'XmpieUproduceCompositions', 'action' => 'index'];
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
    echo '<div class="sidenav-menu-heading">XMPie</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
