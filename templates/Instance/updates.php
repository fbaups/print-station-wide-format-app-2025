<?php
/**
 * @var \App\View\AppView $this
 *
 * @var array $versions
 * @var string $currentVersion
 * @var string $remote_update_url
 * @var int $remote_update_url_id
 * @var int $countOfRunningServices
 * @var Errand $fsoErrand
 */

use App\Model\Entity\Errand;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Update {0}', APP_NAME));
$this->set('headerSubTitle', __(""));

//control what Libraries are loaded
$coreLib = [
    'bootstrap' => true,
    'datatables' => true,
    'feather-icons' => true,
    'fontawesome' => true,
    'jQuery' => true,
    'jQueryUI' => false,
];
$this->set('coreLib', $coreLib);
?>
<div class="container-fluid px-4">

    <?php
    if (!$fsoErrand) {
        ?>
        <div class="row">
            <div class="col-lg-12 installers fso-stats">
                <p class="alert alert-info">
                    <?php
                    if ($countOfRunningServices === 0) {
                        $servicesText = 'Background Services are required to perform an index.';
                    } else {
                        $servicesText = '';
                    }
                    echo __("The Application has not been indexed recently. An upgrade may take several minutes to complete. $servicesText");
                    ?>
                </p>
            </div>
        </div>
        <?php
    } elseif (!$fsoErrand->started || !$fsoErrand->completed) {
        ?>
        <div class="row">
            <div class="col-lg-12 installers fso-stats">
                <p class="alert alert-info">
                    <?php
                    echo __("The Application is being indexed. An upgrade may take several minutes to complete. You can still upgrade or try again in a few minutes when the indexing is complete.");
                    ?>
                </p>
            </div>
        </div>
        <?php
    }
    ?>

    <?php
    if (!$versions) {
        ?>
        <div class="row">
            <div class="col-lg-12 installers update">
                <?php
                echo __("Sorry, something went wrong with the Update List. Please try again later.");
                echo __("<br>Edit the Update URL ");
                echo $this->Html->link($remote_update_url, ['controller' => 'settings', 'action' => 'edit', $remote_update_url_id]);
                ?>
            </div>
        </div>
        <?php
        return;
    }
    ?>

    <?php
    if ($countOfRunningServices) {
        ?>
        <div class="row">
            <div class="col-lg-12 installers update">
                <?php
                $link = $this->Html->link(' Background Services', ['controller' => 'background-services', 'action' => 'index']);
                echo __("There are {0} Background Services. Please shutdown {1}.", $countOfRunningServices, $link);
                ?>
            </div>
        </div>
        <?php
    }
    ?>

    <div class="row">
        <div class="column">
            <div class="numbers index">
                <div class="card">
                    <div class="card-header">
                        <i class="fa fa-align-justify"></i> <?= __('Published Updates') ?>
                    </div>
                    <div class="card-body">
                        <p>
                            Update URL
                            <strong>
                                <?php
                                echo __("{0}", $remote_update_url);
                                ?>
                            </strong>

                            <small>
                                <?php
                                //echo $this->Html->link('Edit', ['controller' => 'settings', 'action' => 'edit', $remote_update_url_id]);
                                ?>
                            </small>
                        </p>

                        <table class="table table-bordered table-striped table-sm">
                            <thead>
                            <tr>
                                <th scope="col" style="width: 10%"><?= __('Version') ?></th>
                                <th scope="col" style="width: 20%"><?= __('Released') ?></th>
                                <th scope="col"><?= __('Note') ?></th>
                                <th scope="col" class="actions"><?= __('Actions') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($versions as $k => $versionInfo): ?>
                                <tr>
                                    <td><?= $versionInfo['tag'] ?></td>
                                    <td>
                                        <?php
                                        if (isset($versionInfo['release_date'])) {
                                            $date = $versionInfo['release_date'];
                                        } else {
                                            if (isset($versionInfo['installer_url'])) {
                                                $date = pathinfo($versionInfo['installer_url'], PATHINFO_FILENAME);
                                                $date = substr($date, 0, 15);
                                            } else {
                                                $date = null;
                                            }
                                        }
                                        echo $date;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (isset($versionInfo['desc'])) {
                                            echo h($versionInfo['desc']);
                                        } else {
                                            echo h('N/A');
                                        }
                                        ?>
                                    </td>
                                    <td class="actions">
                                        <?php
                                        if (isset($versionInfo['installer_url'])) {
                                            $upgradeUrlHashed = \arajcany\ToolBox\Utility\Security\Security::encrypt64Url($versionInfo['release_date']);
                                            if (version_compare($currentVersion, $versionInfo['tag']) == 0) {
                                                echo $this->Html->link(__('Reinstall'), ['action' => 'upgrade', $upgradeUrlHashed]);
                                            } elseif (version_compare($currentVersion, $versionInfo['tag']) < 0) {
                                                echo $this->Html->link(__('Upgrade'), ['action' => 'upgrade', $upgradeUrlHashed]);
                                            }
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
