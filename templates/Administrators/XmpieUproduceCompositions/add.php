<?php
/**
 * @var AppView $this
 * @var XmpieUproduceComposition $xmpieUproduceComposition
 * @var IntegrationCredential[]|CollectionInterface $integrationCredentials
 */

use App\Model\Entity\IntegrationCredential;
use App\Model\Entity\XmpieUproduceComposition;
use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add XMPie uProduce Composition'));
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
    <?php
    $formOpts = ['type' => 'file'];
    echo $this->Form->create($xmpieUproduceComposition, $formOpts)
    ?>
    <div class="card">

        <div class="card-header">
            <?= h($xmpieUproduceComposition->name) ?? "XMPie uProduce Composition Details" ?>
        </div>

        <div class="card-body">
            <div class="xmpieUproduceCompositions form content">
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

                    $selectOpts = [];
                    foreach ($integrationCredentials as $integrationCredential) {
                        $selectOpts[$integrationCredential->id] = "{$integrationCredential->name}: {$integrationCredential->description}";
                    }
                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => 'XMPie uProduce Server'],
                        'data-type' => 'string',
                        'type' => 'select',
                        'options' => $selectOpts
                    ];
                    echo $this->Form->control('integration_credential_id', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'file',
                        'type' => 'file',
                    ];
                    echo $this->Form->control('trigger_file', $opts);


                    $activation = (new DateTime())->second(0);
                    $months = intval(Configure::read("Settings.repo_purge"));
                    $expiration = (clone $activation)->addMonths($months)->second(0);

                    echo '<div class="row activation expiration auto-delete">';

                    echo '<div class="col-md-4">';
                    $opts = [
                        'label' => ['text' => 'Activation <span class="activation-date-clear link-blue pointer">[Clear]</span>', 'escape' => false],
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                        'default' => $activation,
                    ];
                    echo $this->Form->control('activation', $opts);
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    $opts = [
                        'label' => ['text' => 'Expiration <span class="expiration-date-clear link-blue pointer">[Clear]</span>', 'escape' => false],
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                        'default' => $expiration,
                    ];
                    echo $this->Form->control('expiration', $opts);
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<div class="d-none d-md-block" style="height: 33px"></div>';
                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                        'default' => true,
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('auto_delete', $opts);
                    $this->Form->switchBackTemplates();
                    echo '</div>';

                    echo '</div>';
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
                echo $this->Html->link(__('Back'), ['controller' => 'xmpieUproduceCompositions'], $options);

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
