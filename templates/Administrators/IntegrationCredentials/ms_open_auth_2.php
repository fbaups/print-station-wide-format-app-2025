<?php
/**
 * @var AppView $this
 * @var IntegrationCredential $integrationCredential
 */

use App\Model\Entity\IntegrationCredential;
use App\Utility\IntegrationCredentials\BaseIntegrationCredentials;
use App\View\AppView;
use Cake\Routing\Router;

$BIC = new BaseIntegrationCredentials();
$icTypes = $BIC->getIntegrationTypes();


if (isset($integrationCredential->parameters) && !empty($integrationCredential->parameters)) {
    $addEditText = 'Edit';
} else {
    $addEditText = 'Add';
}

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('{0} {1} Integration Credential', $addEditText, $icTypes[$integrationCredential->type]));
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
    <?= $this->Form->create($integrationCredential) ?>
    <div class="card">

        <div class="card-header">
            <?= h($integrationCredential->name) ?? "Integration Credential Details" ?>
        </div>

        <div class="card-body">
            <div class="integrationCredentials form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'type' => 'hidden',
                        'data-type' => 'string',
                    ];
                    echo $this->Form->control('type', $opts);

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

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4'],
                        'data-type' => 'boolean',
                    ];
                    $this->Form->switchToCheckboxTemplate();
                    echo $this->Form->control('is_enabled', $opts);
                    $this->Form->switchBackTemplates();

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'label' => ['text' => ' Tenant ID'],
                    ];
                    if ($integrationCredential->parameters) {
                        $integrationCredential->parameters['provider_options']['tenantId'] = \arajcany\ToolBox\Utility\Security\Security::decrypt64($integrationCredential->parameters['provider_options']['tenantId']);
                    }
                    echo $this->Form->control('parameters.provider_options.tenantId', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'text',
                        'label' => ['text' => ' Client ID'],
                    ];
                    if ($integrationCredential->parameters) {
                        $integrationCredential->parameters['provider_options']['clientId'] = \arajcany\ToolBox\Utility\Security\Security::decrypt64($integrationCredential->parameters['provider_options']['clientId']);
                    }
                    echo $this->Form->control('parameters.provider_options.clientId', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'type' => 'password',
                        'data-type' => 'text',
                        'label' => ['text' => ' Client Secret'],
                    ];
                    echo $this->Form->control('parameters.provider_options.clientSecret', $opts);

                    ?>
                </fieldset>
                <div class="integrationCredentials application-urls">
                    <p class="mb-0">Please make sure the following URL's are configured in
                        <strong>Azure Portal > App Registration > Authentication</strong></p>
                    <ul class="mt-0">
                        <?php
                        $baseUrl = Router::fullBaseUrl();
                        $codeUrl = Router::url(['prefix' => 'Administrators', 'controller' => 'IntegrationCredentials', 'action' => 'authenticate', 'code'], true);
                        $authUrl = Router::url(['prefix' => 'Administrators', 'controller' => 'IntegrationCredentials', 'action' => 'authenticate', 'microsoft-open-auth-2'], true);
                        $logoutUrl = Router::url(['prefix' => false, 'controller' => 'Logout'], true);

                        echo "<li>{$baseUrl}</li>";
                        echo "<li>{$codeUrl}</li>";
                        echo "<li>{$authUrl}</li>";
                        echo "<li>{$logoutUrl}</li>";
                        ?>
                    </ul>
                </div>
                <div class="integrationCredentials application-urls">
                    <p class="mb-0">Optional URL's configured in
                        <strong>Azure Portal > App Registration > Branding & Properties</strong></p>
                    <ul class="mt-0">
                        <?php
                        $baseUrl = Router::fullBaseUrl();
                        $termsUrl = Router::url(['prefix' => 'Administrators', 'controller' => 'Contents', 'action' => 'terms-and-conditions'], true);
                        $privacyUrl = Router::url(['prefix' => 'Administrators', 'controller' => 'Contents', 'action' => 'privacy-policy'], true);

                        echo "<li>{$baseUrl}</li>";
                        echo "<li>{$termsUrl}</li>";
                        echo "<li>{$privacyUrl}</li>";
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'integrationCredentials'], $options);

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
