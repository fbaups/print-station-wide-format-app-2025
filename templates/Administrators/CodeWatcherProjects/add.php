<?php
/**
 * @var AppView $this
 * @var CodeWatcherProject $codeWatcherProject
 */

use App\Model\Entity\CodeWatcherProject;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Code Watcher Project'));
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
    <?= $this->Html->link(__('&larr; Back to Code Watcher Projects'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <?= $this->Form->create($codeWatcherProject) ?>
    <div class="card">

        <div class="card-header">
            <?= h($codeWatcherProject->name) ?? "Code Watcher Project Details" ?>
        </div>

        <div class="card-body">
            <div class="codeWatcherProjects form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('name', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('description', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                    ];
                    //echo $this->Form->control('activation', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                    ];
                    //echo $this->Form->control('expiration', $opts);

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    //echo $this->Form->control('auto_delete', $opts);
                    $this->Form->switchBackTemplates();

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('enable_tracking', $opts);
                    $this->Form->switchBackTemplates();

                    ?>
                    <div class="border border-2 p-3">
                        <p class="lead">Project Folders</p>
                        <?php
                        $k = 0;

                        if (isset($codeWatcherProject->code_watcher_folders)) {
                            foreach ($codeWatcherProject->code_watcher_folders as $k => $folder) {
                                $opts = [
                                    'data-type' => 'integer',
                                    'value' => $folder->id,
                                    'type' => 'hidden',
                                ];
                                echo $this->Form->control("code_watcher_folders.{$k}.id", $opts);

                                $opts = [
                                    'label' => ['text' => "Folder ID {$folder->id}"],
                                    'class' => 'form-control mb-4',
                                    'data-type' => 'string',
                                    'value' => $folder->base_path,
                                ];
                                echo $this->Form->control("code_watcher_folders.{$k}.base_path", $opts);
                            }
                        }

                        $range = range($k + 1, $k + 5);
                        foreach ($range as $k) {

                            $opts = [
                                'label' => ['text' => "Add Folder"],
                                'class' => 'form-control mb-4',
                                'data-type' => 'string',
                            ];
                            echo $this->Form->control("code_watcher_folders.{$k}.base_path", $opts);
                        }

                        ?>
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
                echo $this->Html->link(__('Back'), ['controller' => 'codeWatcherProjects'], $options);

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
