<?php
/**
 * @var AppView $this
 * @var IntegrationCredential $integrationCredential
 * @var array $integrationTypes
 */

use App\Model\Entity\IntegrationCredential;
use App\View\AppView;
use Cake\Utility\Inflector;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Add Integration Credential'));
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
    <?= $this->Html->link(__('&larr; Back to Integration Credentials'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= "Select Type to Add" ?>
        </div>

        <div class="card-body">
            <div class="integrationCredentials links content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    foreach ($integrationTypes as $key => $name) {
                        echo "<p>";
                        $opts = [];
                        $slug = Inflector::dasherize($key);
                        $slug = str_replace("auth2", "auth-2", $slug);
                        $url = ['action' => 'add', $slug];
                        echo $this->Html->link($name, $url, $opts);

                        echo "</p>";
                    }
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
                echo $this->Html->link(__('Back'), ['controller' => 'integrationCredentials'], $options);
                ?>
            </div>
        </div>

    </div>
</div>
