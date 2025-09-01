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
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Release Builder Check'));
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
                $resultVersionHistoryHashPresent = $RemoteUpdateServer->isVersionHistoryHashOnServer();
                $resultEncryptionKey = $RemoteUpdateServer->getVersionHistoryHash();
                $resultUpdateServerReadWrite = $RemoteUpdateServer->getRemoteUpdateServer();
                $resultPhpBinary = $InstanceTasks->getPhpBinary();
                $resultComposerBinary = $InstanceTasks->getComposerBinary();

                if ($resultVersionHistoryHashPresent) {
                    $resultVersionHistoryHashPresentIcon = 'check';
                    $resultVersionHistoryHashPresentAlert = 'success';
                    $resultVersionHistoryHashPresentText = "Remote Update Server URL <strong>{$RemoteUpdateServer->remote_update_url}</strong> is valid";
                } else {
                    $resultVersionHistoryHashPresentIcon = 'x';
                    $resultVersionHistoryHashPresentAlert = 'warning';
                    $resultVersionHistoryHashPresentText = "Remote Update Server URL <strong>{$RemoteUpdateServer->remote_update_url}</strong> is not valid";
                }

                if ($resultEncryptionKey) {
                    $resultEncryptionKeyIcon = 'check';
                    $resultEncryptionKeyAlert = 'success';
                    $resultEncryptionKeyText = 'Encryption Key/Salt is valid';
                } else {
                    $resultEncryptionKeyIcon = 'x';
                    $resultEncryptionKeyAlert = 'warning';
                    $resultEncryptionKeyText = 'Encryption Key/Salt is not valid';
                }

                if ($resultUpdateServerReadWrite) {
                    $resultUpdateServerReadWriteIcon = 'check';
                    $resultUpdateServerReadWriteAlert = 'success';
                    $resultUpdateServerReadWriteText = 'Connection to Remote Update Server succeeded';
                } else {
                    $resultUpdateServerReadWriteIcon = 'x';
                    $resultUpdateServerReadWriteAlert = 'warning';
                    $resultUpdateServerReadWriteText = 'Connection to Remote Update Server failed';
                }

                if ($resultPhpBinary) {
                    $resultPhpBinaryIcon = 'check';
                    $resultPhpBinaryAlert = 'success';
                    $resultPhpBinaryText = "PHP executable <strong>{$resultPhpBinary}</strong> is valid";
                } else {
                    $resultPhpBinaryIcon = 'x';
                    $resultPhpBinaryAlert = 'warning';
                    $resultPhpBinaryText = "PHP executable not found";
                }

                if ($resultComposerBinary) {
                    $resultComposerBinaryIcon = 'check';
                    $resultComposerBinaryAlert = 'success';
                    $resultComposerBinaryText = "Composer executable <strong>{$resultComposerBinary}</strong> is valid";
                } else {
                    $resultComposerBinaryIcon = 'x';
                    $resultComposerBinaryAlert = 'warning';
                    $resultComposerBinaryText = "Composer executable not found";
                }

                if ($batPath) {
                    $batPathIcon = 'check';
                    $batPathAlert = 'success';
                    $batPathText = "Release builder batch file ready to run <strong>{$batPath}</strong>";
                } else {
                    $batPathIcon = 'x';
                    $batPathAlert = 'warning';
                    $batPathText = "Release builder batch file not found";
                }
                ?>

                <p>
                    <i class="ms-0 me-1 text-<?= $resultVersionHistoryHashPresentAlert ?>" data-feather="<?= $resultVersionHistoryHashPresentIcon ?>"></i>
                    <?= $resultVersionHistoryHashPresentText ?>
                </p>
                <p>
                    <i class="ms-0 me-1 text-<?= $resultEncryptionKeyAlert ?>" data-feather="<?= $resultEncryptionKeyIcon ?>"></i>
                    <?= $resultEncryptionKeyText ?>
                </p>
                <p>
                    <i class="ms-0 me-1 text-<?= $resultUpdateServerReadWriteAlert ?>" data-feather="<?= $resultUpdateServerReadWriteIcon ?>"></i>
                    <?= $resultUpdateServerReadWriteText ?>
                </p>
                <p>
                    <i class="ms-0 me-1 text-<?= $resultPhpBinaryAlert ?>" data-feather="<?= $resultPhpBinaryIcon ?>"></i>
                    <?= $resultPhpBinaryText ?>
                </p>
                <p>
                    <i class="ms-0 me-1 text-<?= $resultComposerBinaryAlert ?>" data-feather="<?= $resultComposerBinaryIcon ?>"></i>
                    <?= $resultComposerBinaryText ?>
                </p>
                <p>
                    <i class="ms-0 me-1 text-<?= $batPathAlert ?>" data-feather="<?= $batPathIcon ?>"></i>
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

