<?php
/**
 * @var AppView $this
 * @var OutputProcessor $outputProcessor
 */

use App\Model\Entity\OutputProcessor;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Output Processor'));
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
    <?= $this->Html->link(__('&larr; Back to Output Processors'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($outputProcessor->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Output Processor'), ['action' => 'edit', $outputProcessor->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="outputProcessors view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Type') ?></th>
                            <td><?= h($outputProcessor->type) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($outputProcessor->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($outputProcessor->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($outputProcessor->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($outputProcessor->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($outputProcessor->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Enabled') ?></th>
                            <td><?= $outputProcessor->is_enabled ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parameters') ?></th>
                            <td>
                                <pre><?php
                                    print_r($outputProcessor->parameters);
                                    ?></pre>
                            </td>
                        </tr>
                    </table>

                </div>
            </div>
        </div>

    </div>
</div>



