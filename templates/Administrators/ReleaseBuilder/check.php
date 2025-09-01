<?php
/**
 * @var AppView $this
 * @var string $hSaltCheck
 * @var string $hKeyCheck
 * @var string $batPathIsValid
 * @var bool $batPath
 */

use App\Model\Entity\User;
use App\Utility\Instances\InstanceTasks;
use App\Utility\Releases\RemoteUpdateServer;
use App\Utility\Releases\VersionControl;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __("box-seam"));
$this->set('headerTitle', __('Release Builder Checks'));
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

$RemoteUpdateServer = new RemoteUpdateServer();
$InstanceTasks = new InstanceTasks();
?>

<div class="container-fluid px-4 col-12 col-md-10 col-lg-10">
    <div class="card">

        <div class="card-header">
            <?php
            echo __('Release Builder Configuration Checks')
            ?>
        </div>

        <div class="card-body">
            <div>
                <?php
                $resultUpdateServerReadWrite = $RemoteUpdateServer->getRemoteUpdateServer();
                $resultVersionHistoryHashPresent = $RemoteUpdateServer->isVersionHistoryHashOnServer();
                $resultEncryptionKey = $RemoteUpdateServer->getVersionHistoryHash();
                $resultPhpBinary = $InstanceTasks->getPhpBinary();
                $resultComposerBinary = $InstanceTasks->getComposerBinary();


                if ($resultUpdateServerReadWrite) {
                    $resultUpdateServerReadWriteIcon = 'check-square';
                    $resultUpdateServerReadWriteAlert = 'success';
                    $resultUpdateServerReadWriteText = 'Connection to Remote Update Server succeeded.';
                } else {
                    $resultUpdateServerReadWriteIcon = 'x-square';
                    $resultUpdateServerReadWriteAlert = 'warning';
                    $resultUpdateServerReadWriteText = 'Connection to Remote Update Server failed.';
                }

                if (!$resultUpdateServerReadWrite) {
                    $resultVersionHistoryHashPresentIcon = 'x-square';
                    $resultVersionHistoryHashPresentAlert = 'warning';
                    $resultVersionHistoryHashPresentText = 'Could not check for the Version History file as there is no connection to the Remote Update Server.';
                } elseif ($resultVersionHistoryHashPresent) {
                    $resultVersionHistoryHashPresentIcon = 'check-square';
                    $resultVersionHistoryHashPresentAlert = 'success';
                    $resultVersionHistoryHashPresentText = "Found the Version History file on <strong>{$RemoteUpdateServer->remote_update_url}</strong>.";

                    $options = ['target' => "_blank"];
                    $url = "{$RemoteUpdateServer->remote_update_url}download.html";
                    $link = $this->Html->link($url, $url, $options);
                    $downloadInstallerText = "Download the installer from {$link}";
                } else {
                    $resultVersionHistoryHashPresentIcon = 'x-square';
                    $resultVersionHistoryHashPresentAlert = 'warning';
                    $resultVersionHistoryHashPresentText = "Could not find the Version History file on <strong>{$RemoteUpdateServer->remote_update_url}</strong>.";
                    $downloadInstallerText = '';
                }

                if (!$resultUpdateServerReadWrite) {
                    $resultEncryptionKeyIcon = 'x-square';
                    $resultEncryptionKeyAlert = 'warning';
                    $resultEncryptionKeyText = 'Could not test the Encryption Key/Salt as there is no connection to the Remote Update Server.';
                } elseif (!$resultVersionHistoryHashPresent) {
                    $resultEncryptionKeyIcon = 'x-square';
                    $resultEncryptionKeyAlert = 'warning';
                    $resultEncryptionKeyText = 'Could not test the Encryption Key/Salt because there is no Version History file.';
                } elseif ($resultEncryptionKey) {
                    $resultEncryptionKeyIcon = 'check-square';
                    $resultEncryptionKeyAlert = 'success';
                    $resultEncryptionKeyText = 'Encryption Key/Salt is valid.';
                } else {
                    $resultEncryptionKeyIcon = 'x-square';
                    $resultEncryptionKeyAlert = 'warning';
                    $resultEncryptionKeyText = 'Encryption Key/Salt is not valid.';
                }

                if ($resultPhpBinary) {
                    $resultPhpBinaryIcon = 'check-square';
                    $resultPhpBinaryAlert = 'success';
                    $resultPhpBinaryText = "PHP executable <strong>{$resultPhpBinary}</strong> is valid.";
                } else {
                    $resultPhpBinaryIcon = 'x-square';
                    $resultPhpBinaryAlert = 'warning';
                    $resultPhpBinaryText = "PHP executable not found.";
                }

                if ($resultComposerBinary) {
                    $resultComposerBinaryIcon = 'check-square';
                    $resultComposerBinaryAlert = 'success';
                    $resultComposerBinaryText = "Composer executable <strong>{$resultComposerBinary}</strong> is valid.";
                } else {
                    $resultComposerBinaryIcon = 'x-square';
                    $resultComposerBinaryAlert = 'warning';
                    $resultComposerBinaryText = "Composer executable not found.";
                }

                if ($batPath) {
                    $batPathIcon = 'check-square';
                    $batPathAlert = 'success';
                    $batPathText = "Release builder batch file ready to run <strong>{$batPath}</strong>.";
                } else {
                    $batPathIcon = 'x-square';
                    $batPathAlert = 'warning';
                    $batPathText = "Release builder batch file not found.";
                }
                ?>

                <p>
                    <?= $this->IconMaker->bootstrapIcon($resultUpdateServerReadWriteIcon, additionalClasses: "me-1 text-{$resultUpdateServerReadWriteAlert}"); ?>
                    <?= $resultUpdateServerReadWriteText ?>
                </p>
                <p>
                    <?= $this->IconMaker->bootstrapIcon($resultVersionHistoryHashPresentIcon, additionalClasses: "me-1 text-{$resultVersionHistoryHashPresentAlert}"); ?>
                    <?= $resultVersionHistoryHashPresentText ?>
                </p>
                <?php
                if ($resultVersionHistoryHashPresent) {
                    ?>
                    <p>
                        <?= $this->IconMaker->bootstrapIcon($resultVersionHistoryHashPresentIcon, additionalClasses: "me-1 text-{$resultVersionHistoryHashPresentAlert}"); ?>
                        <?= $downloadInstallerText ?>
                    </p>
                    <?php
                }
                ?>
                <p>
                    <?= $this->IconMaker->bootstrapIcon($resultEncryptionKeyIcon, additionalClasses: "me-1 text-{$resultEncryptionKeyAlert}"); ?>
                    <?= $resultEncryptionKeyText ?>
                </p>
                <p>
                    <?= $this->IconMaker->bootstrapIcon($resultPhpBinaryIcon, additionalClasses: "me-1 text-{$resultPhpBinaryAlert}"); ?>
                    <?= $resultPhpBinaryText ?>
                </p>
                <p>
                    <?= $this->IconMaker->bootstrapIcon($resultComposerBinaryIcon, additionalClasses: "me-1 text-{$resultComposerBinaryAlert}"); ?>
                    <?= $resultComposerBinaryText ?>
                </p>
                <p>
                    <?= $this->IconMaker->bootstrapIcon($batPathIcon, additionalClasses: "me-1 text-{$batPathAlert}"); ?>
                    <?= $batPathText ?>
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

