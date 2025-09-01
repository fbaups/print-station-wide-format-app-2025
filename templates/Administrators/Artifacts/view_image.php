<?php
/**
 * @var AppView $this
 * @var Artifact $artifact
 * @var string[] $pdfMimeTypes
 * @var string[] $imageMimeTypes
 */

use App\Model\Entity\Artifact;
use App\View\AppView;
use arajcany\PrePressTricks\Utilities\PDFGeometry;

?>
<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('EXIF Metadata') ?>
        </div>

        <div class="card-body">
            <div class="artifacts index content">
                <?php if (!empty($artifact->artifact_metadata)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('Width') ?></th>
                                <td><?= h($artifact->artifact_metadata->width) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Height') ?></th>
                                <td><?= h($artifact->artifact_metadata->height) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('EXIF') ?></th>
                                <td>
                                    <pre><?= json_encode($artifact->artifact_metadata->exif, JSON_PRETTY_PRINT) ?></pre>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Artifact Metadata found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

