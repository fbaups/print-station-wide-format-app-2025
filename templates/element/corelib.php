<?php
/**
 * @var App\View\AppView $this
 * @var array $coreLib
 */


/*
 * NOTE:
 * This is placed in the header of the Layout.
 *
 * You can control what is loaded by using $this->set('$coreLib', $options) in the View.
 */
$coreLibDefaults = [
    'base' => true,
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
    'dropzone' => false,
];
$coreLib = $coreLib ?? [];
$coreLib = array_merge($coreLibDefaults, $coreLib);

//startbootstrap.com sb-admin-pro global stylesheet
if ($coreLib['base']) {
    echo $this->Html->css(['styles']);
}

if ($coreLib['fontawesome']) {
    echo $this->Html->css(['/vendors/fontawesome/6.5.1/css/solid']);
    echo $this->Html->css(['/vendors/fontawesome/6.5.1/css/fontawesome']);
    echo $this->Html->script(['/vendors/fontawesome/6.5.1/js/solid']);
    echo $this->Html->script(['/vendors/fontawesome/6.5.1/js/fontawesome']);
}

if ($coreLib['feather-icons']) {
    echo $this->Html->script(['/vendors/feather-icons/4.29.1/feather.min']);
}

if ($coreLib['jQuery']) {
    echo $this->Html->script(['/vendors/jQuery/jquery-3.7.1.min']);
}

if ($coreLib['jQueryUI']) {
    echo $this->Html->script(['/vendors/jquery-ui-1.13.1.custom/jquery-ui']);
    echo $this->Html->css(['/vendors/jquery-ui-1.13.1.custom/jquery-ui']);
}

if ($coreLib['bootstrap']) {
    echo $this->Html->script(['/vendors/bootstrap/5.3.2/js/bootstrap.bundle.min']);
}

if ($coreLib['datatables']) {
    echo $this->Html->css(['/vendors/datatables/dataTables']);
    echo $this->Html->script(['/vendors/datatables/dataTables']);
}

if ($coreLib['dropzone']) {
    echo $this->Html->css(['/vendors/dropzone/6.0.0-beta.1/dropzone']);
    echo $this->Html->script(['/vendors/dropzone/6.0.0-beta.1/dropzone-min']);
}

//startbootstrap.com sb-admin-pro global javascript
if ($coreLib['base']) {
    echo $this->Html->script(['scripts']);
}

//custom styles (always loaded)
echo $this->Html->css(['custom']); //custom override
