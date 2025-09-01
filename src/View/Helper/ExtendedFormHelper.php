<?php

namespace App\View\Helper;

use App\Model\Entity\Setting;
use Cake\I18n\DateTime;
use Cake\View\Helper\FormHelper as CakeFormHelper;

/**
 * Form helper
 */
class ExtendedFormHelper extends CakeFormHelper
{
    private $switchBackPoint;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $templates = [
            'error' => '<div class="form-issue alert text-danger p-0 mb-3">{{content}}</div>',
        ];
        $this->setTemplates($templates);

        $this->setSwitchBackPoint();
    }

    /**
     * @inheritDoc
     */
    public function control(string $fieldName, array $options = []): string
    {
        $defaultOptions = [
            'timezone' => LCL_TZ,
            'locale' => LCL_LOCALE,
        ];
        $options = array_merge($defaultOptions, $options);

        return parent::control($fieldName, $options);
    }


    /**
     * @param $currentOptions
     * @param Setting $setting
     * @return array
     */
    public function settingsFormatOptions($currentOptions, Setting $setting)
    {
        $opts = [
            'id' => 'setting_' . $setting->property_key,
            'name' => '' . $setting->property_key,
            'select' => ['class' => "form-control"],
            'options' => null,
            'multiple' => false,
            'size' => 1,
            'type' => 'text',
            'required' => false,
            'disabled' => false,
            'hiddenField' => false,
            'label' => ['text' => $setting->name],
            'templateVars' => ['help' => $setting->description],
            'value' => $setting->property_value,
            'data-original-value' => $setting->property_value,
        ];

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

        if ($setting->html_select_type === 'number') {
            $opts['type'] = 'number';
            if (isset($selectOpt['min'])) {
                $opts['min'] = $selectOpt['min'];
            }
            if (isset($selectOpt['max'])) {
                $opts['max'] = $selectOpt['max'];
            }
        } elseif ($setting->html_select_type == 'multiple') {
            $opts['multiple'] = true;
            $opts['size'] = count($selectOpts);
            $opts['type'] = null;
            $opts['options'] = $selectOpts;
        } elseif ($setting->html_select_type == 'select') {
            $opts['size'] = 1;
            $opts['type'] = 'select';
            $opts['options'] = $selectOpts;
        }

        if ($setting->is_masked == true) {
            $opts['type'] = 'password';
            if (!empty($opts['value'])) {
                $opts['value'] = sha1($opts['value']);
                $opts['data-original-value'] = $opts['value'];
            }
        }

        return array_merge($currentOptions, $opts);
    }

    private function setSwitchBackPoint()
    {
        $this->switchBackPoint = $this->getTemplates();
    }

    public function switchToPressReadyCsvTemplate()
    {
        $this->setSwitchBackPoint();

        $this->setTemplates([
            'inputContainer' => '<div class="input {{type}}{{required}}"><div class="row">{{content}}</div></div>',
            'label' => '<div class="col-sm-3 col-lg-2"><label{{attrs}}>{{text}}</label></div>',
            'input' => '<div class="col-sm-7 col-lg-8"><input type="{{type}}" name="{{name}}"{{attrs}}></div><div class="col-sm-2 col-lg-2 mt-1">{{dataType}}</div>',
        ]);
    }

    public function switchToSummernoteEditorTemplate()
    {
        $this->setSwitchBackPoint();


        $this->setTemplates([
            'inputContainer' => '<div class="input {{type}}{{required}} mb-4">{{content}}</div>',
            'label' => '<label{{attrs}}>{{text}}</label>',
            'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}>',
        ]);
    }

    public function switchToSummernoteDateRageTemplate()
    {
        $this->setSwitchBackPoint();

        $this->setTemplates([
            'inputContainer' => '<div class="input {{type}}{{required}} d-inline-block">{{content}}</div>',
            'label' => '<label{{attrs}}>{{text}}</label>',
            'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}>',
        ]);
    }

    public function switchToCheckboxTemplate(string $addClasses = '')
    {
        $this->setSwitchBackPoint();

        $this->setTemplates([
            'nestingLabel' => '<div class="form-check ' . $addClasses . '">{{hidden}}{{input}}<label{{attrs}}>{{text}}</label></div>',
            'formGroup' => '{{input}}{{label}}',
        ]);
    }

    public function switchBackTemplates(): void
    {
        $this->setTemplates($this->switchBackPoint);
    }


}
