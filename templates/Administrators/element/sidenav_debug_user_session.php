<?php
/**
 * @var App\View\AppView $this
 */

use Cake\Core\Configure;

?>

<?php
$mode = Configure::read('mode');
if (!in_array(strtolower($mode), ['dev', 'development'])) {
    return;
}

$debug = Configure::read('debug');
if (!$debug) {
    return;
}
?>

<div class="debug-box">
    <div class="p-2">
        <h6>
            User Session Debug
        </h6>
        <p class="small m-0">
            Session Logout <span class="auto-logout-countdown"></span>
        </p>
        <p class="small m-0">
            Inactivity Logout <span class="inactivity-counter"></span>
        </p>
        <p class="small m-0">
            SessID <?= $this->request->getSession()->id() ?>
        </p>
    </div>
</div>
