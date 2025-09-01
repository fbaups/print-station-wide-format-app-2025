<?php
/**
 * @var AppView $this
 * @var User $user
 * @var CollectionInterface|string[] $peerRoles
 */

use App\Model\Entity\User;
use App\View\AppView;
use Cake\Collection\CollectionInterface;

$this->set('headerShow', true);
$this->set('headerIcon', __("person-fill"));
$this->set('headerTitle', __('Invite a User into {0}', APP_NAME));
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
<?php
if ($this->AuthUser->hasAccess(['action' => 'index'])) {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Users'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
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
    <?php
    $formOpts = [];
    echo $this->Form->create($user, $formOpts);
    ?>
    <div class="card">

        <div class="card-header">
            <?php
            echo '<span class="invite-holder">User Details</span>';
            echo ' ';
            echo '<span class="first-name-holder"></span>';
            echo ' ';
            echo '<span class="last-name-holder"></span>';
            ?>
        </div>

        <div class="card-body">
            <div class="users form content">
                <?php
                $newTemplate = [
                    'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                ];
                $this->Form->setTemplates($newTemplate);
                ?>
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('email', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('first_name', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('last_name', $opts);


                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('mobile', $opts);

                    $hidePeerRoles = '';
                    if ((count($peerRoles, COUNT_RECURSIVE) - count($peerRoles)) === 1) {
                        $hidePeerRoles = 'd-none';
                    }
                    if (count($peerRoles) === 1) {
                        $peerRoles = $peerRoles[array_key_first($peerRoles)];
                    }
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => '',
                        'options' => $peerRoles,
                        'type' => 'select',
                        'templateVars' => ['wrapperClass' => $hidePeerRoles]
                    ];
                    echo $this->Form->control('roles._ids', $opts);

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
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
        </div>

    </div>
    <?php
    echo $this->Form->end();
    $this->Form->resetTemplates();
    ?>
</div>

<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        var firstnameField = $("input[name*='first_name']");
        var lastnameField = $("input[name*='last_name']");

        firstnameField.keyup(function () {
            $('.invite-holder').text('Invite');
            $('.first-name-holder').text(this.value);
        });

        lastnameField.keyup(function () {
            $('.invite-holder').text('Invite');
            $('.last-name-holder').text(this.value);
        });
    });
</script>
<?php
$this->end();
?>

