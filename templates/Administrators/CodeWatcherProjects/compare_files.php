<?php
/**
 * @var AppView $this
 * @var CodeWatcherProject $leftProject
 * @var CodeWatcherProject $rightProject
 * @var string $leftFile
 * @var string $rightFile
 * @var string $leftFileFullPath
 * @var string $rightFileFullPath
 */

use App\Model\Entity\CodeWatcherProject;
use App\View\AppView;
use arajcany\ToolBox\Utility\Security\Security;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Compare Files in Project'));
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

<?php
echo $this->Html->css('diff-style');

$leftFile = Security::decrypt64Url($leftFile);
$rightFile = Security::decrypt64Url($rightFile);
$leftFileFullPath = Security::decrypt64Url($leftFileFullPath);
$rightFileFullPath = Security::decrypt64Url($rightFileFullPath);

//convenience name of file
$relativePath = $leftFile;

$a = @explode("\n", file_get_contents($leftFileFullPath));
$b = @explode("\n", file_get_contents($rightFileFullPath));

// Options for generating the diff
$options = array(
    //'ignoreWhitespace' => true,
    //'ignoreCase' => true,
);

// Initialize the diff class
$diff = new Diff($a, $b, $options);

$opsLeft = [
    'data' => [
        'leftProjectId' => $leftProject->id,
        'rightProjectId' => $rightProject->id,
        'leftProjectName' => $leftProject->name,
        'rightProjectName' => $rightProject->name,
        'leftFile' => $leftFileFullPath,
        'rightFile' => $rightFileFullPath,
        'direction' => 'left-to-right'
    ],
    'method' => 'post',
    'confirm' => __('Really Overwrite {0}?', $rightProject->name),
];
$leftToRightLink = $this->Form->postLink(__('Push {0} to {1} →', "Below", $rightProject->name),
    ['action' => 'push-file'],
    $opsLeft);

$opsRight = [
    'data' => [
        'leftProjectId' => $leftProject->id,
        'rightProjectId' => $rightProject->id,
        'leftProjectName' => $leftProject->name,
        'rightProjectName' => $rightProject->name,
        'leftFile' => $leftFileFullPath,
        'rightFile' => $rightFileFullPath,
        'direction' => 'right-to-left'
    ],
    'method' => 'post',
    'confirm' => __('Really Overwrite {0}?', $leftProject->name),
];
$rightToLeftLink = $this->Form->postLink(__('← Push {0} to {1}', "Below", $leftProject->name),
    ['action' => 'push-file'],
    $opsRight);
?>

<div class="container-fluid px-4">

    <div class="row">
        <div class="col-sm-12">
            <?php
            // Generate a side by side diff
            $renderer = new Diff_Renderer_Html_SideBySide;
            $tableHtml = $diff->Render($renderer);

            $newLeftHeading = $leftProject->name . " Version (" . $leftToRightLink . ")";
            $newRightHeading = $rightProject->name . " Version (" . $rightToLeftLink . ")";

            $tableHtml = str_replace("Old Version", $newLeftHeading, $tableHtml);
            $tableHtml = str_replace("New Version", $newRightHeading, $tableHtml);
            ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><?= __('Side by Side Diff of "{0}"', $relativePath) ?></h3>
                </div>
                <div class="card-body">
                    <?php
                    if (strlen($tableHtml) > 0) {
                        echo $tableHtml;
                    } else {
                        echo '<div class="alert alert-info">The files are the same!</div>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

</div>

