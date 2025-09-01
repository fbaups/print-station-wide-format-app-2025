<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var User[]|CollectionInterface $users
 * @var array $datatablesQuery
 * @var bool $isAjax
 * @var int $recordsTotal
 * @var int $recordsFiltered
 * @var string $message
 */

use App\Model\Entity\User;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __('people-fill'));
$this->set('headerTitle', __('Users'));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => true,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);


//$headers must match the Controller
$headers = [
    'Users.id',
    'Users.email',
    'Users.username',
    'Users.first_name',
    'Users.last_name',
    'Users.country',
    'Users.mobile',
    'Users.user_statuses_id',
    'Roles.id',
    'actions',
];

$rows = [];
$counter = 0;
$impersonateUrl = ['prefix' => 'Administrators', 'controller' => 'Users', 'action' => 'impersonate'];
?>
<?php foreach ($users as $k => $user): ?>
    <?php $rows[$counter][] = $this->Number->format($user->id) ?>
    <?php $rows[$counter][] = h($user->email) ?>
    <?php $rows[$counter][] = h($user->username) ?>
    <?php $rows[$counter][] = h($user->first_name) ?>
    <?php $rows[$counter][] = h($user->last_name) ?>
    <?php $rows[$counter][] = h($user->country) ?>
    <?php $rows[$counter][] = h($user->mobile) ?>
    <?php $rows[$counter][] = $user->has('user_status') ? "{$user->user_status->id}: {$user->user_status->name}" : '' ?>
    <?php $rows[$counter][] = ($user->has('roles') && isset($user->roles[0])) ? "{$user->roles[0]->id}: {$user->roles[0]->name}" : '' ?>
    <?php

    $appendName = ($user->name) ? ": $user->name" : "";
    $previewLinkOptions = [
        'class' => '',
        'data-bs-toggle' => "modal",
        'data-bs-target' => "#previewRecord",
        'data-record-title' => "Record #{$user->id}{$appendName}",
        'data-record-id' => $user->id,
    ];
    $rows[$counter][] =
        $this->Html->link(__('Preview'), "#", $previewLinkOptions) . " | " .
        $this->Html->link(__('View'), ['action' => 'view', $user->id])
    ?>
    <?php $counter++ ?>
<?php endforeach; ?>
<?php
if ($isAjax) {
    $result = [
        "message" => $message,
        "draw" => intval($datatablesQuery['draw']),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $rows,
    ];
    echo json_encode($result, JSON_PRETTY_PRINT);
    return;
}
?>
<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= __('User Listing') ?>
            <?= $this->Html->link('<i class="fas fa-plus"></i>' . __('&nbsp;Invite User'), ['action' => 'invite'],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="users index content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm dataset" style="width:100%">
                        <thead>
                        <tr class="filter-headers">
                            <th data-db-type="<?= $typeMap['id'] ?>"><?= strtoupper('id') ?></th>
                            <th data-db-type="<?= $typeMap['email'] ?>"><?= Inflector::humanize('email') ?></th>
                            <th data-db-type="<?= $typeMap['username'] ?>"><?= Inflector::humanize('username') ?></th>
                            <th data-db-type="<?= $typeMap['first_name'] ?>"><?= Inflector::humanize('first_name') ?></th>
                            <th data-db-type="<?= $typeMap['last_name'] ?>"><?= Inflector::humanize('last_name') ?></th>
                            <th data-db-type="<?= $typeMap['country'] ?>"><?= Inflector::humanize('country') ?></th>
                            <th data-db-type="<?= $typeMap['mobile'] ?>"><?= Inflector::humanize('mobile') ?></th>
                            <th data-db-type="<?= $typeMap['user_statuses_id'] ?>"><?= Inflector::humanize('User Status') ?></th>
                            <th data-db-type="<?= $typeMap['user_statuses_id'] ?>"><?= Inflector::humanize('Role') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        </thead>
                        <thead>
                        <tr class="filters">
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        <!--populated by DataTables-->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="previewRecord" tabindex="-1" role="dialog" aria-labelledby="previewRecord"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo __('Record Preview') ?></h5>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="loader-content">
                    <div class="spinner-border d-inline-block align-middle" role="status">
                        <span class="sr-only"><?php echo __('Loading...') ?></span>
                    </div>
                    <span class="px-3 align-middle"><?php echo __('Loading Record Data...') ?></span>
                </div>
                <div class="record-content">

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');

//DataTables initialisation for the index view...
echo $this->Html->script('datatables_manager');

?>
<script>
    $(document).ready(function () {
        //DataTablesManager.autoRefresh = 10000; //every 10 seconds
        DataTablesManager.run();
    });
</script>
<?php
$this->end();
?>
