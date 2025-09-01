<?php
/**
 * @var AppView $this
 * @var CodeWatcherProject $leftProject
 * @var CodeWatcherProject $rightProject
 * @var CodeWatcherFile[] $leftProjectFiles
 * @var CodeWatcherFile[] $rightProjectFiles
 */

use App\Model\Entity\CodeWatcherFile;
use App\Model\Entity\CodeWatcherProject;
use App\View\AppView;
use arajcany\ToolBox\Utility\Security\Security as Security;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Compare Projects'));
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
            Compare Projects
        </div>

        <div class="card-body">
            <div class="codeWatcherProjects view content">
                <div class="projects form large-9 medium-8 columns content">

                    <?php
                    $optsAll = [
                        'class' => 'btn btn-secondary mb-3 me-3',
                        'id' => 'filter-by-all',
                    ];
                    $optsSame = [
                        'class' => 'btn btn-secondary mb-3 me-3',
                        'id' => 'filter-by-same',
                    ];
                    $optsModified = [
                        'class' => 'btn btn-secondary mb-3 me-3',
                        'id' => 'filter-by-modified',
                    ];
                    $optsMissing = [
                        'class' => 'btn btn-secondary mb-3 me-3',
                        'id' => 'filter-by-missing',
                    ];

                    echo $this->Form->button('All', $optsAll);
                    echo $this->Form->button('Same', $optsSame);
                    echo $this->Form->button('Modified', $optsModified);
                    echo $this->Form->button('Missing', $optsMissing);
                    ?>

                    <?= $this->Form->create(null) ?>
                    <fieldset>
                        <?php
                        $combinedRelativePaths = [];

                        $leftProjectFilesMapped = [];
                        foreach ($leftProjectFiles as $leftProjectFile) {
                            $leftProjectFilesMapped[$leftProjectFile['file_path']] = $leftProjectFile;
                            $combinedRelativePaths[] = $leftProjectFile['file_path'];
                        }

                        $rightProjectFilesMapped = [];
                        foreach ($rightProjectFiles as $rightProjectFile) {
                            $rightProjectFilesMapped[$rightProjectFile['file_path']] = $rightProjectFile;
                            $combinedRelativePaths[] = $rightProjectFile['file_path'];
                        }

                        $combinedRelativePaths = array_unique($combinedRelativePaths);
                        natsort($combinedRelativePaths);

                        $differenceList = [];
                        foreach ($combinedRelativePaths as $relativePath) {
                            if (isset($leftProjectFilesMapped[$relativePath]) && isset($rightProjectFilesMapped[$relativePath])) {
                                if ($leftProjectFilesMapped[$relativePath]['sha1'] == $rightProjectFilesMapped[$relativePath]['sha1']) {
                                    $differenceList[$relativePath] = [
                                        'left' => 'same',
                                        'right' => 'same'
                                    ];
                                } else {
                                    $differenceList[$relativePath] = [
                                        'left' => 'modified',
                                        'right' => 'modified'
                                    ];
                                }
                            } else {
                                if (isset($leftProjectFilesMapped[$relativePath]) && !isset($rightProjectFilesMapped[$relativePath])) {
                                    $differenceList[$relativePath] = [
                                        'left' => 'present',
                                        'right' => 'missing'
                                    ];
                                } else {
                                    $differenceList[$relativePath] = [
                                        'left' => 'missing',
                                        'right' => 'present'
                                    ];
                                }
                            }

                            if (isset($leftProjectFilesMapped[$relativePath])) {
                                $differenceList[$relativePath]['left_file'] = $leftProjectFilesMapped[$relativePath];
                            }
                            if (isset($rightProjectFilesMapped[$relativePath])) {
                                $differenceList[$relativePath]['right_file'] = $rightProjectFilesMapped[$relativePath];
                            }

                            //add the grouping
                            $grouping = explode(DS, $relativePath);
                            array_pop($grouping);
                            $grouping = array_slice($grouping, 0, 3);
                            $grouping = implode(DS, $grouping);
                            $differenceList[$relativePath]['grouping'] = $grouping;
                        }
                        //dump($differenceList['Administrators/ApplicationLogs/index.php']);
                        ?>

                        <table class="table table-bordered table-sm table-hover">
                            <thead>
                            <tr>
                                <th>Relative File Path</th>
                                <th><?= $leftProject->name ?> Status</th>
                                <th><?= $rightProject->name ?> Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $counter = 1;
                            $previousGroup = '';
                            $currentGroup = '';
                            foreach ($differenceList as $path => $item) {
                                if (isset($item['left_file'])) {
                                    $leftFile = $item['left_file']['file_path'];
                                    $leftFullPath = "{$item['left_file']['base_path']}{$item['left_file']['file_path']}";
                                } else {
                                    $leftFile = '';
                                    $leftFullPath = '';
                                }

                                if (isset($item['right_file'])) {
                                    $rightFile = $item['right_file']['file_path'];
                                    $rightFullPath = "{$item['right_file']['base_path']}{$item['right_file']['file_path']}";
                                } else {
                                    $rightFile = '';
                                    $rightFullPath = '';
                                }

                                $currentGroup = $item['grouping'];

                                if ($item['left'] == 'same' || $item['right'] == 'same') {
                                    $fileClass = 'file-same';
                                } elseif ($item['left'] == 'modified' || $item['right'] == 'modified') {
                                    $fileClass = 'file-modified';
                                } elseif ($item['left'] == 'missing' || $item['right'] == 'missing') {
                                    $fileClass = 'file-missing';
                                } else {
                                    $fileClass = 'file-same';
                                }

                                if ($item['left'] == 'same') {
                                    $leftColour = 'green';
                                    $leftClass = 'file-same';
                                } elseif ($item['left'] == 'modified') {
                                    $leftColour = 'orange';
                                    $leftClass = 'file-modified';
                                } elseif ($item['left'] == 'present') {
                                    $leftColour = 'green';
                                    $leftClass = 'file-present';
                                } elseif ($item['left'] == 'missing') {
                                    $leftColour = 'red';
                                    $leftClass = 'file-missing';
                                } else {
                                    $leftColour = 'black';
                                    $leftClass = '';
                                }

                                if ($item['right'] == 'same') {
                                    $rightColour = 'green';
                                    $rightClass = 'file-same';
                                } elseif ($item['right'] == 'modified') {
                                    $rightColour = 'orange';
                                    $rightClass = 'file-modified';
                                } elseif ($item['right'] == 'present') {
                                    $rightColour = 'green';
                                    $rightClass = 'file-present';
                                } elseif ($item['right'] == 'missing') {
                                    $rightColour = 'red';
                                    $rightClass = 'file-missing';
                                } else {
                                    $rightColour = 'black';
                                    $rightClass = '';
                                }

                                ?>
                                <tr class="file <?= $fileClass ?>">
                                    <td style="color:black">
                                        <?php echo $path; ?>
                                    </td>
                                    <td style="color:<?= $leftColour; ?>" class="<?= $leftClass; ?>">
                                        <?php echo $item['left']; ?>
                                    </td>
                                    <td style="color:<?= $rightColour; ?>" class="<?= $rightClass; ?>">
                                        <?php echo $item['right']; ?>
                                    </td>
                                    <td style="color:black">
                                        <?php
                                        $url = [
                                            'action' => 'compare-files',
                                            $leftProject->id,
                                            $rightProject->id,
                                            Security::encrypt64Url($leftFile),
                                            Security::encrypt64Url($rightFile),
                                            Security::encrypt64Url($leftFullPath),
                                            Security::encrypt64Url($rightFullPath)
                                        ];
                                        $options = [
                                            'target' => '_blank',
                                        ];

                                        //diff viewer
                                        if ($item['left'] == 'modified' || $item['right'] == 'modified') {
                                            echo $this->Html->link('Diff', $url, $options);
                                        }


                                        ?>
                                    </td>
                                </tr>

                                <?php
                                if ($previousGroup != $currentGroup) {
                                    ?>
                                    <tr>
                                        <th>Relative File Path</th>
                                        <th><?= $leftProject->name ?> Status</th>
                                        <th><?= $rightProject->name ?> Status</th>
                                        <th>Actions</th>
                                    </tr>
                                    <?php
                                }

                                $previousGroup = $item['grouping'];
                                $counter++;
                                ?>
                            <?php } ?>
                            </tbody>
                        </table>

                    </fieldset>
                    <?php
                    $options = [
                        'class' => 'btn btn-primary'
                    ];
                    ?>
                    <?= $this->Form->button(__('Compare'), $options) ?>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>

    </div>
</div>


<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        $("#filter-by-all").on("click", function () {
            $('.file').removeClass('d-none');
        });

        $("#filter-by-same").on("click", function () {
            $('.file').removeClass('d-none').addClass('d-none');
            $('.file-same').removeClass('d-none');
        });

        $("#filter-by-modified").on("click", function () {
            $('.file').removeClass('d-none').addClass('d-none');
            $('.file-modified').removeClass('d-none');
        });

        $("#filter-by-missing").on("click", function () {
            $('.file').removeClass('d-none').addClass('d-none');
            $('.file-missing').removeClass('d-none');
        });
    });
</script>
<?php
$this->end();
?>

