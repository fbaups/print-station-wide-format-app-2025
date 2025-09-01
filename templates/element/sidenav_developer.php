<?php
/**
 * @var App\View\AppView $this
 */
?>

<!-- Sidenav Heading -->
<div class="sidenav-menu-heading">Release Builder</div>
<?php
$title = '<div class="nav-link-icon"><i data-feather="sliders"></i></div> Configuration';
$url = ['controller' => 'ReleaseBuilder', 'action' => 'index'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);

$title = '<div class="nav-link-icon"><i data-feather="check-circle"></i></div> Checks';
$url = ['controller' => 'ReleaseBuilder', 'action' => 'check'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);
?>

<!-- Sidenav Heading -->
<div class="sidenav-menu-heading">Developer</div>
<?php
$title = '<div class="nav-link-icon"><i data-feather="tool"></i></div> Developer Tools';
$url = ['controller' => 'Developers'];
$options = [
    'class' => 'nav-link',
    'escape' => false,
];
echo $this->AuthUser->link($title, $url, $options);
?>

<?php
$fooPages = [
    'Authors',
    'Recipes',
    'Ingredients',
    'Methods',
    'Tags',
];
foreach ($fooPages as $fooPage) {
    $title = __('<div class="nav-link-icon"><i data-feather="file-text"></i></div> Foo {0}', ucwords($fooPage));
    $url = ['controller' => 'Foo' . $fooPage, 'action' => 'index'];
    $options = [
        'class' => 'nav-link',
        'escape' => false,
    ];
    echo $this->AuthUser->link($title, $url, $options);
}
?>
