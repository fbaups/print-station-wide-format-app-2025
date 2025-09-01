<?php
/**
 * @var AppView $this
 * @var Audit $audit
 */

use App\Model\Entity\Audit;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Audit'));
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
    <?= $this->Html->link(__('&larr; Back to Audits'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($audit->id) ?>
        </div>

        <div class="card-body">
            <div class="audits view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Level') ?></th>
                            <td><?= h($audit->level) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('URL') ?></th>
                            <td><?= h($audit->url) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Message Summary') ?></th>
                            <td><?= h($audit->message) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($audit->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User Link') ?></th>
                            <td><?= $audit->user_link === null ? '' : $this->Number->format($audit->user_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($audit->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($audit->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Long Message') ?></th>
                            <td>
                                <pre><?= $audit->message_overflow ?></pre>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



