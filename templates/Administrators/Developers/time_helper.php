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
                    $ts = '2024-05-16 02:00:00.000';
                    $time = new \Cake\I18n\DateTime($ts);

                    dump($time);
                    dd($this->Time->format($time));

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
