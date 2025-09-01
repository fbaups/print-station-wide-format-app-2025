<?php
/**
 * @var AppView $this
 * @var bool $isNssm
 * @var Setting $errandBackgroundServiceSetting
 * @var Setting $messageBackgroundServiceSetting
 * @var Setting $databasePurgerBackgroundServiceSetting
 * @var Setting $hotFolderBackgroundServiceSetting
 * @var Setting $scheduledTaskBackgroundServiceSetting
 * @var string $serviceNamePrefix
 * @var string $phpVersionMatchToIis
 */

use App\Model\Entity\Setting;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __('house-gear'));
$this->set('headerTitle', __('Background Services'));
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
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4"><small class="form-text text-muted">{{help}}</small></div></div>',
];
?>

<?php

if (!$isNssm) {
    return;
}

?>
<div class="container-fluid px-4">
    <?php
    $formOpts = [];
    echo $this->Form->create(null, $formOpts);
    ?>
    <div class="card mb-5">
        <div class="card-header">
            <?= __('Installation Options') ?>
        </div>
        <div class="card-body">
            <div class="background-services install content">
                <?php
                $labelClass = 'form-control-label';
                $inputClass = 'form-control mb-4';
                $checkboxClass = 'mr-2 mb-4';

                $defaultOptions = [
                    'label' => [
                        'class' => $labelClass,
                    ],
                    'options' => null,
                    'class' => $inputClass,
                ];

                $checkboxOptions = array_merge($defaultOptions, ['class' => $checkboxClass]);

                $serviceStartTypes = [
                    'SERVICE_AUTO_START' => 'Automatic startup',
                    'SERVICE_DELAYED_START' => 'Delayed startup',
                    'SERVICE_DEMAND_START' => 'Manual startup',
                    'SERVICE_DISABLED' => 'Service is disabled'
                ];

                $phpOptions = $defaultOptions;
                $phpOptions['options'] = [$phpVersionMatchToIis => $phpVersionMatchToIis];
                $phpOptions['default'] = $phpVersionMatchToIis;
                $phpOptions['label']['text'] = __("Select PHP Version");

                $usernameStartOptions = $defaultOptions;
                $usernameStartOptions['class'] = 'form-control';
                $usernameStartOptions['label']['text'] = __("Username the Windows Service will run under (Optional)");
                $usernameStartOptions['templateVars'] = ['help' => 'Best to use domain\user syntax e.g. .\WebAdmin for a local account.'];

                $passwordStartOptions = $defaultOptions;
                $passwordStartOptions['label']['text'] = __("Password the Windows Service will run under (Optional)");

                $serviceStartOptions = $defaultOptions;
                $serviceStartOptions['options'] = $serviceStartTypes;
                $serviceStartOptions['label']['text'] = __("Services Start Options on Server Start/Reboot");

                $errandOptions = $defaultOptions;
                $errandOptions['options'] = json_decode($errandBackgroundServiceSetting->selections, JSON_OBJECT_AS_ARRAY);
                $errandOptions['default'] = $errandBackgroundServiceSetting->property_value;
                $errandOptions['label']['text'] = __("Number of Background Services to process Errands");

                $messageOptions = $defaultOptions;
                $messageOptions['options'] = json_decode($messageBackgroundServiceSetting->selections, JSON_OBJECT_AS_ARRAY);
                $messageOptions['default'] = $messageBackgroundServiceSetting->property_value;
                $messageOptions['label']['text'] = __("Number of Background Services to process Messages");

                $databasePurgeOptions = $defaultOptions;
                $databasePurgeOptions['options'] = json_decode($databasePurgerBackgroundServiceSetting->selections, JSON_OBJECT_AS_ARRAY);
                $databasePurgeOptions['default'] = $databasePurgerBackgroundServiceSetting->property_value;
                $databasePurgeOptions['label']['text'] = __("Number of Background Services to perform cleanup operations");

                $hotFolderBackgroundServiceOptions = $defaultOptions;
                $hotFolderBackgroundServiceOptions['options'] = json_decode($hotFolderBackgroundServiceSetting->selections, JSON_OBJECT_AS_ARRAY);
                $hotFolderBackgroundServiceOptions['default'] = $hotFolderBackgroundServiceSetting->property_value;
                $hotFolderBackgroundServiceOptions['label']['text'] = __("Number of Background Services to process Hot Folders");

                $scheduledTaskBackgroundServiceOptions = $defaultOptions;
                $scheduledTaskBackgroundServiceOptions['options'] = json_decode($scheduledTaskBackgroundServiceSetting->selections, JSON_OBJECT_AS_ARRAY);
                $scheduledTaskBackgroundServiceOptions['default'] = $scheduledTaskBackgroundServiceSetting->property_value;
                $scheduledTaskBackgroundServiceOptions['label']['text'] = __("Number of Background Services to process Scheduled Tasks");

                ?>
                <p>Background Services will have a prefix of <strong><?= $serviceNamePrefix ?>_</strong> in the name.
                </p>
                <fieldset>
                    <?php
                    $this->Form->setTemplates($templates);
                    echo $this->Form->control('php_version', $phpOptions);
                    echo $this->Form->control('username', $usernameStartOptions);
                    echo $this->Form->control('password', $passwordStartOptions);
                    echo $this->Form->control('service_start', $serviceStartOptions);
                    $this->Form->resetTemplates();

                    echo $this->Form->control('errand_background_service_limit', $errandOptions);
                    echo $this->Form->control('message_background_service_limit', $messageOptions);
                    echo $this->Form->control('hot_folder_background_service_limit', $hotFolderBackgroundServiceOptions);
                    echo $this->Form->control('scheduled_task_background_service_limit', $scheduledTaskBackgroundServiceOptions);
                    echo $this->Form->control('database_purger_background_service_limit', $databasePurgeOptions);
                    ?>
                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'background-services'], $options);

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
