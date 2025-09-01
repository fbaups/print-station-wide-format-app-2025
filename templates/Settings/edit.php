<?php
/**
 * @var AppView $this
 * @var Setting $setting
 */

use App\Model\Entity\Setting;
use App\View\AppView;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Setting'));
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

<div class="container px-4">
    <div class="card">

        <div class="card-header">
            <?= h($setting->name) ?? "Setting Details" ?>
        </div>

        <div class="card-body">
            <div class="settings form content">
                <?php
                $formOpts = [
                ];

                $labelClass = 'form-control-label';
                $inputClass = 'form-control mb-4';

                $defaultOptions = [
                    'label' => [
                        'class' => $labelClass,
                    ],
                    'options' => null,
                    'class' => $inputClass,
                    'disabled' => true,
                ];
                ?>
                <?= $this->Form->create($setting, $formOpts) ?>
                <h3><?= __("Edit {0}", $setting->name) ?></h3>
                <fieldset>
                    <?php
                    echo $this->Form->control('name', $defaultOptions);
                    echo $this->Form->control('description', $defaultOptions);

                    $selectOpts = json_decode($setting->selections, JSON_FORCE_OBJECT);
                    $dtf = ['datetime_format', 'date_format', 'time_format'];
                    if (in_array($setting->property_key, $dtf)) {
                        $dtObj = new DateTime('now', LCL_TZ);
                        foreach ($selectOpts as $header => $selectOptData) {
                            foreach ($selectOptData as $k => $selectOpt) {
                                $selectOpts[$header][$k] = $dtObj->format($k);
                            }
                        }
                    }

                    if ($setting->html_select_type == 'multiple') {
                        $multiple = true;
                        $size = count($selectOpts);
                    } else {
                        $multiple = false;
                        $size = 1;
                    }

                    if ($setting->is_masked == true) {
                        $type = 'password';
                    } else {
                        $type = null;
                    }

                    $opts = [
                        'select' => ['class' => "form-control"],
                        'options' => $selectOpts,
                        'multiple' => $multiple,
                        'size' => $size,
                        'type' => $type,
                        'required' => false,
                        'disabled' => false,
                        'hiddenField' => false,
                    ];

                    echo $this->Form->hidden('property_value', ['value' => '']);
                    echo $this->Form->control('property_value', array_merge($defaultOptions, $opts));
                    echo $this->Form->hidden('forceRefererRedirect', ['value' => $this->request->referer(false)]);
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
