<?php
/**
 * @var AppView $this
 * @var CodeWatcherProject $codeWatcherProject
 * @var array $activityDataLastMonth
 * @var array $activitySumLastMonth
 * @var array $activityDataThisMonth
 * @var array $activitySumThisMonth
 * @var array $activityDataRawThisMonth
 * @var array $activityDataRawLastMonth
 */

use App\Model\Entity\CodeWatcherProject;
use App\View\AppView;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('View Code Watcher Project'));
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
    <?= $this->Html->link(__('&larr; Back to Code Watcher Projects'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>

<div class="container-fluid px-4">
    <div class="card">

        <div class="card-header">
            <?= h($codeWatcherProject->name) ?>
            <?= $this->Html->link('<i class="fas fa-edit"></i>' . __('&nbsp;Edit Code Watcher Project'), ['action' => 'edit', $codeWatcherProject->id],
                ['class' => 'btn btn-secondary btn-sm float-end', 'escape' => false]) ?>
        </div>

        <div class="card-body">
            <div class="codeWatcherProjects view content">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($codeWatcherProject->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Description') ?></th>
                            <td><?= h($codeWatcherProject->description) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('ID') ?></th>
                            <td><?= $this->Number->format($codeWatcherProject->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($codeWatcherProject->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($codeWatcherProject->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Activation') ?></th>
                            <td><?= h($codeWatcherProject->activation) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expiration') ?></th>
                            <td><?= h($codeWatcherProject->expiration) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Auto Delete') ?></th>
                            <td><?= $codeWatcherProject->auto_delete ? __('Yes') : __('No'); ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Enable Tracking') ?></th>
                            <td><?= $codeWatcherProject->enable_tracking ? __('Yes') : __('No'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('Related Code Watcher Folders') ?>
        </div>

        <div class="card-body">
            <div class="codeWatcherProjects index content">
                <?php if (!empty($codeWatcherProject->code_watcher_folders)) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th><?= __('Folder ID') ?></th>
                                <th><?= __('Activation') ?></th>
                                <th><?= __('Expiration') ?></th>
                                <th><?= __('Auto Delete') ?></th>
                                <th><?= __('Base Path') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                            <?php foreach ($codeWatcherProject->code_watcher_folders as $codeWatcherFolders) : ?>
                                <tr>
                                    <td><?= h($codeWatcherFolders->id) ?></td>
                                    <td><?= h($codeWatcherFolders->activation) ?></td>
                                    <td><?= h($codeWatcherFolders->expiration) ?></td>
                                    <td><?= h($codeWatcherFolders->auto_delete) ?></td>
                                    <td><?= h($codeWatcherFolders->base_path) ?></td>
                                    <td class="actions">
                                        <?= $this->Form->postLink(__('Delete'), ['controller' => 'CodeWatcherFolders', 'action' => 'delete', $codeWatcherFolders->id], ['confirm' => __('Are you sure you want to delete # {0}?', $codeWatcherFolders->id)]) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no Code Watcher Folders found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<div class="container-fluid px-4 mt-5">
    <div class="card coding-activity">

        <div class="card-header">
            <?= __('Coding Activity Summary') ?>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-sm-12 col-lg-6">
                    <?php
                    $text = '';
                    $timeHours = intval(floor($activitySumLastMonth / 60));
                    if ($timeHours === 1) {
                        $text .= __("{0} Hour ", $timeHours);
                    } else {
                        $text .= __("{0} Hours ", $timeHours);
                    }

                    $timeMins = intval($activitySumLastMonth % 60);
                    if ($timeMins >= 1) {
                        $text .= __("& {0} Minutes ", $timeMins);
                    }
                    ?>
                    <h4>Last Month - <?= $text ?></h4>
                    <canvas id="timeChartLastMonth"></canvas>
                </div>
                <div class="col-sm-12 col-lg-6">
                    <?php
                    $text = '';
                    $timeHours = intval(floor($activitySumThisMonth / 60));
                    if ($timeHours === 1) {
                        $text .= __("{0} Hour ", $timeHours);
                    } else {
                        $text .= __("{0} Hours ", $timeHours);
                    }

                    $timeMins = intval($activitySumThisMonth % 60);
                    if ($timeMins >= 1) {
                        $text .= __("& {0} Minutes ", $timeMins);
                    }
                    ?>
                    <h4>This Month - <?= $text ?></h4>
                    <canvas id="timeChartThisMonth"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$this->start('viewCustomScripts');
?>

<?php
echo $this->Html->script('https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js');
?>

<script>
    const dataLast = <?= $activityDataLastMonth ?>;
    const labelsLast = Object.keys(dataLast); // Dates
    const timesLast = Object.values(dataLast).map(entry => entry.time); // Time values
    const ctxLast = document.getElementById('timeChartLastMonth').getContext('2d');
    new Chart(ctxLast, {
        type: 'bar', // Line chart
        data: {
            labels: labelsLast,
            datasets: [{
                label: 'Time (minutes)',
                data: timesLast,
                backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
                borderColor: 'rgba(75, 192, 192, 1)',       // Bar border color
                borderWidth: 1                             // Bar border width
            }]
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Date'
                    },
                    ticks: {
                        min: 0,
                    }
                }],
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Time (mins)'
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 480,
                        stepSize: 60
                    }
                }],
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });

    const dataThis = <?= $activityDataThisMonth ?>;
    const labelsThis = Object.keys(dataThis); // Dates
    const timesThis = Object.values(dataThis).map(entry => entry.time); // Time values
    const ctxThis = document.getElementById('timeChartThisMonth').getContext('2d');
    new Chart(ctxThis, {
        type: 'bar', // Line chart
        data: {
            labels: labelsThis,
            datasets: [{
                label: 'Time (minutes)',
                data: timesThis,
                backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
                borderColor: 'rgba(75, 192, 192, 1)',       // Bar border color
                borderWidth: 1                             // Bar border width
            }]
        },
        options: {
            responsive: true,
            scales: {
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Date'
                    },
                    ticks: {
                        min: 0,
                    }
                }],
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Time (mins)'
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 480,
                        stepSize: 60
                    }
                }],
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        }
    });
</script>

<?php
$this->end();
?>


<style>
    .text-white {
        color: white;
        background-color: white;
    }

    .text-green {
        color: green;
        background-color: rgba(75, 192, 192, 0.6);
        border-top: 2px solid white;
        border-bottom: 2px solid white;
    }

    .time {
        min-height: 24px;
    }

    .border-rhs-remove:not(:last-child) {
        border-right-width: 0 !important;
    }
</style>

<div class="container-fluid px-4 mt-5">
    <div class="card coding-activity">
        <div class="card-header">
            <?= __('Coding Activity This Month') ?>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-1">
                    <code class="text-dark me-2">DATE/TIME</code>
                </div>

                <div class="col-11">
                    <div class="container-flex">
                        <div class="d-flex">
                            <?php
                            $hours = [
                                '12am',
                                '01am',
                                '02am',
                                '03am',
                                '04am',
                                '05am',
                                '06am',
                                '07am',
                                '08am',
                                '09am',
                                '10am',
                                '11am',
                                '12pm',
                                '01pm',
                                '02pm',
                                '03pm',
                                '04pm',
                                '05pm',
                                '06pm',
                                '07pm',
                                '08pm',
                                '09pm',
                                '10pm',
                                '11pm',
                            ];
                            foreach ($hours as $hour) {
                                ?>
                                <div class="flex-grow-1 text-center border border-rhs-remove">
                                    <code class="text-dark"><?= $hour ?></code>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-12">
                    <?php
                    $start = (new DateTime())->setTimezone(LCL_TZ)->startOfMonth();
                    $end = (new DateTime())->setTimezone(LCL_TZ)->endOfMonth();
                    $rollingDate = (clone $start);
                    $counter = 0;
                    $codingBlock = [];
                    while ($rollingDate->lessThanOrEquals($end) && $counter <= 9000) {

                        $rollingDate = $rollingDate->startOfDay();

                        $hours = [
                            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
                            '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
                            '20', '21', '22', '23'
                        ];
                        $minutes = ['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'];

                        foreach ($hours as $hour) {
                            foreach ($minutes as $minute) {
                                $dateBlock = $rollingDate->format("Y") . "-" . $rollingDate->format("m") . "-" . $rollingDate->format("d");
                                $hourBlock = "{$hour}";
                                $minuteBlock = "{$hour}-{$minute}-00";
                                $dateTimeBlock = "{$dateBlock}-{$minuteBlock}";

                                if (in_array($dateTimeBlock, $activityDataRawThisMonth)) {
                                    $codingBlock[$dateBlock][$hourBlock][$minuteBlock] = true;
                                } else {
                                    $codingBlock[$dateBlock][$hourBlock][$minuteBlock] = false;
                                }
                            }
                        }
                        $rollingDate = $rollingDate->addDays(1);
                        $counter++;
                    }
                    ?>

                    <?php
                    foreach ($codingBlock as $d => $codingBlockDate) {
                        ?>
                        <div class="row">
                            <div class="col-1">
                                <code class="text-dark me-2"><?= $d ?></code>
                            </div>
                            <div class="col-11">
                                <div class="container-flex date date-<?= $d ?>">
                                    <div class="d-flex">
                                        <?php
                                        foreach ($codingBlockDate as $t => $codingBlockTime) {
                                            ?>
                                            <div class="flex-grow-1 border border-rhs-remove border-top-0">
                                                <div class="d-flex">
                                                    <?php
                                                    foreach ($codingBlockTime as $m => $codingBlockMinute) {
                                                        if ($codingBlockMinute) {
                                                            $textColour = 'green';
                                                        } else {
                                                            $textColour = 'white';
                                                        }

                                                        $cssText = "flex-grow-1 text-{$textColour} time time-{$m}"
                                                        ?>

                                                        <div class="<?= $cssText ?>"></div>

                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>


