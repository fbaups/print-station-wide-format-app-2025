<?php
/**
 * @var AppView $this
 * @var string $hSaltCheck
 * @var string $hKeyCheck
 */

use App\Model\Entity\User;
use App\Utility\Releases\RemoteUpdateServer;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Release Builder'));
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
?>

<div class="container-fluid px-4 col-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">
    <div class="card">

        <div class="card-header">
            <?php
            echo __('Release Builder Configuration')
            ?>
        </div>

        <div class="card-body">
            <div>
                <p>Configure how the Release Builder will upload update packages to the Remote Update Server.</p>
                <p>Build a release by running the following in a terminal window:
                    <strong><?= ROOT . '\\bin\\RealeasBuilder.bat' ?></strong>
                </p>
            </div>
            <div class="release-builder form content">
                <?php
                $formOptions = [
                ];

                $newTemplate = [
                    'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                ];
                $this->Form->setTemplates($newTemplate);

                echo $this->Form->create(null, $formOptions)
                ?>
                <fieldset class="border p-3 mb-4">
                    <?php
                    $opts = [
                        'class' => 'form-control',
                        'data-type' => 'string',
                        'label' => ['text' => 'Remote Update Server'],
                        'templateVars' => ['help' => 'URL of where update packages are stored.'],
                        'default' => $RemoteUpdateServer->remote_update_url,
                    ];
                    $this->Form->setTemplates($templates);
                    echo $this->Form->control('remote-update-url', $opts);
                    $this->Form->resetTemplates();
                    ?>
                </fieldset>

                <fieldset class="border p-3 mb-4">
                    <?php
                    $this->Form->setTemplates($templates);
                    $hKeyDefault = $hKeyCheck ? 'true' : '';
                    $opts = [
                        'class' => 'form-control',
                        'type' => 'password',
                        'data-type' => 'password',
                        'templateVars' => ['help' => 'Used to encrypt/decrypt update packages.'],
                        'default' => $hKeyDefault,
                    ];
                    echo $this->Form->control('encryption-key', $opts);

                    $hSaltDefault = $hSaltCheck ? 'true' : '';
                    $opts = [
                        'label' => ['class' => 'mt-3'],
                        'class' => 'form-control',
                        'type' => 'password',
                        'data-type' => 'password',
                        'templateVars' => ['help' => 'Used to encrypt/decrypt update packages.'],
                        'default' => $hSaltDefault,
                    ];
                    echo $this->Form->control('encryption-salt', $opts);
                    $this->Form->resetTemplates();
                    ?>
                </fieldset>

                <fieldset class="border p-3">
                    <?php

                    if ($RemoteUpdateServer->remote_update_b2_bucket) {
                        $currentMode = 'backblaze';
                    } elseif ($RemoteUpdateServer->remote_update_unc) {
                        $currentMode = 'unc';
                    } elseif ($RemoteUpdateServer->remote_update_sftp_host) {
                        $currentMode = 'sftp';
                    } else {
                        $currentMode = null;
                    }

                    $this->Form->setTemplates($templates);
                    $options = [
                        'label' => ['text' => 'Remote Update Server Connection Method'],
                        'class' => 'form-control',
                        'templateVars' => ['help' => 'How update packages are uploaded to the Remote Update Server. Uploading of packages will use any of the methods as specified in the above order.'],
                        'options' => [
                            'backblaze' => 'Backblaze Bucket Storage',
                            'unc' => 'Via UNC File Path',
                            'sftp' => 'Via sFTP Transfer',
                        ],
                        'default' => $currentMode,
                    ];
                    echo $this->Form->control('remote-update-connection-method', $options);
                    ?>

                    <div class="connection-method-unc d-none">
                        <?php
                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'Remote Update Server UNC Path'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'UNC path to the Remote Update Server'],
                            'default' => $RemoteUpdateServer->remote_update_unc,
                        ];
                        echo $this->Form->control('remote-update-unc', $opts);
                        ?>
                    </div>

                    <div class="connection-method-sftp d-none">
                        <?php
                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'sFTP Host'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Fully qualified sFTP host name'],
                            'default' => $RemoteUpdateServer->remote_update_sftp_host,
                        ];
                        echo $this->Form->control('remote-update-sftp-host', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'sFTP Port'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Port number for the sFTP host'],
                            'default' => $RemoteUpdateServer->remote_update_sftp_port,
                        ];
                        echo $this->Form->control('remote-update-sftp-port', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'sFTP Username'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Username for the sFTP host'],
                            'default' => $RemoteUpdateServer->remote_update_sftp_username,
                        ];
                        echo $this->Form->control('remote-update-sftp-username', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'sFTP Password'],
                            'class' => 'form-control',
                            'type' => 'password',
                            'data-type' => 'password',
                            //'templateVars' => ['help' => 'Password for the sFTP host'],
                            'default' => $RemoteUpdateServer->remote_update_sftp_password,
                        ];
                        echo $this->Form->control('remote-update-sftp-password', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'sFTP Timeout'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Timeout for the sFTP host'],
                            'default' => $RemoteUpdateServer->remote_update_sftp_timeout,
                        ];
                        echo $this->Form->control('remote-update-sftp-timeout', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'sFTP Path'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Path on the sFTP host'],
                            'default' => $RemoteUpdateServer->remote_update_sftp_path,
                        ];
                        echo $this->Form->control('remote-update-sftp-path', $opts);
                        ?>
                    </div>

                    <div class="connection-method-backblaze d-none">
                        <?php
                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'B2 Key ID'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Backblaze B2 Key ID'],
                            'default' => $RemoteUpdateServer->remote_update_b2_key_id,
                        ];
                        echo $this->Form->control('remote-update-b2-key-id', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'B2 Key'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Backblaze B2 Key'],
                            'default' => $RemoteUpdateServer->remote_update_b2_key,
                        ];
                        echo $this->Form->control('remote-update-b2-key', $opts);

                        $opts = [
                            'label' => ['class' => 'mt-3', 'text' => 'B2 Bucket ID'],
                            'class' => 'form-control',
                            'data-type' => 'text',
                            //'templateVars' => ['help' => 'Backblaze B2 Bucket ID'],
                            'default' => $RemoteUpdateServer->remote_update_b2_bucket,
                        ];
                        echo $this->Form->control('remote-update-b2-bucket', $opts);
                        ?>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => '/'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
            <?php
            echo $this->Form->end();
            $this->Form->resetTemplates();
            ?>
        </div>

    </div>
</div>

<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {

        exposeConnectionMethod();

        $('select[name="remote-update-connection-method"]').on('click change', function () {
            exposeConnectionMethod();
        });

        function exposeConnectionMethod() {
            var connectionMethod = $('select[name="remote-update-connection-method"]');
            var connectionMethodValue = connectionMethod.val();

            if (connectionMethodValue === 'unc') {
                $('div.connection-method-unc').removeClass('d-none');
                $('div.connection-method-sftp').addClass('d-none');
                $('div.connection-method-backblaze').addClass('d-none');
            } else if (connectionMethodValue === 'sftp') {
                $('div.connection-method-unc').addClass('d-none');
                $('div.connection-method-sftp').removeClass('d-none');
                $('div.connection-method-backblaze').addClass('d-none');
            } else if (connectionMethodValue === 'backblaze') {
                $('div.connection-method-unc').addClass('d-none');
                $('div.connection-method-sftp').addClass('d-none');
                $('div.connection-method-backblaze').removeClass('d-none');
            }
        }
    });
</script>
<?php
$this->end();
?>

