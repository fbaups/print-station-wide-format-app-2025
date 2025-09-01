<?php
/**
 * @var AppView $this
 * @var Setting $setting
 *
 * @var string $activationInformation
 * @var string $trialRegistrationName
 * @var string $trialCompanyName
 *
 */

use App\Model\Entity\Setting;
use App\View\AppView;
use Cake\Core\Configure\Engine\PhpConfig;

?>

<?php
$labelClass = 'col-8 form-control-label pl-0 mb-1';
$inputClass = 'form-control mb-3';

$defaultOptions = [
    'label' => [
        'class' => $labelClass,
    ],
    'options' => null,
    'class' => $inputClass,
];
?>

<div class="row">
    <div class="col-md-12 col-xl-10 m-xl-auto">
        <div class="users">
            <?= $this->Form->create() ?>
            <div class="card">
                <div class="card-header">
                    <legend><?= __('Request Trial Activation') ?></legend>
                </div>

                <div class="card-body">
                    <?php
                    if (!empty($activationInformation)) {
                        echo '<div class="border p-5 mb-5">';
                        echo '<pre class="text-black m-0 p-0">' . $activationInformation . '</pre>';
                        echo '</div>';
                    }
                    ?>
                    <fieldset>
                        <?php
                        $registrationNameOptions = $defaultOptions;
                        $registrationNameOptions['label']['text'] = 'Registration Name';
                        $registrationNameOptions['value'] = $trialRegistrationName;
                        echo $this->Form->control('trial_registration_name', $registrationNameOptions);
                        ?>

                        <?php
                        $companyNameOptions = $defaultOptions;
                        $companyNameOptions['label']['text'] = 'Company Name';
                        $companyNameOptions['value'] = $trialCompanyName;
                        echo $this->Form->control('trial_company_name', $companyNameOptions);
                        ?>
                    </fieldset>
                </div>
                <div class="card-footer">
                    <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary float-start']) ?>
                    <?= $this->Form->button(__('Create Request Info'), ['class' => 'btn btn-primary float-end']) ?>
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<?php
//restore the original templates
$this->Form->resetTemplates();
?>

