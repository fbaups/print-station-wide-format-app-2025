<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Seed $seed
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Seed'));
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
    <?= $this->Html->link(__('&larr; Back to Seeds'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($seed->id) ?>
        </div>

        <div class="card-body">
            <div class="seeds view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('URL') ?></th>
                            <td><?= h($seed->url) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($seed->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Bids') ?></th>
                            <td><?= $seed->bids === null ? '' : $this->Number->format($seed->bids) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Bid Limit') ?></th>
                            <td><?= $seed->bid_limit === null ? '' : $this->Number->format($seed->bid_limit) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User Link') ?></th>
                            <td><?= $seed->user_link === null ? '' : $this->Number->format($seed->user_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($seed->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($seed->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($seed->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($seed->expiration) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



