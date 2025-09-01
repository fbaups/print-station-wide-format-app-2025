<?php
/**
 * @var \App\View\AppView $this
 *
 * @var mixed $toDebug
 */

use App\Utility\Feedback\DebugCapture;

?>

<div class="row">
    <div class="col-md-12 col-xl-8 m-xl-auto">
        <div class="tester">
            <div class="card">
                <div class="card-body">
                    <pre>

                    <?php
                    //echo json_encode($this->AuthUser->user(), JSON_PRETTY_PRINT);
                    //echo json_encode($this->AuthUser->getConfig(), JSON_PRETTY_PRINT);
                    echo $this->AuthUser->hasRoles('superadmin');
                    //echo $this->AuthUser->roles();
                    //echo $this->AuthUser->getConfig('roleColumn');

                    Cake\Core\Configure::write('debug', true);
                    $toDebug = DebugCapture::captureDump($toDebug);
                    pr($toDebug);
                    ?>
                    </pre>
                </div>
            </div>
        </div>
    </div>
</div>
