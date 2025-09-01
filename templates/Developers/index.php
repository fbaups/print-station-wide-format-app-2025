<?php
/**
 * @var \App\View\AppView $this
 *
 */

use App\Utility\Feedback\DebugCapture;

?>

<div class="row">
    <div class="col-md-12 col-xl-8 m-xl-auto">

        <div class="card mb-5">
            <div class="card-header">
                <?= __('Developer Tasks') ?>
            </div>
            <div class="card-body">
                <div class="updates content">
                    <?php
                    $options = [
                        'class' => '',
                        'escape' => false
                    ];
                    $link = $this->Html->link(
                        __('Perform Migrations'),
                        ['controller' => 'instance', 'action' => 'migrations'],
                        $options
                    );
                    ?>
                    <p><?= $link ?> if you have handwritten a migration you can run them here.</p>
                    <?php
                    $options = [
                        'class' => '',
                        'escape' => false
                    ];
                    $link = $this->Html->link(
                        __('Clear Cache'),
                        ['controller' => 'instance', 'action' => 'clear-cache'],
                        $options
                    );
                    $link2 = $this->Html->link(
                        __('Background Services'),
                        ['controller' => 'background-services'],
                        $options
                    );
                    ?>
                    <p><?= $link ?> if you have made some configuration changes. Stop the <?= $link2 ?> first.</p>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header">
                <?= __('Localisation Constants') ?>
            </div>
            <div class="card-body">
                <div class="auth-user content">
                    Localisation Constants are determined in the following order:
                    <ul>
                        <li>Static values are hard coded into the Application.</li>
                        <li>SuperAdmin can provide Localisation values in the Settings Page to override the static values.</li>
                        <li>User can provide Localisation values in their Profile Page to override the SuperAdmin set values.</li>
                    </ul>
                    When a user, for example, views a date in the Dashboard, it will be formatted according to the following values.
                    <?php
                    $dt = (new \Cake\I18n\DateTime())->setTimezone(LCL_TZ);
                    ?>
                    <table class="table font-monospace">
                        <thead>
                        <tr>
                            <th scope="col">Constant</th>
                            <th scope="col">Value</th>
                            <th scope="col">Example</th>
                            <th scope="col">Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td>LCL_LOCATION</td><td><?php echo LCL_LOCATION ?></td>  <td></td>      <td>Location of the User or App</td></tr>
                        <tr><td>LCL_LOCALE</td><td><?php echo LCL_LOCALE ?></td>      <td></td>      <td>Language of the User or App</td></tr>
                        <tr><td>LCL_DF</td><td><?php echo LCL_DF ?></td>              <td><?php echo $dt->format(LCL_DF) ?></td>      <td>Date Format of the User or App</td></tr>
                        <tr><td>LCL_TF</td><td><?php echo LCL_TF ?></td>              <td><?php echo $dt->format(LCL_TF) ?></td>      <td>Time Format of the User or App</td></tr>
                        <tr><td>LCL_DTF</td><td><?php echo LCL_DTF ?></td>            <td><?php echo $dt->format(LCL_DTF) ?></td>      <td>Datetime Format of the User or App</td></tr>
                        <tr><td>LCL_WS</td><td><?php echo LCL_WS ?></td>              <td></td>      <td>Day the week starts on for the User or App</td></tr>
                        <tr><td>LCL_TZ</td><td><?php echo LCL_TZ ?></td>              <td></td>      <td>Default Timezone of the User or App</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-header">
                <?= __('AuthUser Details') ?>
            </div>
            <div class="card-body">
                <div class="auth-user content">
                    <pre><?php
                        echo json_encode($this->AuthUser->user(), JSON_PRETTY_PRINT);
                        ?></pre>
                </div>
            </div>
        </div>

    </div>
</div>
