<?php
/**
 * @var AppView $this
 * @var User $user
 * @var string[]|CollectionInterface $userStatuses
 * @var string[]|CollectionInterface $roles
 * @var Setting[] $localizationSettings
 */

use App\Model\Entity\Setting;
use App\Model\Entity\User;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Update My Profile'));
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

if ($this->AuthUser->hasRoles(['operator', 'manager'])) {
    $coreLib = [
        'base' => false,
        'bootstrap' => false,
        'datatables' => true,
        'feather-icons' => true,
        'fontawesome' => true,
        'jQuery' => true,
        'jQueryUI' => false,
    ];
}
$this->set('coreLib', $coreLib);

?>

<?php
$this->append('backLink');
?>
<?php
if ($this->AuthUser->hasAccess(['action' => 'index'])) {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Users'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
    </div>
    <?php
} else {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Home'), ['controller' => '/'], ['class' => '', 'escape' => false]) ?>
    </div>
    <?php
}
?>
<?php
$this->end();
?>
<div class="spacer-50"></div>

<div class="container-fluid px-4 col-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">
    <div class="card">

        <div class="card-header">
            <?php
            echo '<span class="first-name-holder">' . $user->first_name . '</span>';
            echo ' ';
            echo '<span class="last-name-holder">' . $user->last_name . '</span>';
            ?>
        </div>

        <div class="card-body">
            <div class="users form content">
                <?= $this->Form->create($user) ?>
                <fieldset>
                    <h3>Profile Settings</h3>

                    <div class="row mt-0">
                        <div class="col-12 col-md-6 mt-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('email', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'label' => ['text' => 'Mobile Number'],
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('mobile', $opts);
                            ?>
                        </div>
                    </div>


                    <div class="row mt-0 mt-md-4">
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('username', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'value' => sha1($user->password),
                            ];
                            echo $this->Form->control('password', $opts);
                            ?>
                        </div>
                    </div>


                    <div class="row mt-0 mt-md-4">
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('first_name', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('last_name', $opts);
                            ?>
                        </div>
                    </div>


                    <h3 class="mt-5">Contact Details</h3>

                    <div class="row mt-0">
                        <div class="col-12 col-md-6 mt-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('address_1', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'label' => ['text' => 'Address 2 (optional)'],
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('address_2', $opts);
                            ?>
                        </div>
                    </div>


                    <div class="row mt-0 mt-md-4">
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('suburb', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-3 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('state', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-3 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('post_code', $opts);
                            ?>
                        </div>
                    </div>

                    <div class="row mt-0 mt-md-4">
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('country', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'label' => ['text' => 'Phone (landline)'],
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('phone', $opts);
                            ?>
                        </div>
                    </div>


                    <h3 class="mt-5">Localisation Settings</h3>
                    <?php
                    $localizationSettingsKeyed = [];
                    /** @var Setting $localizationSetting */
                    foreach ($localizationSettings as $localizationSetting) {
                        $localizationSettingsKeyed[$localizationSetting->property_key] = $localizationSetting;
                    }
                    $opts = [
                        'type' => 'hidden',
                    ];
                    echo $this->Form->control('user_localizations.0.id', $opts);
                    echo $this->Form->control('user_localizations.0.user_id', $opts);
                    ?>

                    <div class="row mt-0">
                        <div class="col-12 col-md-6 mt-0">
                            <?php
                            $opts = [
                                'class' => 'form-control',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control('user_localizations.0.location', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'type' => 'select',
                                'empty' => false,
                                'default' => LCL_TZ,
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'options' => json_decode($localizationSettingsKeyed['timezone']['selections'], JSON_OBJECT_AS_ARRAY)
                            ];
                            echo $this->Form->control('user_localizations.0.timezone', $opts);
                            ?>
                        </div>
                    </div>

                    <div class="row mt-0 mt-md-4">
                        <div class="col-12 col-md-4 mt-3 mt-md-0">
                            <?php
                            $dtObj = new DateTime('now', LCL_TZ);

                            $selectOpts = json_decode($localizationSettingsKeyed['date_format']['selections'], JSON_OBJECT_AS_ARRAY);
                            foreach ($selectOpts as $header => $selectOptData) {
                                foreach ($selectOptData as $k => $selectOpt) {
                                    $selectOpts[$header][$k] = $dtObj->format($k);
                                }
                            }
                            $opts = [
                                'type' => 'select',
                                'empty' => false,
                                'default' => LCL_DF,
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'options' => $selectOpts
                            ];
                            echo $this->Form->control('user_localizations.0.date_format', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-4 mt-3 mt-md-0">
                            <?php
                            $selectOpts = json_decode($localizationSettingsKeyed['time_format']['selections'], JSON_OBJECT_AS_ARRAY);
                            foreach ($selectOpts as $header => $selectOptData) {
                                foreach ($selectOptData as $k => $selectOpt) {
                                    $selectOpts[$header][$k] = $dtObj->format($k);
                                }
                            }
                            $opts = [
                                'type' => 'select',
                                'empty' => false,
                                'default' => LCL_TF,
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'options' => $selectOpts
                            ];
                            echo $this->Form->control('user_localizations.0.time_format', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-4 mt-3 mt-md-0">
                            <?php
                            $selectOpts = json_decode($localizationSettingsKeyed['datetime_format']['selections'], JSON_OBJECT_AS_ARRAY);
                            foreach ($selectOpts as $header => $selectOptData) {
                                foreach ($selectOptData as $k => $selectOpt) {
                                    $selectOpts[$header][$k] = $dtObj->format($k);
                                }
                            }
                            $opts = [
                                'type' => 'select',
                                'empty' => false,
                                'default' => LCL_DTF,
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'options' => $selectOpts
                            ];
                            echo $this->Form->control('user_localizations.0.datetime_format', $opts);
                            ?>
                        </div>
                    </div>

                    <div class="row mt-0 mt-md-4">
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'type' => 'select',
                                'empty' => false,
                                'default' => LCL_WS,
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'options' => json_decode($localizationSettingsKeyed['week_start']['selections'], JSON_OBJECT_AS_ARRAY)
                            ];
                            echo $this->Form->control('user_localizations.0.week_start', $opts);
                            ?>
                        </div>
                        <div class="col-12 col-md-6 mt-3 mt-md-0">
                            <?php
                            $opts = [
                                'type' => 'select',
                                'empty' => false,
                                'default' => LCL_LOCALE,
                                'class' => 'form-control',
                                'data-type' => 'string',
                                'options' => json_decode($localizationSettingsKeyed['locale']['selections'], JSON_OBJECT_AS_ARRAY)
                            ];
                            echo $this->Form->control('user_localizations.0.locale', $opts);
                            ?>
                        </div>
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
            <?= $this->Form->end() ?>
        </div>

    </div>
</div>

<div class="spacer-50"></div>

<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        var firstnameField = $("input[name*='first_name']");
        var lastnameField = $("input[name*='last_name']");

        firstnameField.keyup(function () {
            $('.first-name-holder').text(this.value);
        });

        lastnameField.keyup(function () {
            $('.last-name-holder').text(this.value);
        });
    });
</script>
<?php
$this->end();
?>
