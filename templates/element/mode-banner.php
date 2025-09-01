<?php
/**
 * @var App\View\AppView $this
 */

use Cake\Core\Configure;

?>
<?php
$bs = '<span class="d-inline-block d-sm-none">XS</span>'
    . '<span class="d-none d-sm-inline-block d-md-none">SM</span>'
    . '<span class="d-none d-md-inline-block d-lg-none">MD</span>'
    . '<span class="d-none d-lg-inline-block d-xl-none">LG</span>'
    . '<span class="d-none d-xl-inline-block d-xxl-none">XL</span>'
    . '<span class="d-none d-xxl-inline-block">XXL</span>';

$mode = Configure::read('mode');
$modeBanner = Configure::read('mode-banner');
if ($modeBanner && in_array(strtolower($mode), ['dev', 'development'])) {
    echo '<div class="mode-banner mode-banner-development">DEVELOPMENT (' . $bs . ') <span class="auto-logout-countdown">000</span></div>';
} elseif ($modeBanner && in_array(strtolower($mode), ['uat', 'test', 'testing'])) {
    echo '<div class="mode-banner mode-banner-test">UAT</div>';
} elseif ($modeBanner && in_array(strtolower($mode), ['prd', 'prod', 'production'])) {
    echo '<div class="mode-banner mode-banner-production">PRODUCTION</div>';
}
?>
