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
    'inputContainer' => '<div class="input settings email-server {{type}}{{required}}">{{content}} <small class="form-text text-muted">{{help}}</small></div>',
];
$this->Form->setTemplates($templates);
?>

<div class="container px-4 mt-5">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-8 col-md-10 col-12">
            <?= $this->Form->create(null) ?>
            <div class="card">
                <div class="card-header">
                    <?= __('{0} Settings', $groupNameHuman) ?>
                </div>
                <div class="card-body">
                    <?= $this->Form->hidden('forceRefererRedirect', ['value' => $this->request->referer(false)]); ?>
                    <fieldset>
                        <?php
                        foreach ($settingsKeyed as $setting) {
                            echo '<div class="mb-4">';
                            $tmpOptions = $defaultOptions;
                            $tmpOptions = $this->Form->settingsFormatOptions($tmpOptions, $setting);
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
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

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
                        'label' => ['text' => 'Test Send To'],
                        'class' => 'form-control mb-2',
                        'value' => $this->AuthUser->user('email'),
                    ];
                    echo $this->Form->control('test_send_to', $opts);

                    $opts = [
                        'label' => ['text' => 'Test Subject'],
                        'class' => 'form-control mb-2',
                        'value' => __("Test Email From {0}", APP_NAME),
                    ];
                    echo $this->Form->control('test_subject', $opts);

                    $opts = [
                        'label' => ['text' => 'Test Body (HTML allowed)'],
                        'class' => 'form-control mb-4',
                        'type' => 'textarea',
                        'value' => __("Hello <strong>{0}</strong>.\n<br>\n<br>\nThis is a test email.", $this->AuthUser->getFulName()),
                    ];
                    echo $this->Form->control('test_body', $opts);

                    $btnOpts = [
                        'class' => 'form-control btn btn-secondary test-email'
                    ];
                    echo $this->Form->button('Test Settings', $btnOpts);
                    ?>
                    <div class="test-email-results">
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

        $('.test-email').off('click').on('click', function () {
            var emailSettings = {};
            var holder = $('div.email-server');

            holder.find('input').each(function () {
                emailSettings[$(this).attr('name')] = $(this).val();
            });

            holder.find('textarea').each(function () {
                emailSettings[$(this).attr('name')] = $(this).val();
            });

            holder.find('select').each(function () {
                emailSettings[$(this).attr('name')] = $(this).val();
            });

            //check if the user has changed the password
            var originalPassword = holder.find('#setting_email_password').attr('data-original-value');
            var currentPassword = holder.find('#setting_email_password').val();
            emailSettings['email_password_is_hashed'] = (originalPassword === currentPassword);

            //convert specific values to Boolean
            if (emailSettings['email_tls'].toLowerCase() === 'false') {
                emailSettings['email_tls'] = false;
            } else {
                emailSettings['email_tls'] = Boolean(emailSettings['email_tls']);
            }

            var jsonData = (JSON.stringify(emailSettings));

            $.ajax({
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                type: "POST",
                url: homeUrl + 'administrators/settings/test-email-server',
                async: true,
                contentType: 'application/json',
                dataType: "json",
                data: jsonData,
                cache: false,
                processData: false,
                timeout: 60000,
                beforeSend: function () {
                    $('.test-email-results').html('<div class="alert my-4">&nbsp;</div>')
                },
                success: function (response) {
                    //console.log(response);
                    if (response['status'] === false) {
                        $('.test-email-results').html('<div class="alert alert-danger my-4">' + response['message'] + '</div>')
                    } else {
                        $('.test-email-results').html('<div class="alert alert-success my-4">Email Sent. Please check your Inbox.</div>')
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
