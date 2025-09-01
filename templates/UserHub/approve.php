<?php
/**
 * @var AppView $this
 * @var User $user
 */

use App\Model\Entity\User;
use App\View\AppView;

$this->append('viewCustomScripts');
//echo $this->Html->script('script-name');
$this->end();

$this->append('css');
echo $this->Html->css('instance');
$this->end();

$this->assign('title', 'Approve User');
$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __(APP_NAME . ' Approve User Access Request'));
$this->set('headerSubTitle', __(""));
?>

<div class="container-xl px-4">
    <div class="row justify-content-center">
        <div class="col-lg">
            <!-- Basic confirm form-->
            <?php
            $formOpts = [];
            echo $this->Form->create($user, $formOpts);
            ?>
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header justify-content-center">
                    <?php
                    if (empty(APP_LOGO)) {
                        ?>
                        <h3 class="fw-light my-4 text-center">
                            <?= APP_NAME ?><br>Approve User Access Request
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
                            Approve User Access Request
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
                    ?>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <?php
                            $options = [
                                //'label' => false,
                                'class' => 'form-control mb-3',
                                'type' => 'text',
                                'placeholder' => 'Username',
                                'templateVars' => ['wrapperClass' => 'wrapper-username'],
                            ];
                            echo $this->Form->control('username', $options);
                            ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <?php
                            $options = [
                                //'label' => false,
                                'class' => 'form-control mb-3',
                                'type' => 'text',
                                'placeholder' => 'Email',
                                'templateVars' => ['wrapperClass' => 'wrapper-username'],
                            ];
                            echo $this->Form->control('email', $options);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <?php
                            $options = [
                                //'label' => false,
                                'class' => 'form-control mb-3',
                                'type' => 'text',
                                'placeholder' => 'First Name',
                                'templateVars' => ['wrapperClass' => 'wrapper-username'],
                            ];
                            echo $this->Form->control('first_name', $options);
                            ?>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-6">
                            <?php
                            $options = [
                                //'label' => false,
                                'class' => 'form-control mb-3',
                                'type' => 'text',
                                'placeholder' => 'Last Name',
                                'templateVars' => ['wrapperClass' => 'wrapper-username'],
                            ];
                            echo $this->Form->control('last_name', $options);
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <?php
                            $options = [
                                //'label' => false,
                                'class' => 'form-control mb-3',
                                'type' => 'text',
                                'placeholder' => 'mobile',
                                'templateVars' => ['wrapperClass' => 'wrapper-username'],
                            ];
                            echo $this->Form->control('mobile', $options);
                            ?>
                        </div>
                    </div>


                    <?php
                    /*
                     * Submit Options
                     */
                    $submitOptions = [
                        'class' => 'btn btn-primary',
                    ];
                    $backLink = [
                        'prefix' => false, 'controller' => '/'
                    ];
                    $backLinkOptions = [
                        'class' => 'small',
                    ];
                    ?>

                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <?= $this->Html->link('Back to ' . APP_NAME, $backLink, $backLinkOptions) ?>
                        <?= $this->Form->submit('Confirm', $submitOptions) ?>
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
