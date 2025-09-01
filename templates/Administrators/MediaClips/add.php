<?php
/**
 * @var AppView $this
 * @var MediaClip $mediaClip
 */

use App\Model\Entity\MediaClip;
use App\View\AppView;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Media Clip'));
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
$activation = (new DateTime())->second(0);
$months = intval(Configure::read("Settings.repo_purge"));
$expiration = (clone $activation)->addMonths($months)->second(0);

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4 mb-md-0"><small class="form-text text-muted">{{help}}</small></div></div>',
];
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
    <?php
    $formOpts = ['type' => 'file'];
    echo $this->Form->create($mediaClip, $formOpts);
    ?>
    <div class="card">

        <div class="card-header">
            <?= h($mediaClip->name) ?? "Media Clip Details" ?>
        </div>

        <div class="card-body">
            <div class="mediaClips form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <p>There is an upload limit of <?= ini_get('upload_max_filesize') ?></p>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    $nameControl = $this->Form->control('name', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    $descriptionControl = $this->Form->control('description', $opts);

                    $files = range(0, 0);
                    $fileControl = '';
                    foreach ($files as $file) {
                        $opts = [
                            'class' => 'form-control mb-4',
                            'label' => false,
                            'type' => 'file',
                            'data-type' => 'file',
                        ];
                        $fileControl = $this->Form->control('files.' . $file, $opts);
                    }
                    ?>

                    <div class="row">
                        <div class="col-12 col-md-4"><?= $nameControl ?></div>
                        <div class="col-12 col-md-8"><?= $descriptionControl ?></div>
                        <div class="col-12 col-md-8"><?= $fileControl ?></div>
                    </div>

                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'mediaClips'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
        </div>

    </div>
    <?= $this->Form->end() ?>
</div>
