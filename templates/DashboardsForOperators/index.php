<?php
/**
 * @var \App\View\AppView $this
 *
 */

$this->assign('title', $this->get('title'));

//control what Libraries are loaded
$coreLib = [
    'base' => false,
    'bootstrap' => false,
    'datatables' => true,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);
