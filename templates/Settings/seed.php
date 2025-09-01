<?php
/**
 * @var Cake\ORM\Query $seeds ;
 */

use App\Utility\Feedback\DebugCapture;

$seedCompiled = [];
foreach ($seeds as $k => $seed) {

    foreach ($seed as $key => $value) {
        /** @var Cake\I18n\DateTime $value */
        if ($key == 'id') {
            //do nothing
        } elseif ($key == 'created') {
            $seedCompiled[$k][$key] = "\$currentDate";
        } elseif ($key == 'modified') {
            $seedCompiled[$k][$key] = "\$currentDate";
        } else {
            $seedCompiled[$k][$key] = $value;
        }
    }

}

$count = count($seedCompiled);

$output = DebugCapture::captureDump($seedCompiled);
$output = str_replace("'\$currentDate'", "\$currentDate", $output);
foreach (range(0, $count - 1) as $key) {
    $output = str_replace("(int) $key => ", "", $output);
}
?>

<div class="container px-4">
    <div class="card">
        <div class="card-header">
            Seed Data
        </div>
        <div class="card-body">
            <pre><?= $output ?></pre>
        </div>
    </div>
</div>
