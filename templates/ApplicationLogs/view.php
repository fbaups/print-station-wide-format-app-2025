<?php
/**
 * @var AppView $this
 * @var ApplicationLog $applicationLog
 */

use App\Model\Entity\ApplicationLog;
use App\View\AppView;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Application Log'));
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
    <?= $this->Html->link(__('&larr; Back to Application Logs'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($applicationLog->id) ?>
        </div>

        <div class="card-body">
            <div class="applicationLogs view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Level') ?></th>
                            <td><?= h($applicationLog->level) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('URL') ?></th>
                            <td><?= h($applicationLog->url) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Message Summary') ?></th>
                            <td><?= h($applicationLog->message) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($applicationLog->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User Link') ?></th>
                            <td><?= $applicationLog->user_link === null ? '' : $this->Number->format($applicationLog->user_link) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($applicationLog->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($applicationLog->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Message') ?></th>
                            <td>
                                <pre><?= $applicationLog->message_overflow ?></pre>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>



