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

use App\View\Helper\ExtendedAuthUserHelper;
use TinyAuth\View\Helper\AuthUserHelper;

$coreLibDefaults = [
    'bootstrap' => true,
    'bootstrap-icons' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
    'dropzone' => false,
    'summernote' => false,
];
$coreLib = $coreLib ?? [];
$coreLib = array_merge($coreLibDefaults, $coreLib);

//startbootstrap.com sb-admin-pro global stylesheet
echo $this->Html->css(['styles']);

if ($coreLib['fontawesome']) {
    echo $this->Html->css(['/vendors/fontawesome/6.5.1/css/solid']);
    echo $this->Html->css(['/vendors/fontawesome/6.5.1/css/fontawesome']);
    echo $this->Html->script(['/vendors/fontawesome/6.5.1/js/solid']);
    echo $this->Html->script(['/vendors/fontawesome/6.5.1/js/fontawesome']);
}

if ($coreLib['feather-icons']) {
    echo $this->Html->script(['/vendors/feather-icons/4.29.1/feather.min']);
}

if ($coreLib['bootstrap-icons']) {
    echo $this->Html->css(['/vendors/bootstrap-icons/1.11.3/font/bootstrap-icons.min']);
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
    echo $this->Html->css(['/vendors/datatables/datatables']);
    echo $this->Html->script(['/vendors/datatables/datatables']);
}

if ($coreLib['dropzone']) {
    echo $this->Html->css(['/vendors/dropzone/6.0.0-beta.1/dropzone']);
    echo $this->Html->script(['/vendors/dropzone/6.0.0-beta.1/dropzone-min']);
}

if ($coreLib['summernote']) {
    echo $this->Html->css(['/vendors/summernote/0.9.0/summernote-bs5.min']);
    echo $this->Html->script(['/vendors/summernote/0.9.0/summernote-bs5.min']);
}

//startbootstrap.com sb-admin-pro global javascript
echo $this->Html->script(['scripts']);

//custom styles
echo $this->Html->css(['custom']); //custom override

//custom style based on the User's Role
$identity = $this->getRequest()->getAttribute('identity');
if ($identity && isset($usersSessionData['roles'])) {
    // Extract role names from role objects/arrays
    $roleNames = [];
    foreach ($usersSessionData['roles'] as $role) {
        if (is_array($role)) {
            $roleNames[] = $role['name'] ?? $role['alias'] ?? '';
        } elseif (is_object($role)) {
            $roleNames[] = $role->name ?? $role->alias ?? '';
        } else {
            $roleNames[] = $role; // Already a string
        }
    }

    if (array_intersect($roleNames, ['superadmin', 'admin'])) {
        echo $this->Html->css(['custom-administrator']);
    } elseif (array_intersect($roleNames, ['manager', 'supervisor', 'operator'])) {
        echo $this->Html->css(['custom-producer']);
    } elseif (array_intersect($roleNames, ['superuser', 'user'])) {
        echo $this->Html->css(['custom-consumer']);
    }
}
