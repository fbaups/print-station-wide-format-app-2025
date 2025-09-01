<?php
/**
 * @var AppView $this
 * @var XmpieUproduceComposition $xmpieUproduceComposition
 */

use App\Model\Entity\XmpieUproduceComposition;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View XMPie uProduce Composition'));
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
    <?= $this->Html->link(__('&larr; Back to XMPie uProduce Compositions'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($xmpieUproduceComposition->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit XMPie uProduce Composition'), ['action' => 'edit', $xmpieUproduceComposition->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="xmpieUproduceCompositions view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Guid') ?></th>
                            <td><?= h($xmpieUproduceComposition->guid) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($xmpieUproduceComposition->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($xmpieUproduceComposition->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($xmpieUproduceComposition->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Errand Link') ?></th>
                            <td><?= $xmpieUproduceComposition->errand_link === null ? '' : $this->Number->format($xmpieUproduceComposition->errand_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Artifact Link') ?></th>
                            <td><?= $xmpieUproduceComposition->artifact_link === null ? '' : $this->Number->format($xmpieUproduceComposition->artifact_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($xmpieUproduceComposition->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($xmpieUproduceComposition->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($xmpieUproduceComposition->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($xmpieUproduceComposition->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $xmpieUproduceComposition->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



