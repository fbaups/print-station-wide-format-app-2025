<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\ORM\Query $settings
 * @var \App\Model\Entity\Setting $setting
 * @var \App\Model\Entity\Setting[] $settingsKeyed
 *
 * @var string $groupName
 * @var string $groupNameHuman
 *
 */

use App\MessageGateways\SmsGatewayFactory;
use Cake\Core\Configure\Engine\PhpConfig;


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
    'inputContainer' => '<div class="input settings sms-gateway {{type}}{{required}}">{{content}} <small class="form-text text-muted">{{help}}</small></div>',
];
$this->Form->setTemplates($templates);
?>

<div class="container px-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-12">
            <div class="card">
                <div class="card-header">
                    <?= __('{0} Settings', $groupNameHuman) ?>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null) ?>
                    <?= $this->Form->hidden('forceRefererRedirect', ['value' => $this->request->referer(false)]); ?>
                    <fieldset>
                        <?php
                        foreach ($settingsKeyed as $setting) {
                            echo '<div class="mb-4">';
                            $tmpOptions = $defaultOptions;
                            $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $setting);

                            if ($setting->property_key === 'sms_gateway_provider') {
                                $list = (new SmsGatewayFactory())->getSmsGatewayClasses();
                                $tmpOptions['type'] = 'select';
                                $tmpOptions['options'] = $list;
                            }

                            echo $this->Form->control('property_value', $tmpOptions);
                            echo '</div>';
                        }
                        ?>
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
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

//dd($this->AuthUser);

?>

<div class="container px-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-12">
            <div class="card">
                <div class="card-header">
                    <?= __('Test {0} Settings', $groupNameHuman) ?>
                </div>
                <div class="card-body">
                    <p>Press the Test button to validate the above settings.</p>
                    <?php
                    $opts = [
                        'label' => ['text' => 'Mobile Number'],
                        'class' => 'form-control mb-2',
                        'value' => $this->AuthUser->user('mobile'),
                    ];
                    echo $this->Form->control('test_mobile', $opts);

                    $opts = [
                        'label' => ['text' => 'Test Message'],
                        'class' => 'form-control mb-2',
                        'value' => __("Test Message From {0}", APP_NAME),
                    ];
                    echo $this->Form->control('test_message', $opts);

                    $btnOpts = [
                        'class' => 'form-control btn btn-secondary test-sms-gateway'
                    ];
                    echo $this->Form->button('Test Settings', $btnOpts);
                    ?>
                    <div class="test-sms-gateway-results">
                        <div class="alert my-4">&nbsp;</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
//restore the original templates
$this->Form->resetTemplates();
?>



<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {

        $('.test-sms-gateway').off('click').on('click', function () {
            var smsGatewaySettings = {};
            var holder = $('div.sms-gateway');

            holder.find('input').each(function () {
                smsGatewaySettings[$(this).attr('name')] = $(this).val();
            });

            holder.find('textarea').each(function () {
                smsGatewaySettings[$(this).attr('name')] = $(this).val();
            });

            holder.find('select').each(function () {
                smsGatewaySettings[$(this).attr('name')] = $(this).val();
            });

            //check if the user has changed the password key
            var originalPassword = holder.find('#setting_sms_gateway_password').attr('data-original-value');
            var currentPassword = holder.find('#setting_sms_gateway_password').val();
            smsGatewaySettings['sms_gateway_password_is_hashed'] = (originalPassword === currentPassword);

            //check if the user has changed the api key
            var originalApiKey = holder.find('#setting_sms_gateway_api_key').attr('data-original-value');
            var currentApiKey = holder.find('#setting_sms_gateway_api_key').val();
            smsGatewaySettings['sms_gateway_api_key_is_hashed'] = (originalApiKey === currentApiKey);

            var jsonData = (JSON.stringify(smsGatewaySettings));

            $.ajax({
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                type: "POST",
                url: homeUrl + 'settings/test-sms-gateway',
                async: true,
                contentType: 'application/json',
                dataType: "json",
                data: jsonData,
                cache: false,
                processData: false,
                timeout: 60000,
                beforeSend: function () {
                    $('.test-sms-gateway-results').html('<div class="alert my-4">&nbsp;</div>')
                },
                success: function (response) {
                    //console.log(response);
                    if (response['status'] === false) {
                        $('.test-sms-gateway-results').html('<div class="alert alert-danger my-4">' + response['message'] + '</div>')
                    } else {
                        $('.test-sms-gateway-results').html('<div class="alert alert-success my-4">SMS Sent. Please check your mobile device.</div>')
                    }
                },
                error: function (e) {
                    //alert("An error occurred: " + e.responseText.message);
                    //console.log(e);
                },
                complete: function (e) {
                }
            })

        });

    });
</script>
<?php
$this->end();
?>
