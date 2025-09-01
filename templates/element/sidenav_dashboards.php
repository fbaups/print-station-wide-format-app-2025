<?php
/**
 * @var App\View\AppView $this
 */
?>

<!-- Sidenav Accordion (Dashboard)-->
<a id="dashboardToggle" class="nav-link collapsed mt-3" href="javascript:void(0);" data-bs-toggle="collapse"
   data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
    <div class="nav-link-icon"><i data-feather="grid"></i></div>
    Dashboards
    <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>
<div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
    <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
        <?php
        $title = '<div class="nav-link-icon"><i data-feather="grid"></i></div> Default';
        $url = ['controller' => 'Dashboards', 'action' => 'index'];
        $options = [
            'class' => 'nav-link',
            'escape' => false,
        ];
        echo $this->AuthUser->link($title, $url, $options);

        $title = '<div class="nav-link-icon"><i data-feather="grid"></i></div> Secondary';
        $url = ['controller' => 'Dashboards', 'action' => 'secondary'];
        $options = [
            'class' => 'nav-link',
            'escape' => false,
        ];
        echo $this->AuthUser->link($title, $url, $options);
        ?>
    </nav>
</div>
