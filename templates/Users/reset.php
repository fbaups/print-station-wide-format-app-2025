<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */

$this->append('viewCustomScripts');
//echo $this->Html->script('script-name');
$this->end();

$this->append('css');
echo $this->Html->css('instance');
$this->end();

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __(APP_NAME . ' Password Reset'));
$this->set('headerSubTitle', __(""));
$this->setLayout('one-page-form');
?>

<div class="container-xl px-4">
    <div class="row justify-content-center">
        <div class="col-lg">
            <!-- Basic reset form-->
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header justify-content-center">
                    <?php
                    if (empty(APP_LOGO)) {
                        ?>
                        <h3 class="fw-light my-4 text-center">
                            <?= APP_NAME ?><br>Password Reset
                        </h3>
                        <?php
                    } else {
                        ?>
                        <a class="h-100" href="/">
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
                            Password Reset
                        </h3>
                        <?php
                    }
                    ?>
                </div>
                <div class="card-body">
                    <?php
                    $formOptions = [
                    ];

                    $newTemplate = [
                        'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                    ];
                    $this->Form->setTemplates($newTemplate);

                    echo $this->Form->create($user, $formOptions);

                    $defaultOptions = [
                    ];

                    $options = [
                        'label' => false,
                        'class' => 'form-control mb-3',
                        'type' => 'password',
                        'value' => '',
                        'placeholder' => 'New Password',
                        'templateVars' => ['wrapperClass' => 'wrapper-password'],
                    ];
                    echo $this->Form->control('password', $options);

                    $options = [
                        'label' => false,
                        'class' => 'form-control mb-3',
                        'type' => 'password',
                        'value' => '',
                        'placeholder' => 'Password Repeated',
                        'templateVars' => ['wrapperClass' => 'wrapper-password'],
                    ];
                    echo $this->Form->control('password_1', $options);

                    /*
                     * Submit Options
                     */
                    $submitOptions = [
                        'class' => 'btn btn-primary',
                    ];
                    $backLink = [
                        'controller' => 'login',
                    ];
                    $backLinkOptions = [
                        'class' => 'small',
                    ];
                    ?>

                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <?= $this->Html->link('Back to Login', $backLink, $backLinkOptions) ?>
                        <?= $this->Form->submit('Reset', $submitOptions) ?>
                    </div>

                    <?php
                    echo $this->Form->end();
                    $this->Form->resetTemplates();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
