<?php
/**
 * @var \App\View\AppView $this
 * @var array $services
 */

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Background Services'));
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

<div class="container-fluid px-4">


    <div class="card mb-5">
        <div class="card-header">
            <?= __('Important Information') ?>
        </div>
        <div class="card-body">
            <div class="background-services index content">
                <p>
                    Interacting with the Background Services on this page may have unintended side effects.<br>
                    Killing a Background Services should be used as a last resort.
                    Consider using recycle or shutdown to gracefully stop Background Services.
                </p>
            </div>
        </div>
    </div>


    <div class="card mb-5">
        <div class="card-header">
            <?= __('Choose a Task') ?>
        </div>
        <div class="card-body">
            <div class="background-services index content">
                <p>
                    <?php
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 120px !important"
                    ];
                    echo $this->Html->link(
                        __('Install'),
                        ['action' => 'install',],
                        $options
                    )
                    ?>
                    Install (or re-install) the Windows Services.
                </p>
                <p>
                    <?php
                    $url = ['action' => 'uninstall'];
                    $options = [
                        'class' => "btn btn-primary btn-sm me-2",
                        'style' => "width: 120px !important",
                        'confirm' => __('Are you sure you want to uninstall the Windows Services for {0}?', APP_NAME),
                    ];
                    echo $this->Form->postLink(__('Uninstall'), $url, $options);
                    ?>
                    Uninstall the Windows Services.
                </p>
                <?php if (1 === 2) { ?>
                    <p>
                        <?php
                        $options = [
                            'class' => "btn btn-primary btn-sm me-2",
                            'style' => "width: 120px !important",
                        ];
                        echo $this->Html->link(
                            __('Start'),
                            ['action' => 'start', 'all'],
                            $options
                        )
                        ?>
                        Start all Background Services with a Current State of STOPPED or PAUSED and Startup Type is not
                        DISABLED
                    </p>
                    <p>
                        <?php
                        $options = [
                            'class' => "btn btn-primary btn-sm me-2",
                            'style' => "width: 120px !important",
                        ];
                        echo $this->Html->link(
                            __('Kill'),
                            ['action' => 'stop', 'all'],
                            $options
                        )
                        ?>
                        Kill all Background Services. If any Errands are being run, they will fail to complete.
                    </p>
                    <p>
                        <?php
                        $options = [
                            'class' => "btn btn-primary btn-sm me-2",
                            'style' => "width: 120px !important",
                        ];
                        echo $this->Html->link(
                            __('Recycle'),
                            ['action' => 'recycle'],
                            $options
                        )
                        ?>
                        Recycle all Services that are currently RUNNING.
                    </p>
                    <p>
                        <?php
                        $options = [
                            'class' => "btn btn-primary btn-sm me-2",
                            'style' => "width: 120px !important",
                        ];
                        echo $this->Html->link(
                            __('Start Errands'),
                            ['action' => 'start', 'all-errand-background-services'],
                            $options
                        )
                        ?>
                        Start all Errand Services.
                    </p>
                    <p>
                        <?php
                        $options = [
                            'class' => "btn btn-primary btn-sm me-2",
                            'style' => "width: 120px !important",
                        ];
                        echo $this->Html->link(
                            __('Start Messages'),
                            ['action' => 'start', 'all-message-background-services'],
                            $options
                        )
                        ?>
                        Start all Message Services.
                    </p>
                <?php } ?>
            </div>
        </div>
    </div>


    <div class="card mb-5">
        <div class="card-header">
            <strong><?php echo __("Installed Background Services") ?></strong>
        </div>
        <div class="card-body">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Current State</th>
                    <th scope="col">Startup Type</th>
                    <th scope="col" class="actions"><?= __('Actions') ?> </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($services as $service): ?>
                    <?php
                    /**
                     * Service states:
                     * RUNNING | PAUSED | STOPPED
                     *
                     * Service start types:
                     * DEMAND_START | DISABLED | (DELAYED) | AUTO_START
                     */
                    ?>

                    <tr class="background-service-record" data-background-service="<?= $service['name'] ?>">
                        <td class="background-service-name" data-background-service="<?= $service['name'] ?>">
                            <?= $service['name'] ?>
                        </td>
                        <td>
                            <span class="background-service-state" data-background-service="<?= $service['name'] ?>">
                            <?= $service['state'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="background-service-start-type"
                                  data-background-service="<?= $service['name'] ?>">
                            <?= $service['start_type'] ?>
                            </span>
                        </td>
                        <td class="actions" style="width: 25%">
                            <?php if ($service['start_type'] != 'DISABLED') { ?>
                                <div class="spinner-border spinner-border-sm d-none" role="status"
                                     data-background-service="<?= $service['name'] ?>"></div>

                                <span class="background-service-manage start link-blue pointer d-none"
                                      data-action="start"
                                      data-background-service="<?= $service['name'] ?>">Start</span>

                                <span class="background-service-manage kill link-blue pointer d-none"
                                      data-action="kill"
                                      data-background-service="<?= $service['name'] ?>">Kill</span>

                                <span class="action-divider d-none">|</span>

                                <span class="background-service-manage recycle link-blue pointer d-none"
                                      data-action="recycle"
                                      data-background-service="<?= $service['name'] ?>">Recycle</span>

                                <span class="action-divider d-none">|</span>

                                <span class="background-service-manage shutdown link-blue pointer d-none"
                                      data-action="shutdown"
                                      data-background-service="<?= $service['name'] ?>">Shutdown</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>


</div>


<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');

//DataTables initialisation for the index view...
echo $this->Html->script('datatables_manager');

?>
<script>
    $(document).ready(function () {

        $('.background-service-manage').on('click', function () {
            var serviceAction = $(this).attr('data-action');
            var serviceName = $(this).attr('data-background-service');
            var serviceRecord = $(this).closest('tr.background-service-record[data-background-service="' + serviceName + '"]')
            var serviceState = serviceRecord.find('span.background-service-state').text()

            serviceRecord.find('span.action-divider').addClass('d-none')
            serviceRecord.find('span.background-service-manage').addClass('d-none');
            serviceRecord.find('span.background-service-state').text("");
            serviceRecord.find('span.background-service-state').attr("background-service-state-previous", serviceState);

            var spinner = serviceRecord.find('div.spinner-border');
            spinner.removeClass('d-none');

            var targetUrl = homeUrl + 'background-services/manage';
            var formData = new FormData();
            formData.append("service-action", serviceAction);
            formData.append("service-name", serviceName);

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
        })

        getServicesInfo();

        function getServicesInfo() {
            var targetUrl = homeUrl + 'background-services/services-info';
            $.ajax({
                url: targetUrl,
                dataType: 'json',
                success: function (data) {
                    $.each(data, function (key, value) {
                        var name = value["name"];
                        var serviceRecord = $('tr.background-service-record[data-background-service="' + name + '"]');
                        var state = value["state"];
                        var startType = value["start_type"];

                        serviceRecord.find('.background-service-start-type').text(startType);

                        var previousState = serviceRecord.find('span.background-service-state').attr('background-service-state-previous');

                        if (previousState === state) {
                            return;
                        }

                        serviceRecord.find('span.background-service-state').removeAttr('background-service-state-previous');

                        if (state === 'RUNNING') {
                            $('span.start[data-background-service="' + name + '"]').addClass('d-none');
                            $('span.kill[data-background-service="' + name + '"]').removeClass('d-none');
                            $('span.recycle[data-background-service="' + name + '"]').removeClass('d-none');
                            $('span.shutdown[data-background-service="' + name + '"]').removeClass('d-none');
                            serviceRecord.find('span.action-divider').removeClass('d-none')
                        }
                        if (state === 'STOPPED') {
                            $('span.start[data-background-service="' + name + '"]').removeClass('d-none');
                            $('span.kill[data-background-service="' + name + '"]').addClass('d-none');
                            $('span.recycle[data-background-service="' + name + '"]').addClass('d-none');
                            $('span.shutdown[data-background-service="' + name + '"]').addClass('d-none');
                            serviceRecord.find('span.action-divider').addClass('d-none')
                        }
                        if (state === 'PAUSED') {
                            $('span.start[data-background-service="' + name + '"]').addClass('d-none');
                            $('span.kill[data-background-service="' + name + '"]').removeClass('d-none');
                            $('span.recycle[data-background-service="' + name + '"]').addClass('d-none');
                            $('span.shutdown[data-background-service="' + name + '"]').addClass('d-none');
                            serviceRecord.find('span.action-divider').addClass('d-none')
                        }

                        serviceRecord.find('span.background-service-state').text(state);
                        serviceRecord.find('div.spinner-border').addClass('d-none');
                    });
                },
                error: function (xhr, status, error) {
                },
                complete: function (xhr, status) {
                },
                timeout: function () {
                }
            });

            var serviceInfoQueryId = setTimeout(getServicesInfo, 5000);
        }

    });
</script>
<?php
$this->end();
?>


