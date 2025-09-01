<?php
/**
 * @var AppView $this
 * @var TheatrePin $theatrePin
 */

use App\Model\Entity\TheatrePin;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Theatre Pin'));
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
    <?= $this->Html->link(__('&larr; Back to Theatre Pins'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($theatrePin->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Theatre Pin'), ['action' => 'edit', $theatrePin->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="theatrePins view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($theatrePin->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($theatrePin->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Pin Code') ?></th>
                            <td><?= h($theatrePin->pin_code) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($theatrePin->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User Link') ?></th>
                            <td><?= $theatrePin->user_link === null ? '' : $this->Number->format($theatrePin->user_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($theatrePin->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($theatrePin->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



