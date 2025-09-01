<?php
/**
 * @var AppView $this
 * @var string $hSaltCheck
 * @var string $hKeyCheck
 * @var GhostscriptCommands|false $GhostscriptCommands
 * @var CallasCommands|false $CallasCommands
 * @var ImageInfo|false $ImageInfo
 * @var ImageMagickCommands|false $ImageMagickCommands
 * @var FFmpegCommands|false $FFmpegCommands
 * @var bool $isLoadBalancerOrProxy
 * @var array $serverParams
 */

use App\Utility\Instances\InstanceTasks;
use App\Utility\Releases\VersionControl;
use App\View\AppView;
use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;
use arajcany\PrePressTricks\Graphics\FFmpeg\FFmpegCommands;
use arajcany\PrePressTricks\Graphics\Ghostscript\GhostscriptCommands;
use arajcany\PrePressTricks\Graphics\ImageMagick\ImageMagickCommands;
use arajcany\PrePressTricks\Utilities\ImageInfo;
use Cake\Core\Configure;
use Cake\Database\Driver;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;
use Cake\Routing\Router;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('System Information'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-0"><small class="form-text text-muted">{{help}}</small></div></div>',
];

$InstanceTasks = new InstanceTasks();
$VersionControl = new VersionControl();

$checkConnection = function (string $name) {
    $error = null;
    $connected = false;
    $host = '';
    $driver = '';
    try {
        ConnectionManager::get($name)->getDriver()->connect();
        $connected = true;
        $connection = ConnectionManager::get($name);
        /** @var Driver|Sqlserver|Sqlite|Mysql $driver */
        $driver = $connection->getDriver();
        $dbConfig = $connection->config();
        if (isset($dbConfig['host'])) {
            $host = ($dbConfig['host']);
        } elseif (isset($dbConfig['driver'])) {
            if ($dbConfig['driver'] === 'Cake\Database\Driver\Sqlite') {
                $host = pathinfo($dbConfig['database'], PATHINFO_BASENAME);
            }
        }
    } catch (Exception $connectionError) {
        $error = $connectionError->getMessage();
        if (method_exists($connectionError, 'getAttributes')) {
            $attributes = $connectionError->getAttributes();
            if (isset($attributes['message'])) {
                $error .= '<br />' . $attributes['message'];
            }
        }
    }

    return compact('connected', 'error', 'driver', 'host');
};
?>

<?php
if ($isLoadBalancerOrProxy) {
    ?>
    <div class="container-fluid px-4 col-12 col-md-8 col-lg-6">
        <div class="card mb-5">
            <div class="card-header">
                <?php
                echo __('{0} Reverse Proxy or Load Balancer', APP_NAME)
                ?>
            </div>
            <div class="card-body">
                <p class="mb-4"><strong>The installation might be behind a Reverse Proxy or Load Balancer</strong></p>
                <?php foreach ($serverParams as $k => $serverParam) { ?>
                    <p class="mb-0"><?= $k ?>: <strong><?= $serverParam ?></strong></p>
                <?php } ?>
                <p class="mt-4">
                    Please make sure you enable "Trust Proxy Header" in
                    <?php
                    $link = ['prefix' => 'Administrators', 'controller' => 'Settings', 'action' => 'edit-group', 'install'];
                    echo $this->Html->link("Settings", $link)
                    ?>
                    to ensure <?= APP_NAME ?> functions correctly.
                </p>
            </div>
            <div class="card-footer">
                <div class="float-end">
                    <?php
                    $options = [
                        'class' => 'link-secondary me-4'
                    ];
                    echo $this->Html->link(__('Home'), ['controller' => '/'], $options);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

<div class="container-fluid px-4 col-12 col-md-8 col-lg-6">
    <div class="card mb-5">

        <div class="card-header">
            <?php
            echo __('{0} System Information', APP_NAME)
            ?>
        </div>

        <div class="card-body">
            <div>
                <?php
                $resultPhpFastCgi = phpversion();
                if ($resultPhpFastCgi) {
                    $resultPhpFastCgiIcon = 'check-square';
                    $resultPhpFastCgiText = "PHP Version <strong>{$resultPhpFastCgi}</strong>";
                    $resultPhpFastCgiAlert = 'success';
                } else {
                    $resultPhpFastCgiIcon = 'x-square';
                    $resultPhpFastCgiText = "PHP Fast CGI Failed to Load.";
                    $resultPhpFastCgiAlert = 'warning';
                }

                $resultPhpBinary = $InstanceTasks->getPhpBinary();
                $resultPhpBinaryVersion = $InstanceTasks->getPhpBinaryVersion();
                if ($resultPhpBinary) {
                    $resultPhpBinaryIcon = 'check-square';
                    $resultPhpBinaryText = "PHP CLI Version <strong>{$resultPhpBinaryVersion}</strong> ({$resultPhpBinary})";
                    $resultPhpBinaryAlert = 'success';
                } else {
                    $resultPhpBinaryIcon = 'x-square';
                    $resultPhpBinaryText = "PHP CLI Failed to Load.";
                    $resultPhpBinaryAlert = 'warning';
                }

                $resultCakePhpVersion = Configure::version();
                if ($resultCakePhpVersion) {
                    $resultCakePhpVersionIcon = 'check-square';
                    $resultCakePhpVersionText = "CakePHP Version <strong>{$resultCakePhpVersion}</strong>";
                    $resultCakePhpVersionAlert = 'success';
                } else {
                    $resultCakePhpVersionIcon = 'x-square';
                    $resultCakePhpVersionText = "Could not determine the CakePHP Version.";
                    $resultCakePhpVersionAlert = 'warning';
                }

                $resultApplicationVersion = $VersionControl->getCurrentVersionTag();
                if ($resultApplicationVersion) {
                    $resultApplicationVersionIcon = 'check-square';
                    $resultApplicationVersionText = "Application Version <strong>{$resultApplicationVersion}</strong>";
                    $resultApplicationVersionAlert = 'success';
                } else {
                    $resultApplicationVersionIcon = 'x-square';
                    $resultApplicationVersionText = "Could not determine the Application Version.";
                    $resultApplicationVersionAlert = 'warning';
                }

                $resultDatabaseConnection = $checkConnection('default');
                if ($resultDatabaseConnection) {
                    $resultDatabaseConnectionIcon = 'check-square';
                    $resultDatabaseConnectionText = "Connected to Database <strong>{$resultDatabaseConnection['host']}</strong>";
                    $resultDatabaseConnectionAlert = 'success';
                } else {
                    $resultDatabaseConnectionIcon = 'x-square';
                    $resultDatabaseConnectionText = "Connection to Database failed.";
                    $resultDatabaseConnectionAlert = 'warning';
                }

                ?>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($resultPhpFastCgiIcon, additionalClasses: "me-1 text-{$resultPhpFastCgiAlert}"); ?>
                    <?= $resultPhpFastCgiText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($resultPhpBinaryIcon, additionalClasses: "me-1 text-{$resultPhpBinaryAlert}"); ?>
                    <?= $resultPhpBinaryText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($resultCakePhpVersionIcon, additionalClasses: "me-1 text-{$resultCakePhpVersionAlert}"); ?>
                    <?= $resultCakePhpVersionText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($resultApplicationVersionIcon, additionalClasses: "me-1 text-{$resultApplicationVersionAlert}"); ?>
                    <?= $resultApplicationVersionText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($resultDatabaseConnectionIcon, additionalClasses: "me-1 text-{$resultDatabaseConnectionAlert}"); ?>
                    <?= $resultDatabaseConnectionText ?>
                </p>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Home'), ['controller' => '/'], $options);
                ?>
            </div>
        </div>

    </div>

    <div class="card mb-5">

        <div class="card-header">
            <?php
            echo __('PDF Processing Options')
            ?>
        </div>

        <div class="card-body">
            <div>
                <?php
                if ($GhostscriptCommands && $GhostscriptCommands->isAlive()) {
                    $gsIcon = 'check-square';
                    $gsText = "Ghostscript {$GhostscriptCommands->getCliVersion()} is installed and functioning.";
                    $gsAlert = 'success';
                } else {
                    $gsIcon = 'x-square';
                    $gsText = "Ghostscript is not installed or failed to start.";
                    $gsAlert = 'warning';
                }

                if ($CallasCommands && $CallasCommands->isAlive()) {
                    $callasIcon = 'check-square';
                    $callasText = ucfirst("{$CallasCommands->getCliVersion()} is installed and functioning.");
                    $callasAlert = 'success';
                } elseif ($CallasCommands && $CallasCommands->getCliVersion()) {
                    $callasIcon = 'x-square';
                    $callasText = ucfirst("{$CallasCommands->getCliVersion()} is installed but not serialised.");
                    $callasAlert = 'warning';
                } else {
                    $callasIcon = 'x-square';
                    $callasText = "Callas pdfToolbox CLI is not installed or failed to start.";
                    $callasAlert = 'warning';
                }

                ?>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($gsIcon, additionalClasses: "me-1 text-{$gsAlert}"); ?>
                    <?= $gsText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($callasIcon, additionalClasses: "me-1 text-{$callasAlert}"); ?>
                    <?= $callasText ?>
                    <?php
                    echo "(";
                    echo $this->Html->link('Perform Activation', ['prefix' => 'Administrators', 'controller' => 'callas']);
                    echo ")";
                    ?>
                </p>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Home'), ['controller' => '/'], $options);
                ?>
            </div>
        </div>

    </div>

    <div class="card mb-5">

        <div class="card-header">
            <?php
            echo __('Image Processing Options')
            ?>
        </div>

        <div class="card-body">
            <div>
                <?php
                $imCli = $ImageMagickCommands->getCliVersion();
                if ($imCli) {
                    $imCliIcon = 'check-square';
                    $imCliText = "ImageMagick CLI <strong>{$imCli}</strong> is installed and functioning.";
                    $imCliAlert = 'success';
                } else {
                    $imCliIcon = 'x-square';
                    $imCliText = "ImageMagick CLI is not installed or failed to load.";
                    $imCliAlert = 'warning';
                }

                $imExtension = $ImageMagickCommands->getExtensionVersion();
                if ($imExtension) {
                    $imExtensionIcon = 'check-square';
                    $imExtensionText = "ImageMagick PHP Extension <strong>{$imExtension}</strong> is installed and functioning.";
                    $imExtensionAlert = 'success';
                } else {
                    $imExtensionIcon = 'x-square';
                    $imExtensionText = "ImageMagick PHP Extension is not installed or failed to load.";
                    $imExtensionAlert = 'warning';
                }

                $gdExtension = phpversion('gd');
                if ($gdExtension) {
                    $gdExtensionIcon = 'check-square';
                    $gdExtensionText = "GD Image PHP Extension <strong>{$gdExtension}</strong> is installed and functioning.";
                    $gdExtensionAlert = 'success';
                } else {
                    $gdExtensionIcon = 'x-square';
                    $gdExtensionText = "GD Image PHP Extension is not installed or failed to load.";
                    $gdExtensionAlert = 'warning';
                }

                $exifTool = $ImageInfo->getExifToolPath();
                $exifToolVersion = $ImageInfo->getExifToolVersion();
                if ($exifTool) {
                    $exifToolIcon = 'check-square';
                    $exifToolText = "ExifTool CLI <strong>{$exifToolVersion}</strong> is installed and functioning.";
                    $exifToolAlert = 'success';
                } else {
                    $exifToolIcon = 'x-square';
                    $exifToolText = "ExifTool CLI is not installed or failed to load.";
                    $exifToolAlert = 'warning';
                }

                $ffmpegToolVersion = $FFmpegCommands->getCliVersion();
                if ($ffmpegToolVersion) {
                    $ffmpegToolIcon = 'check-square';
                    $ffmpegToolText = "FFmpeg CLI <strong>{$ffmpegToolVersion}</strong> is installed and functioning.";
                    $ffmpegToolAlert = 'success';
                } else {
                    $ffmpegToolIcon = 'x-square';
                    $ffmpegToolText = "ffmpegTool CLI is not installed or failed to load.";
                    $ffmpegToolAlert = 'warning';
                }
                ?>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($gdExtensionIcon, additionalClasses: "me-1 text-{$gdExtensionAlert}"); ?>
                    <?= $gdExtensionText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($imCliIcon, additionalClasses: "me-1 text-{$imCliAlert}"); ?>
                    <?= $imCliText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($imExtensionIcon, additionalClasses: "me-1 text-{$imExtensionAlert}"); ?>
                    <?= $imExtensionText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($exifToolIcon, additionalClasses: "me-1 text-{$exifToolAlert}"); ?>
                    <?= $exifToolText ?>
                </p>
                <p class="mt-1 mb-1">
                    <?= $this->IconMaker->bootstrapIcon($ffmpegToolIcon, additionalClasses: "me-1 text-{$ffmpegToolAlert}"); ?>
                    <?= $ffmpegToolText ?>
                </p>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Home'), ['controller' => '/'], $options);
                ?>
            </div>
        </div>

    </div>
</div>

<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {

    });
</script>
<?php
$this->end();
?>

