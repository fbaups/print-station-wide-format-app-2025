<?php
/**
 * @var AppView $this
 * @var User $user
 */

//dd($user);

use App\Model\Entity\User;
use App\View\AppView;
use Cake\Core\Configure;

$this->append('viewCustomScripts');
//echo $this->Html->script('script-name');
$this->end();

$this->append('css');
echo $this->Html->css('instance');
$this->end();

$this->assign('title', 'Login');
$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __(APP_NAME . ' Login'));
$this->set('headerSubTitle', __(""));
?>

<div class="container-xl px-4">
    <div class="row justify-content-center">
        <div class="col-lg">
            <!-- Basic login form-->
            <?php
            $formOpts = [];
            echo $this->Form->create($user, $formOpts);
            ?>
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header justify-content-center">
                    <?php
                    if (empty(APP_LOGO)) {
                        ?>
                        <h3 class="fw-light my-4 text-center">
                            <?= APP_NAME ?> Login
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
                            <?= APP_NAME ?> Login
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
                        'type' => 'password',
                        'placeholder' => 'Password',
                        'templateVars' => ['wrapperClass' => 'wrapper-password'],
                    ];
                    echo $this->Form->control('password', $options);

                    /*
                     * Submit Options
                     */
                    $submitOptions = [
                        'class' => 'btn btn-primary',
                    ];
                    $resetLink = [
                        'prefix' => false, 'controller' => 'UserHub', 'action' => 'forgot'
                    ];
                    $resetOptions = [
                        'class' => 'small',
                    ];
                    ?>

                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <?= $this->Html->link('Forgot Password?', $resetLink, $resetOptions) ?>
                        <?= $this->Form->submit('Login', $submitOptions) ?>
                    </div>

                </div>
                <div class="card-footer text-center">
                    <div class="small">
                        <?php
                        $allowedToRequest = ['self', 'admin'];
                        if (in_array(Configure::read("Settings.self_registration"), $allowedToRequest)) {
                            $invitationOptions = [

                            ];
                            $invitationLink = [
                                'prefix' => false, 'controller' => 'UserHub', 'action' => 'request'
                            ];
                            echo $this->Html->link('Need an account? Request an Invitation!', $invitationLink, $invitationOptions);
                        } else {
                            echo "&nbsp;";
                        }
                        ?>
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
        //update the CSRF Token
        setInterval(function () {
            updateCsrfToken('open');
        }, 2 * 60 * 1000); // 2 minutes

        var usernameField = $("input[name*='username']");
        var usernameValue;
        var passwordField = $("input[name*='password']");

        usernameField.change(function () {
            runUser();
        });

        function runUser() {
            usernameValue = usernameField.val();

            var targetUrl = homeUrl + 'primer';
            var formData = new FormData();
            formData.append("username", usernameValue);

            $.ajax({
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                type: "POST",
                url: targetUrl,
                async: true,
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                timeout: 60000,

                success: function (response) {
                },
                error: function (e) {
                }
            })
        }

    });
</script>
<?php
$this->end();
?>

