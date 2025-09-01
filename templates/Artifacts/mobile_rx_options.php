<?php
/**
 * @var AppView $this
 */

use App\Model\Entity\User;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __("", APP_NAME));
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

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4"><small class="form-text text-muted">{{help}}</small></div></div>',
];
?>

<?php
$this->append('backLink');
?>
<?php
if ($this->AuthUser->hasAccess(['action' => 'index'])) {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Artifacts'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
    </div>
    <?php
} else {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Home'), ['controller' => '/'], ['class' => '', 'escape' => false]) ?>
    </div>
    <?php
}
?>
<?php
$this->end();
?>

<div class="container-fluid px-4 col-12 col-md-10 col-lg-8 col-xl-7 col-xxl-6">
    <div class="card">

        <div class="card-header">
            Mobile Upload - QR Code Options
        </div>

        <div class="card-body">
            <div class="users form content">
                <?php
                $formOptions = [
                ];

                $newTemplate = [
                    'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                ];
                $this->Form->setTemplates($newTemplate);

                echo $this->Form->create(null, $formOptions)
                ?>
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $this->Form->setTemplates($templates);
                    $opts = [
                        'class' => 'form-control mb-0',
                        'data-type' => '',
                        'default' => 5,
                        'options' => [
                            1 => '1 Minute',
                            5 => '5 Minutes',
                            10 => '10 Minutes',
                            15 => '15 Minutes',
                            20 => '20 Minutes',
                        ],
                        'type' => 'select',
                        'templateVars' => ['help' => "How long the QR will remain valid for Users to upload."],
                    ];
                    echo $this->Form->control('max_time', $opts);

                    $opts = [
                        'class' => 'form-control mb-0',
                        'data-type' => '',
                        'default' => 4,
                        'options' => [
                            1 => '1 File',
                            2 => '2 Files',
                            3 => '3 Files',
                            4 => '4 Files',
                            5 => '5 Files',
                            10 => '10 Files',
                            15 => '15 Files',
                            20 => '20 Files',
                        ],
                        'type' => 'select',
                        'templateVars' => ['help' => "Each User that scans the QR will be able to upload this many files."],
                    ];
                    echo $this->Form->control('max_uploads', $opts);
                    $this->Form->resetTemplates();

                    ?>
                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => '/'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Show QR Code'), $options);
                ?>
            </div>
            <?php
            echo $this->Form->end();
            $this->Form->resetTemplates();
            ?>
        </div>

    </div>
</div>

<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {

    });
</script>
<?php
$this->end();
?>

