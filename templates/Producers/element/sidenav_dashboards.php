<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = $this->IconMaker->sideNavigationLinkIcon('Primary', 'grid');
$url = ['controller' => 'Dashboards', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Secondary', 'grid');
$url = ['controller' => 'Dashboards', 'action' => 'secondary'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);
}

$title = $this->IconMaker->sideNavigationLinkIcon('Tertiary', 'grid');
$url = ['controller' => 'Dashboards', 'action' => 'tertiary'];
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
    ?>
    <!-- Sidenav Accordion (Dashboard)-->
    <a id="dashboardToggle" class="nav-link collapsed mt-3" data-bs-toggle="collapse"
       data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
        <?= $this->IconMaker->sideNavigationLinkIcon('Dashboards', 'grid') ?>
        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
    </a>
    <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
        <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
            <?php
            echo implode("\r\n", $linksMatrix);
            ?>
        </nav>
    </div>
    <?php
}
?>
