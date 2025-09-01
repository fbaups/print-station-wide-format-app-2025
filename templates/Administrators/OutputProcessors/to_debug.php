<?php
/**
 * @var AppView $this
 *
 * @var mixed $toDebug
 */

use App\Utility\Feedback\DebugCapture;
use App\View\AppView;

?>

<div class="row">
    <div class="col-md-12 col-xl-8 m-xl-auto">
        <div class="tester">
            <div class="card">
                <div class="card-body">
                    <?php
                    $previousValue = Cake\Core\Configure::read('debug', false);
                    Cake\Core\Configure::write('debug', true);
                    $toDebug = DebugCapture::captureDump($toDebug);
                    pr($toDebug);
                    Cake\Core\Configure::write('debug', $previousValue);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
