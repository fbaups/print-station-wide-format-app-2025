<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\HotFolder $hotFolder
 */

use Cake\Routing\Router;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Hot Folder'));
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
    <?= $this->Html->link(__('&larr; Back to Hot Folders'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($hotFolder->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Hot Folder'), ['action' => 'edit', $hotFolder->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="hotFolders view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($hotFolder->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($hotFolder->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($hotFolder->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Path') ?></th>
                            <td><?= h($hotFolder->path) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Workflow') ?></th>
                            <td><?= h($hotFolder->workflow) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Polling Interval') ?></th>
                            <td><?= $hotFolder->polling_interval === null ? '' : $this->Number->format($hotFolder->polling_interval) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Stable Interval') ?></th>
                            <td><?= $hotFolder->stable_interval === null ? '' : $this->Number->format($hotFolder->stable_interval) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($hotFolder->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($hotFolder->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Next Polling Time') ?></th>
                            <td><?= h($hotFolder->next_polling_time) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Enabled') ?></th>
                            <td><?= $hotFolder->is_enabled ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Submit URL') ?></th>
                            <td>
                                <?= Router::url("/", true) ?>hot-folders/submit/<?= $hotFolder->submit_url ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Submit URL Enabled') ?></th>
                            <td><?= $hotFolder->submit_url_enabled ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



