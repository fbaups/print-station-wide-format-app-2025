<?php
/**
 * @var AppView $this
 * @var MediaClip $mediaClip
 * @var string[] $pdfMimeTypes
 * @var string[] $imageMimeTypes
 * @var string[] $videoMimeTypes
 * @var string[] $audioMimeTypes
 */

use App\Model\Entity\MediaClip;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Media Clip'));
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

?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Media Clips'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($mediaClip->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Media Clip'), ['action' => 'edit', $mediaClip->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="mediaClips view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($mediaClip->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($mediaClip->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Fitting') ?></th>
                            <td><?= h($mediaClip->fitting) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($mediaClip->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rank') ?></th>
                            <td><?= $mediaClip->rank === null ? '' : $this->Number->format($mediaClip->rank) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Artifact Link') ?></th>
                            <td><?= $mediaClip->artifact_link === null ? '' : $this->Number->format($mediaClip->artifact_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Trim Start') ?></th>
                            <td><?= $mediaClip->trim_start === null ? '' : $this->Number->format($mediaClip->trim_start) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Trim End') ?></th>
                            <td><?= $mediaClip->trim_end === null ? '' : $this->Number->format($mediaClip->trim_end) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Duration') ?></th>
                            <td><?= $mediaClip->duration === null ? '' : $this->Number->format($mediaClip->duration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($mediaClip->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($mediaClip->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($mediaClip->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($mediaClip->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $mediaClip->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Muted') ?></th>
                            <td><?= $mediaClip->muted ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Loop') ?></th>
                            <td><?= $mediaClip->loop ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Autoplay') ?></th>
                            <td><?= $mediaClip->autoplay ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <?php
                        $allowed = array_merge($pdfMimeTypes, $imageMimeTypes, $videoMimeTypes);
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
                                            A preview image is currently being generated,
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



