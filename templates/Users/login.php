<?php
/**
 * @var \App\View\AppView $this
 * @var User $user
 */

//dd($user);

use App\Model\Entity\User;
use Cake\Core\Configure;

$this->append('viewCustomScripts');
//echo $this->Html->script('script-name');
$this->end();

$this->append('css');
echo $this->Html->css('instance');
$this->end();

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __(APP_NAME . ' Login'));
$this->set('headerSubTitle', __(""));
$this->setLayoutPath('interface');
$this->setLayout('login');

$coreLib = [
    'base' => false,
    'bootstrap' => false,
    'datatables' => false,
    'feather-icons' => false,
    'fontawesome' => false,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);
?>

<div class="login-form">
    <div class="login-form-cols d-grid grid-cols-2 min-h-100vh">
        <div class="form-left-col d-flex flex-column">
            <div class="form-header bg-sea-green d-flex align-items-center justify-content-between gap-2">
                <h2 class="site-name mb-0">
                    <?php
                    $option = [

                    ];
                    echo $this->Html->image('/interface/assets/images/fujifilm_white_negative.svg', $option);
                    ?>
                </h2>
                <div class="form-header-switch custom-switch d-inline-flex align-items-center">
                    <label class="switch">
                        <input type="checkbox" id="modeToggle"/>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="container flex-1 d-flex align-items-center justify-content-center py-3 py-lg-4">
                <div class="form-wrapper w-100">
                    <h3 class="form-main-text text-gray font-medium"><?= APP_NAME ?></h3>
                    <p class="text-gray">Please enter your details to proceed.</p>

                    <?php echo $this->Flash->render() ?>

                    <?php
                    $formOptions = [];
                    echo $this->Form->create($user, $formOptions);
                    ?>
                    <div class="form-elem mb-4">
                        <label for="username" class="form-label text-battleship-gray font-medium">
                            User Name or Email Address
                        </label>
                        <?php
                        $options = [
                            'label' => false,
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Username or Email',
                            'templateVars' => ['wrapperClass' => 'wrapper-username'],
                        ];
                        echo $this->Form->control('username', $options);
                        ?>
                    </div>
                    <div class="form-elem mb-4">
                        <label for="password" class="form-label text-battleship-gray font-medium">
                            Password
                        </label>
                        <div class="form-control-wrapper pwd-form-control-wrapper position-relative">
                            <?php
                            $options = [
                                'label' => false,
                                'class' => 'form-control mb-3',
                                'type' => 'password',
                                'placeholder' => 'Password',
                                'templateVars' => ['wrapperClass' => 'wrapper-password'],
                            ];
                            echo $this->Form->control('password', $options);
                            ?>
                            <button type="button" class="pwd-toggle-btn icon position-absolute">
                                <?php
                                $option = [
                                    'class' => 'eye-open-icon',
                                ];
                                echo $this->Html->image('/interface/assets/images/icons/eye.svg', $option);

                                $option = [
                                    'class' => 'eye-slash-icon',
                                ];
                                echo $this->Html->image('/interface/assets/images/icons/eye_slash.svg', $option);
                                ?>
                            </button>
                        </div>
                    </div>

                    <div
                        class="d-flex align-items-center justify-content-between gap-2 flex-wrap form-bottom d-none">
                        <a href="#" class="text-gray text-decoration-underline">Forget Password?</a>
                    </div>

                    <?php
                    $submitOptions = [
                        'class' => 'bg-sea-green btn btn-lg btn-primary w-100 text-white font-medium',
                    ];
                    echo $this->Form->submit('Login', $submitOptions)
                    ?>

                    <?php
                    echo $this->Form->end();
                    $this->Form->resetTemplates();
                    ?>
                </div>
            </div>
            <div class="form-footer d-flex align-items-center justify-content-between gap-2">
                <p class="mb-0 text-base"><?php echo date("Y") ?> &copy; Print Station Wide Format</p>
                <?php
                $options = [
                    'class' => 'text-base text-decoration-none'
                ];
                echo $this->Html->link('Help', ['controller' => 'docs']);
                ?>
            </div>
        </div>
        <div class="form-right-col position-relative">
            <?php
            $option = [
                'class' => 'w-100 h-100 object-cover position-absolute light-login-banner',
            ];
            echo $this->Html->image('/interface/assets/images/login_banner_light.png', $option);

            $option = [
                'class' => 'w-100 h-100 object-cover position-absolute dark-login-banner',
            ];
            echo $this->Html->image('/interface/assets/images/login_banner_dark.png', $option);
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
        var usernameValue;
        var passwordField = $("input[name*='password']");

        var pageReloadInterval = 1000 * 60 * 2; //reload this page every 2 mins to get new csrf
        var pageLoadTimestamp = Date.now();
        var pageReloadTimestamp = pageLoadTimestamp + pageReloadInterval;
        var pageReloadId = null;
        setupPageReload();

        usernameField.keyup(function () {
            extendPageReload();
        }).change(function () {
            runUser();
        });

        passwordField.keyup(function () {
            extendPageReload();
        });

        function runUser() {
            usernameValue = usernameField.val();

            var targetUrl = homeUrl + 'users/pre-login';
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

        function extendPageReload() {
            clearInterval(pageReloadId);
            var extendBy = 1000 * 10; //extend by 10 seconds if almost timeout
            var reloadingInSecs = pageReloadTimestamp - Date.now();
            if (reloadingInSecs < extendBy) {
                pageReloadTimestamp = pageReloadTimestamp + extendBy;
            }
            setupPageReload();
        }

        function setupPageReload() {
            var reloadTimeout = pageReloadTimestamp - Date.now();

            pageReloadId = setInterval(function () {
                window.location.href = loginUrl;
            }, reloadTimeout);
        }

    });
</script>
<?php
$this->end();
?>

