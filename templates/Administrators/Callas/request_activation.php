<?php
/**
 * @var AppView $this
 * @var Setting $setting
 *
 * @var string $activationInformation
 * @var string $registrationName
 * @var string $companyName
 * @var string $licenseKey
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
            <?php
            $formOpts = ['type' => 'file'];
            echo $this->Form->create(null, $formOpts);
            ?>
            <div class="card">
                <div class="card-header">
                    <legend><?= __('Request Activation') ?></legend>
                </div>
                <div class="card-body">
                    <?php
                    if (!empty($activationInformation)) {
                        echo '<pre class="text-info">' . $activationInformation . '</pre>';
                    }
                    ?>
                    <fieldset>
                        <?php
                        $registrationNameOptions = $defaultOptions;
                        $registrationNameOptions['label']['text'] = 'Registration Name';
                        $registrationNameOptions['value'] = $registrationName;
                        echo $this->Form->control('registration_name', $registrationNameOptions);
                        ?>

                        <?php
                        $companyNameOptions = $defaultOptions;
                        $companyNameOptions['label']['text'] = 'Company Name';
                        $companyNameOptions['value'] = $companyName;
                        echo $this->Form->control('company_name', $companyNameOptions);
                        ?>

                        <?php
                        $licenseUploadOptions = $defaultOptions;
                        $licenseUploadOptions['label']['text'] = 'Upload License PDF';
                        $licenseUploadOptions['label']['class'] = 'form-control-label p-0 m-0 me-2';
                        $licenseUploadOptions['type'] = 'file';
                        $licenseUploadOptions['class'] = 'form-control-file';
                        echo $this->Form->control('license_pdf', $licenseUploadOptions);
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

