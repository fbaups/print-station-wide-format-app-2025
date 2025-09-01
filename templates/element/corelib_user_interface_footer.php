<?php
/**
 * @var App\View\AppView $this
 * @var array $coreLibUserInterface
 */


/*
 * NOTE:
 * This is placed in the footer of the Layout.
 *
 * You can control what is loaded by using $this->set('$coreLibUserInterface', $options) in the View.
 */
$coreLibUserInterfaceDefaults = [
    'baseHeader' => false,
    'baseFooter' => true,
];
$coreLibUserInterface = $coreLibUserInterface ?? [];
$coreLibUserInterface = array_merge($coreLibUserInterfaceDefaults, $coreLibUserInterface);

if ($coreLibUserInterface['baseFooter']) {
    echo $this->Html->script(['/interface/assets/js/script']);
    echo $this->Html->script(['/interface/assets/js/vendor/bootstrap.bundle']);
}
