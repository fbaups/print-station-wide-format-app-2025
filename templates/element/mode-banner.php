<?php
/**
 * @var App\View\AppView $this
 */

use App\View\Helper\ExtendedAuthUserHelper;
use Cake\Core\Configure;

?>
<?php
$bs = '<span class="d-inline-block d-sm-none">XS</span>'
    . '<span class="d-none d-sm-inline-block d-md-none">SM</span>'
    . '<span class="d-none d-md-inline-block d-lg-none">MD</span>'
    . '<span class="d-none d-lg-inline-block d-xl-none">LG</span>'
    . '<span class="d-none d-xl-inline-block d-xxl-none">XL</span>'
    . '<span class="d-none d-xxl-inline-block">XXL</span>';

if (($this->AuthUser) && @$this->AuthUser instanceof ExtendedAuthUserHelper) {
    $debugIcon = $this->IconMaker->bootstrapIcon('bug');
} else {
    $debugIcon = '';
}

$mode = Configure::read('mode');
$modeBanner = Configure::read('mode-banner');
if ($modeBanner && in_array(strtolower($mode), ['dev', 'development'])) {
    echo '<div class="mode-banner mode-banner-development">DEVELOPMENT (' . $bs . ') <span id="return-alerts-debug" class="ms-3">' . $debugIcon . '</span></div>';
    ?>
    <script type="application/javascript">
        $('#return-alerts-debug').on('click', function () {
            let returnAlertsUrl = dataObjectsClosedUrl + "return-alerts";
            $.getJSON(returnAlertsUrl, function (jsonData) {
                $.each(jsonData, function (key, value) {
                    let formattedTime = formatTimestamp(parseFloat(key));
                    console.log(formattedTime + " " + value);
                });
            }).fail(function () {

            })
        })

    </script>
    <?php
} elseif ($modeBanner && in_array(strtolower($mode), ['uat', 'test', 'testing'])) {
    echo '<div class="mode-banner mode-banner-test">UAT</div>';
} elseif ($modeBanner && in_array(strtolower($mode), ['prd', 'prod', 'production'])) {
    echo '<div class="mode-banner mode-banner-production">PRODUCTION</div>';
}
?>
