<?php
/**
 * @var App\View\AppView $this
 */
?>

<?php
$linksMatrix = [];
?>

<?php
$title = '<div class="nav-link-icon"><i data-feather="corner-down-right"></i></div> Orders';
$url = ['controller' => 'Orders', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}

$title = '<div class="nav-link-icon"><i data-feather="corner-down-right"></i></div> Jobs';
$url = ['controller' => 'Jobs', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}

$title = '<div class="nav-link-icon"><i data-feather="corner-down-right"></i></div> Documents';
$url = ['controller' => 'Documents', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
if ($this->AuthUser->hasAccess($url)) {
    $linksMatrix[] = $this->AuthUser->link($title, $url, $options);;
}

$title = '<div class="nav-link-icon"><i data-feather="archive"></i></div> Artifacts Repository';
$url = ['controller' => 'Artifacts', 'action' => 'index'];
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
    echo '<div class="sidenav-menu-heading">Application Data</div>';
    echo implode("\r\n", $linksMatrix);
}
?>
