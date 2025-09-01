<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Entity|Order $orderData
 * @var Artifact[] $validArtifacts
 * @var Entity $recordData
 */

use App\Model\Entity\Artifact;
use App\Model\Entity\Order;
use App\View\AppView;
use Cake\ORM\Entity;
use Cake\Utility\Inflector;

$fields = $recordData->getAccessible();
?>

<div class="record-preview">
    <div class="artifact-previews">
        <?php
        foreach ($validArtifacts as $artifact) {
            $imgOpts = [
                'class' => 'artifact-preview-image ms-auto me-auto'
            ];
            if (is_file($artifact->sample_unc_preview)) {
                $url = $artifact->sample_url_preview;
                ?>
                <div class="preview m-2">
                    <div class="artifact-preview ms-auto me-auto">
                        <?= $this->Html->image($url, $imgOpts) ?>
                    </div>
                </div>
                <?php
            } else {
                $artifact->createSampleSizesErrand();
                ?>
                <p class="mb-0">
                    Image preview is currently being generated,
                    please try again is a few seconds.
                </p>
                <?php
            }
        }
        ?>
    </div>

    <dl class="row mb-0">

    </dl>
</div>
