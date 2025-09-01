<?php
/**
 * @var App\View\AppView $this
 * @var array $coreLibUserInterface
 */


/*
 * NOTE:
 * This is placed in the header of the Layout.
 *
 * You can control what is loaded by using $this->set('$coreLibUserInterface', $options) in the View.
 */
$coreLibUserInterfaceDefaults = [
    'baseHeader' => true,
    'baseFooter' => false,
];
$coreLibUserInterface = $coreLibUserInterface ?? [];
$coreLibUserInterface = array_merge($coreLibUserInterfaceDefaults, $coreLibUserInterface);

if ($coreLibUserInterface['baseHeader']) {
    echo $this->Html->css(['/interface/assets/css/vendor/normalize']);
    echo $this->Html->css(['/interface/assets/css/vendor/bootstrap']);
    echo $this->Html->css(['/interface/assets/css/vendor/select2.min']);
    echo $this->Html->css(['/interface/assets/css/vendor/bootstrap-datepicker.min']);
    echo $this->Html->css(['/interface/dist/css/main']);
}
