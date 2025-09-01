<?php
/**
 * @var AppView $this
 * @var Query $settings
 * @var Setting $setting
 * @var Setting[] $settingsKeyed
 *
 * @var string $groupName
 * @var string $groupNameHuman
 *
 * @var string $remote_update_unc
 * @var string $remote_update_sftp_host
 * @var string $remote_update_sftp_port
 * @var string $remote_update_sftp_username
 * @var string $remote_update_sftp_password
 * @var string $remote_update_sftp_timeout
 * @var string $remote_update_sftp_path
 *
 * @var string $repo_unc
 * @var string $repo_url
 * @var string $repo_sftp_host
 * @var string $repo_sftp_port
 * @var string $repo_sftp_username
 * @var string $repo_sftp_password
 * @var string $repo_sftp_timeout
 * @var string $repo_sftp_path
 * @var bool $isURL
 * @var bool $isSFTP
 * @var bool $isUNC
 * @var array $remoteUpdateDebug
 */

use App\Model\Entity\Setting;
use App\View\AppView;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\ORM\Query;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit {0} Settings', $groupNameHuman));
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
?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Settings'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<?php
$labelClass = 'col-8 form-control-label pl-0 mb-1';
$inputClass = 'form-control mb-0';

$defaultOptions = [
    'label' => [
        'class' => $labelClass,
    ],
    'options' => null,
    'class' => $inputClass,
];

$settingsKeyed = [];
foreach ($settings as $setting) {
    $settingsKeyed[$setting->property_key] = $setting;
}

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <small class="form-text text-muted">{{help}}</small></div>',
];
$this->Form->setTemplates($templates);
?>

<div class="container px-4">
    <div class="card">
        <div class="card-header">
            <?= __('Connection Test Results') ?>
        </div>
        <div class="card-body">

            <p>
                <?php
                if ($isURL) {
                    echo __("Connection to URL <strong>{0}</strong> established.", $repo_url);
                } else {
                    echo __("Could not connect to URL <strong>{0}</strong>.", $repo_url);
                }
                ?>
            </p>

            <p>
                <?php
                if ($isSFTP) {
                    echo __("Round trip connection to SFTP <strong>{0}@{1}:{2}</strong> established.", $repo_sftp_username, $repo_sftp_host, $repo_sftp_port);
                } else {
                    echo __("Could not connect to SFTP <strong>{0}@{1}:{2}</strong>.", $repo_sftp_username, $repo_sftp_host, $repo_sftp_port);
                }
                ?>
            </p>

            <p>
                <?php
                if ($isUNC) {
                    echo __("Round trip connection to UNC path <strong>{0}</strong> established.", $repo_unc);
                } else {
                    echo __("Could not connect to UNC path <strong>{0}</strong>.", $repo_unc);
                }
                ?>
            </p>

            <?php
            if (!$isSFTP || !$isUNC || !$isURL) {
                ?>
                <div class="card pb-0">
                    <div class="card-body">
                        <code>
                            <?php
                            echo __("<span class=\"text-dark\" >Debugging information...</span><br>");

                            foreach ($remoteUpdateDebug as $item) {
                                if (str_contains($item, "SUCCESS")) {
                                    $colour = 'success';
                                } elseif (str_contains($item, "DANGER")) {
                                    $colour = 'danger';
                                } elseif (str_contains($item, "WARNING")) {
                                    $colour = 'warning';
                                } elseif (str_contains($item, "INFO")) {
                                    $colour = 'info';
                                } else {
                                    $colour = 'info';
                                }
                                echo __("<span class=\"text-{$colour}\" >{$item}</span>");
                                echo "<br>";
                            }
                            ?>
                        </code>
                    </div>
                </div>
                <?php
            }
            ?>

        </div>
    </div>
</div>

<div class="container px-4 mt-5">
    <?= $this->Form->create(null) ?>
    <div class="card">
        <div class="card-header">
            <?= __('{0} Settings', $groupNameHuman) ?>
        </div>
        <div class="card-body">
            <?= $this->Form->hidden('forceRefererRedirect', ['value' => $this->request->referer(false)]); ?>
            <fieldset>
                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_unc']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_url']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                    </div>
                </div>


                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_mode']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_purge']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_sftp_host']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_sftp_port']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_sftp_username']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_sftp_password']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_sftp_timeout']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_sftp_path']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_size_icon']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_size_thumbnail']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_size_preview']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_size_lr']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_size_mr']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                        <?php
                        $tmpOptions = $defaultOptions;
                        $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $settingsKeyed['repo_size_hr']);
                        echo $this->Form->control('property_value', $tmpOptions);
                        ?>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'settings'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

<?php
//restore the original templates
$this->Form->resetTemplates();
?>

