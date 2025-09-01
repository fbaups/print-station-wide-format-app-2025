<?php
/**
 * @var AppView $this
 * @var string $hSaltCheck
 * @var string $hKeyCheck
 * @var GhostscriptCommands|false $GhostscriptCommands
 * @var CallasCommands|false $CallasCommands
 * @var ImageInfo|false $ImageInfo
 * @var ImageMagickCommands|false $ImageMagickCommands
 */

use App\Utility\Instances\InstanceTasks;
use App\Utility\Releases\VersionControl;
use App\View\AppView;
use arajcany\PrePressTricks\Graphics\Callas\CallasCommands;
use arajcany\PrePressTricks\Graphics\Ghostscript\GhostscriptCommands;
use arajcany\PrePressTricks\Graphics\ImageMagick\ImageMagickCommands;
use arajcany\PrePressTricks\Utilities\ImageInfo;
use Cake\Core\Configure;
use Cake\Database\Driver;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Sqlite;
use Cake\Database\Driver\Sqlserver;
use Cake\Datasource\ConnectionManager;

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
        $host = ($connection->config()['host']);
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
                    $resultPhpFastCgiIcon = 'check';
                    $resultPhpFastCgiText = "PHP Version <strong>{$resultPhpFastCgi}</strong>";
                    $resultPhpFastCgiAlert = 'success';
                } else {
                    $resultPhpFastCgiIcon = 'x';
                    $resultPhpFastCgiText = "PHP Fast CGI Failed to Load.";
                    $resultPhpFastCgiAlert = 'warning';
                }

                $resultPhpBinary = $InstanceTasks->getPhpBinary();
                $resultPhpBinaryVersion = $InstanceTasks->getPhpBinaryVersion();
                if ($resultPhpBinary) {
                    $resultPhpBinaryIcon = 'check';
                    $resultPhpBinaryText = "PHP CLI Version <strong>{$resultPhpBinaryVersion}</strong> ({$resultPhpBinary})";
                    $resultPhpBinaryAlert = 'success';
                } else {
                    $resultPhpBinaryIcon = 'x';
                    $resultPhpBinaryText = "PHP CLI Failed to Load.";
                    $resultPhpBinaryAlert = 'warning';
                }

                $resultCakePhpVersion = Configure::version();
                if ($resultCakePhpVersion) {
                    $resultCakePhpVersionIcon = 'check';
                    $resultCakePhpVersionText = "CakePHP Version <strong>{$resultCakePhpVersion}</strong>";
                    $resultCakePhpVersionAlert = 'success';
                } else {
                    $resultCakePhpVersionIcon = 'x';
                    $resultCakePhpVersionText = "Could not determine the CakePHP Version.";
                    $resultCakePhpVersionAlert = 'warning';
                }

                $resultApplicationVersion = $VersionControl->getCurrentVersionTag();
                if ($resultApplicationVersion) {
                    $resultApplicationVersionIcon = 'check';
                    $resultApplicationVersionText = "Application Version <strong>{$resultApplicationVersion}</strong>";
                    $resultApplicationVersionAlert = 'success';
                } else {
                    $resultApplicationVersionIcon = 'x';
                    $resultApplicationVersionText = "Could not determine the Application Version.";
                    $resultApplicationVersionAlert = 'warning';
                }

                $resultDatabaseConnection = $checkConnection('default');
                if ($resultDatabaseConnection) {
                    $resultDatabaseConnectionIcon = 'check';
                    $resultDatabaseConnectionText = "Connected to Database <strong>{$resultDatabaseConnection['host']}</strong>";
                    $resultDatabaseConnectionAlert = 'success';
                } else {
                    $resultDatabaseConnectionIcon = 'x';
                    $resultDatabaseConnectionText = "Connection to Database failed.";
                    $resultDatabaseConnectionAlert = 'warning';
                }

                ?>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $resultPhpFastCgiAlert ?>"
                                        data-feather="<?= $resultPhpFastCgiIcon ?>"></i>
                    <?= $resultPhpFastCgiText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $resultPhpBinaryAlert ?>"
                                        data-feather="<?= $resultPhpBinaryIcon ?>"></i>
                    <?= $resultPhpBinaryText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $resultCakePhpVersionAlert ?>"
                                        data-feather="<?= $resultCakePhpVersionIcon ?>"></i>
                    <?= $resultCakePhpVersionText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $resultApplicationVersionAlert ?>"
                                        data-feather="<?= $resultApplicationVersionIcon ?>"></i>
                    <?= $resultApplicationVersionText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $resultDatabaseConnectionAlert ?>"
                                        data-feather="<?= $resultDatabaseConnectionIcon ?>"></i>
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
                    $gsIcon = 'check';
                    $gsText = "Ghostscript {$GhostscriptCommands->getCliVersion()} is installed and functioning.";
                    $gsAlert = 'success';
                } else {
                    $gsIcon = 'x';
                    $gsText = "Ghostscript is not installed or failed to start.";
                    $gsAlert = 'warning';
                }

                if ($CallasCommands && $CallasCommands->isAlive()) {
                    $callasIcon = 'check';
                    $callasText = "Callas pdfToolbox CLI {$CallasCommands->getCliVersion()} is installed and functioning.";
                    $callasAlert = 'success';
                } else {
                    $callasIcon = 'x';
                    $callasText = "Callas pdfToolbox CLI is not installed or failed to start.";
                    $callasAlert = 'warning';
                }

                ?>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $gsAlert ?>" data-feather="<?= $gsIcon ?>"></i>
                    <?= $gsText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $callasAlert ?>"
                                        data-feather="<?= $callasIcon ?>"></i>
                    <?= $callasText ?>
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
                    $imCliIcon = 'check';
                    $imCliText = "ImageMagick CLI <strong>{$imCli}</strong> is installed and functioning.";
                    $imCliAlert = 'success';
                } else {
                    $imCliIcon = 'x';
                    $imCliText = "ImageMagick CLI is not installed or failed to load.";
                    $imCliAlert = 'warning';
                }

                $imExtension = $ImageMagickCommands->getExtensionVersion();
                if ($imExtension) {
                    $imExtensionIcon = 'check';
                    $imExtensionText = "ImageMagick PHP Extension <strong>{$imExtension}</strong> is installed and functioning.";
                    $imExtensionAlert = 'success';
                } else {
                    $imExtensionIcon = 'x';
                    $imExtensionText = "ImageMagick PHP Extension is not installed or failed to load.";
                    $imExtensionAlert = 'warning';
                }

                $gdExtension = phpversion('gd');
                if ($gdExtension) {
                    $gdExtensionIcon = 'check';
                    $gdExtensionText = "GD Image PHP Extension <strong>{$gdExtension}</strong> is installed and functioning.";
                    $gdExtensionAlert = 'success';
                } else {
                    $gdExtensionIcon = 'x';
                    $gdExtensionText = "GD Image PHP Extension is not installed or failed to load.";
                    $gdExtensionAlert = 'warning';
                }

                $exifTool = $ImageInfo->getExifToolPath();
                $exifToolVersion = $ImageInfo->getExifToolVersion();
                if ($exifTool) {
                    $exifToolIcon = 'check';
                    $exifToolText = "ExifTool CLI <strong>{$exifToolVersion}</strong> is installed and functioning.";
                    $exifToolAlert = 'success';
                } else {
                    $exifToolIcon = 'x';
                    $exifToolText = "ExifTool CLI is not installed or failed to load.";
                    $exifToolAlert = 'warning';
                }
                ?>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $gdExtensionAlert ?>"
                                        data-feather="<?= $gdExtensionIcon ?>"></i>
                    <?= $gdExtensionText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $imCliAlert ?>"
                                        data-feather="<?= $imCliIcon ?>"></i>
                    <?= $imCliText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $imExtensionAlert ?>"
                                        data-feather="<?= $imExtensionIcon ?>"></i>
                    <?= $imExtensionText ?>
                </p>
                <p class="mt-1 mb-1"><i class="ms-0 me-1 mt-1 text-<?= $exifToolAlert ?>"
                                        data-feather="<?= $exifToolIcon ?>"></i>
                    <?= $exifToolText ?>
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

