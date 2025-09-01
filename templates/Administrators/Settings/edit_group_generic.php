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
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <small class="form-text text-muted">{{help}}</small></div>',
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

<?php
//restore the original templates
$this->Form->resetTemplates();
?>

