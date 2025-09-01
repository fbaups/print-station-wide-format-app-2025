<?php
/**
 * @var AppView $this
 */

use App\View\AppView;
use Cake\Cache\Cache;
use Cake\Core\Configure;

$this->append('viewCustomScripts');
echo $this->Html->script('instance');
$this->end();

$this->append('css');
echo $this->Html->css('instance');
$this->end();

$this->assign('title', 'Application Configuration');
$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Application Configuration'));
$this->set('headerSubTitle', __(""));
?>

<div class="container px-4 my-5">
    <div class="card">
        <div class="card-header justify-content-center">
            <h3 class="fw-light mt-2 mb-0">Application Configuration</h3>
        </div>
        <div class="card-body">
            <div class="row updates-holder d-none">
                <div class="column">
                    <section id="updates">
                        <header><h3>Configuration Complete</h3></header>
                        <p>
                            Please proceed to the
                            <?php echo $this->Html->link("Login", ['prefix' => false, 'controller' => 'UserHub', 'action' => 'login']) ?>
                            page.
                        </p>
                    </section>
                </div>
            </div>
            <div class="row instance-holder">
                <div class="column">
                    <section id="instance">
                        <header><h3>Instance Configuration</h3></header>
                        <?php
                        $formOpts = ['id' => 'instance-configuration'];
                        echo $this->Form->create(null, $formOpts);

                        $newTemplate = [
                            'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                        ];
                        $this->Form->setTemplates($newTemplate);

                        $defaultOptions = [
                        ];

                        $options = [
                            'label' => ['text' => 'Instance Type'],
                            'class' => 'form-control mb-3',
                            'templateVars' => ['wrapperClass' => 'driver-select'],
                            'options' => [
                                '-' => '--- Please Select ---',
                                'dev' => 'Development',
                                'test' => 'Test',
                                'prod' => 'Production',
                            ],
                            'default' => $currentMode = Configure::read('mode'),
                        ];
                        $options = array_merge($defaultOptions, $options);
                        echo $this->Form->control('instance-configuration-selection', $options);

                        /*
                         * Submit
                         */
                        $submitOptions = [
                            'class' => 'btn btn-primary instance-submit',
                            'type' => 'button',
                        ];
                        echo $this->Form->button('Apply', $submitOptions);
                        echo $this->Form->end();
                        $this->Form->resetTemplates();
                        ?>
                    </section>
                </div>
            </div>
            <hr class="hr-1 my-5">
            <div class="row database-holder">
                <div class="column">
                    <section id="database">
                        <header><h3>Database Configuration</h3></header>
                        <?php
                        $formOpts = ['id' => 'database-driver'];
                        echo $this->Form->create(null, $formOpts);

                        $newTemplate = [
                            'inputContainer' => '<div class="input {{type}}{{required}} {{wrapperClass}}">{{content}}</div>'
                        ];
                        $this->Form->setTemplates($newTemplate);

                        $defaultOptions = [
                        ];

                        $options = [
                            'label' => ['text' => 'Database Type'],
                            'class' => 'form-control mb-3',
                            'templateVars' => ['wrapperClass' => 'driver-select'],
                            'options' => [
                                '-' => '--- Please Select ---',
                                'Sqlserver' => 'MS SQL Server',
                                'Mysql' => 'MySQL/MariaDB',
                                'Sqlite' => 'SQLite',
                            ]
                        ];
                        $options = array_merge($defaultOptions, $options);
                        echo $this->Form->control('database-driver-selection', $options);

                        /*
                         * SQL Server Options
                         */
                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Fully qualified host name',
                            'value' => '.\SQLEXPRESS',
                            'templateVars' => ['wrapperClass' => 'server server-sql d-none'],
                            'label' => ['text' => 'MS SQL Server Host'],
                        ];
                        echo $this->Form->control('server.sql.host', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Leave empty or non-standard port number',
                            'templateVars' => ['wrapperClass' => 'server server-sql d-none'],
                        ];
                        echo $this->Form->control('server.sql.port', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Username. Leave blank for Windows authentication',
                            'templateVars' => ['wrapperClass' => 'server server-sql d-none'],
                        ];
                        echo $this->Form->control('server.sql.username', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'type' => 'password',
                            'placeholder' => 'Password. Leave blank for Windows authentication',
                            'templateVars' => ['wrapperClass' => 'server server-sql d-none'],
                        ];
                        echo $this->Form->control('server.sql.password', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Database name',
                            'value' => APP_NAME,
                            'templateVars' => ['wrapperClass' => 'server server-sql d-none'],
                        ];
                        echo $this->Form->control('server.sql.database', $options);

                        /*
                         * MySQL  Options
                         */
                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Fully qualified host name',
                            'value' => 'localhost',
                            'templateVars' => ['wrapperClass' => 'server server-mysql d-none'],
                            'label' => ['text' => 'MySQL/MariaDB Server Host'],
                        ];
                        echo $this->Form->control('server.mysql.host', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Leave empty or non-standard port number',
                            'templateVars' => ['wrapperClass' => 'server server-mysql d-none'],
                        ];
                        echo $this->Form->control('server.mysql.port', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Username. Leave blank for Windows authentication',
                            'templateVars' => ['wrapperClass' => 'server server-mysql d-none'],
                        ];
                        echo $this->Form->control('server.mysql.username', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'type' => 'password',
                            'placeholder' => 'Password. Leave blank for Windows authentication',
                            'templateVars' => ['wrapperClass' => 'server server-mysql d-none'],
                        ];
                        echo $this->Form->control('server.mysql.password', $options);

                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'Database name',
                            'value' => APP_NAME,
                            'templateVars' => ['wrapperClass' => 'server server-mysql d-none'],
                        ];
                        echo $this->Form->control('server.mysql.database', $options);

                        /*
                         * SQLite  Options
                         */
                        $options = [
                            'class' => 'form-control mb-3',
                            'placeholder' => 'SQLite Database File Name',
                            'value' => APP_NAME,
                            'templateVars' => ['wrapperClass' => 'server server-sqlite d-none'],
                        ];
                        echo $this->Form->control('server.sqlite.name', $options);

                        /*
                         * Submit
                         */
                        $submitOptions = [
                            'class' => 'btn btn-primary driver-submit',
                            'disabled' => 'disabled',
                            'type' => 'button',
                        ];
                        echo $this->Form->button('Apply', $submitOptions);
                        echo $this->Form->end();
                        $this->Form->resetTemplates();
                        ?>
                    </section>
                </div>
            </div>
            <hr class="hr-1 my-5">
            <div class="row">
                <div class="column">
                    <section id="environment">
                        <header><h3>Server Configuration</h3></header>
                        <ul>
                            <?php if (version_compare(PHP_VERSION, '7.2.0', '>=')) : ?>
                                <li class="bullet success">Your version of PHP is 8.1.0 or higher
                                    (detected <?= PHP_VERSION ?>).
                                </li>
                            <?php else : ?>
                                <li class="bullet problem">Your version of PHP is too low. You need PHP 8.1.0 or higher
                                    to use
                                    CakePHP (detected <?= PHP_VERSION ?>).
                                </li>
                            <?php endif; ?>

                            <?php if (extension_loaded('mbstring')) : ?>
                                <li class="bullet success">Your version of PHP has the mbstring extension loaded.</li>
                            <?php else : ?>
                                <li class="bullet problem">Your version of PHP does NOT have the mbstring extension
                                    loaded.
                                </li>
                            <?php endif; ?>

                            <?php if (extension_loaded('openssl')) : ?>
                                <li class="bullet success">Your version of PHP has the openssl extension loaded.</li>
                            <?php elseif (extension_loaded('mcrypt')) : ?>
                                <li class="bullet success">Your version of PHP has the mcrypt extension loaded.</li>
                            <?php else : ?>
                                <li class="bullet problem">Your version of PHP does NOT have the openssl or mcrypt
                                    extension
                                    loaded.
                                </li>
                            <?php endif; ?>

                            <?php if (extension_loaded('intl')) : ?>
                                <li class="bullet success">Your version of PHP has the intl extension loaded.</li>
                            <?php else : ?>
                                <li class="bullet problem">Your version of PHP does NOT have the intl extension
                                    loaded.
                                </li>
                            <?php endif; ?>
                        </ul>
                    </section>
                </div>
            </div>
            <hr class="hr-1 my-5">
            <div class="row">
                <div class="column">
                    <section id="filesystem">
                        <header><h3>File System</h3></header>
                        <ul>
                            <?php if (is_writable(TMP)) : ?>
                                <li class="bullet success">Your tmp directory is writable.</li>
                            <?php else : ?>
                                <li class="bullet problem">Your tmp directory is NOT writable.</li>
                            <?php endif; ?>

                            <?php if (is_writable(LOGS)) : ?>
                                <li class="bullet success">Your logs directory is writable.</li>
                            <?php else : ?>
                                <li class="bullet problem">Your logs directory is NOT writable.</li>
                            <?php endif; ?>

                            <?php $settings = Cache::getConfig('_cake_core_'); ?>
                            <?php if (!empty($settings)) : ?>
                                <li class="bullet success">The <em><?= $settings['className'] ?>Engine</em> is being
                                    used for
                                    core caching.
                                </li>
                            <?php else : ?>
                                <li class="bullet problem">Your cache is NOT working. Please check the settings in
                                    config/app.php
                                </li>
                            <?php endif; ?>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
