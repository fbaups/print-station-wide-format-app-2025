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

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Artifact'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => false,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);

//dump($artifact->sample_urls);
//dump($artifact->doAllSampleImagesExist());

//dump($artifact->light_table_urls);
//dump($artifact->doAllLightTableImagesExist());

?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Artifacts'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($artifact->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Artifact'), ['action' => 'edit', $artifact->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="artifacts view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($artifact->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($artifact->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('MIME Type') ?></th>
                            <td><?= h($artifact->mime_type) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('URL') ?></th>
                            <td><?= h($artifact->full_url) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('UNC') ?></th>
                            <td><?= h($artifact->full_unc) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Hash Sum') ?></th>
                            <td><?= h($artifact->hash_sum) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($artifact->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Size') ?></th>
                            <td><?= $artifact->size === null ? '' : $this->Number->format($artifact->size) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($artifact->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($artifact->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($artifact->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($artifact->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $artifact->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <?php
                        $allowed = [
                            'image/jpeg',
                            'image/png',
                            'application/pdf',
                            'application/pdf',
                            'application/x-pdf',
                            'application/acrobat',
                            'applications/vnd.pdf',
                            'text/pdf',
                            'text/x-pdf',
                        ];
                        if (in_array($artifact->mime_type, $allowed)) {
                            ?>
                            <tr>
                                <th scope="row"><?= __('Preview') ?></th>
                                <td>
                                    <?php
                                    $imgOpts = [
                                        'class' => 'artifact-preview-image'
                                    ];
                                    if (is_file($artifact->sample_unc_thumbnail)) {
                                        $url = $artifact->sample_url_thumbnail;
                                        ?>
                                        <div class="preview m-2">
                                            <div class="artifact-preview">
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
                                    ?>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
if (!empty($artifact->artifact_metadata->exif)) {
    if (in_array($artifact->mime_type, $imageMimeTypes)) {
        include_once("view_image.php");
    }
}
?>

<?php
if (!empty($artifact->artifact_metadata->exif)) {
    if (in_array($artifact->mime_type, $pdfMimeTypes)) {
        include_once("view_pdf.php");
    }
}
?>


