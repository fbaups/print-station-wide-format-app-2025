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
    <div class="col-md-12 m-4">
        <div class="tester">
            <div class="card">
                <div class="card-body">
                    <?php
                    foreach ($toDebug as $k => $item) {
                        if (is_array($item) || str_starts_with($item, 'http')) {
                            echo $this->Html->link($item, $item, ['target' => 'blank']);
                        } else {
                            echo $item;
                        }
                        echo "<br>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
