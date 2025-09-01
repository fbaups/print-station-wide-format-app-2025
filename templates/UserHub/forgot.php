<?php
/**
 * @var AppView $this
 */

use App\View\AppView;

$this->append('viewCustomScripts');
//echo $this->Html->script('script-name');
$this->end();

$this->append('css');
echo $this->Html->css('instance');
$this->end();

$this->assign('title', 'Forgot Password');
$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __(APP_NAME . ' Forgot Password'));
$this->set('headerSubTitle', __(""));
?>

<div class="container-xl px-4">
    <div class="row justify-content-center">
        <div class="col-lg">
            <!-- Basic form-->
            <?php
            $formOpts = [];
            echo $this->Form->create(null, $formOpts);
            ?>
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header justify-content-center">
                    <?php
                    if (empty(APP_LOGO)) {
                        ?>
                        <h3 class="fw-light my-4 text-center">
                            <?= APP_NAME ?><br>Forgot Password
                        </h3>
                        <?php
                    } else {
                        ?>
                        <a class="h-100" href="<?= APP_LINK_HOME ?>">
                            <?php
                            $options = [
                                'class' => "img-fluid mx-auto d-block mt-2",
                                'style' => "max-height: 5rem",
                                'alt' => APP_NAME . 'Login',
                            ];
                            echo $this->Html->image(APP_LOGO, $options)
                            ?>
                        </a>
                        <h3 class="fw-light my-4 text-center">
                            Forgot Password
                        </h3>
                        <?php
                    }
                    ?>
                </div>
                <div class="card-body">
                    <?php
                    $newTemplate = [
                        'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                    ];
                    $this->Form->setTemplates($newTemplate);

                    $defaultOptions = [
                    ];

                    $options = [
                        'label' => false,
                        'class' => 'form-control mb-3',
                        'placeholder' => 'Username or Email',
                        'templateVars' => ['wrapperClass' => 'wrapper-username'],
                    ];
                    echo $this->Form->control('username', $options);

                    $options = [
                        'label' => false,
                        'class' => 'form-control mb-3',
                        'type' => 'text',
                        'placeholder' => 'Email',
                        'templateVars' => ['wrapperClass' => 'wrapper-email d-none'],
                    ];
                    echo $this->Form->control('email', $options);

                    /*
                     * Submit Options
                     */
                    $submitOptions = [
                        'class' => 'btn btn-primary',
                    ];
                    $backLink = [
                        'prefix' => false, 'controller' => 'UserHub', 'action' => 'login'
                    ];
                    $backLinkOptions = [
                        'class' => 'small',
                    ];
                    ?>

                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <?= $this->Html->link('Back to Login', $backLink, $backLinkOptions) ?>
                        <?= $this->Form->submit('Send Reset Link', $submitOptions) ?>
                    </div>

                </div>
            </div>
            <?php
            echo $this->Form->end();
            $this->Form->resetTemplates();
            ?>
        </div>
    </div>
</div>

<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        var usernameField = $("input[name*='username']");

        var emailField = $("input[name*='email']");

        usernameField.keyup(function () {
            emailField.val(this.value);
        });
    });
</script>
<?php
$this->end();
?>

