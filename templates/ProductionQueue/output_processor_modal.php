<?php
/**
 * @var AppView $this
 * @var array $typeMap
 * @var Entity|Order $orderData
 * @var Artifact[] $validArtifacts
 * @var array $outputProcessorTypes
 * @var array $outputProcessorsList
 */

use App\Model\Entity\Artifact;
use App\Model\Entity\Order;
use App\View\AppView;
use Cake\ORM\Entity;


$nameAndDesc = strtolower("{$orderData->name} {$orderData->description}");
$map = [];
foreach ($outputProcessorsList as $opKey => $opName) {
    if (str_contains(strtolower($opName), 'canvas')) {
        $map['canvas'] = $opKey;
    }

    if (str_contains(strtolower($opName), 'poster')) {
        $map['poster'] = $opKey;
    }
}

$defaultOption = null;
if (str_contains($nameAndDesc, 'canvas')) {
    if (isset($map['canvas'])) {
        $defaultOption = $map['canvas'];
    }
} elseif (str_contains($nameAndDesc, 'poster')) {
    if (isset($map['poster'])) {
        $defaultOption = $map['poster'];
    }
}
?>

<div class="order-output">
    <div class="row">
        <div class="col-12">
            <?php
            $jobCount = count($orderData->jobs);

            $documentCount = 0;
            foreach ($orderData->jobs as $job) {
                $documentCount += count($job->documents);
            }

            $validArtifactCount = count($validArtifacts);
            ?>
            <div class="card mb-4">
                <div class="card-header">
                    Order Information
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 mt-1 mb-1">Job Count</dt>
                        <dd class="col-sm-9 mt-1 mb-1">
                            <?= $jobCount ?>
                            <span class="show-all-jobs pointer text-muted">(Show All)</span>
                            <span class="hide-all-jobs pointer text-muted d-none">(Hide All)</span>
                        </dd>

                        <dt class="col-sm-3 mt-1 mb-1">File Count</dt>
                        <dd class="col-sm-9 mt-1 mb-1">
                            <?= $validArtifactCount ?>
                            <?php
                            if ($documentCount !== $validArtifactCount) {
                                $missing = $documentCount - $validArtifactCount;
                                echo "<span class='text-muted'>";
                                echo "({$missing} of the {$jobCount} files are missing)";
                                echo "</span>";
                            }
                            ?>
                        </dd>

                        <dt class="col-sm-3 mt-1 mb-1">Output Order</dt>
                        <dd class="col-sm-9 mt-1 mb-1">
                            <?php
                            if ($outputProcessorsList->count() === 0) {
                                echo "Sorry, there are no Output Processors defined.";
                            } elseif ($validArtifactCount > 0) {
                                $optionsSelect = [
                                    'label' => ['text' => 'Select Output Processor', 'class' => 'visually-hidden'],
                                    'type' => 'select',
                                    'empty' => false,
                                    'class' => 'form-control form-control-sm mb-0',
                                    'data-type' => 'string',
                                    'default' => $defaultOption,
                                    'options' => $outputProcessorsList
                                ];

                                $options = [
                                    'class' => 'btn btn-sm btn-primary output-order-send',
                                    'data-order-id' => $orderData->id,
                                ];
                                $fileDesc = ($validArtifactCount > 1) ? 'Files' : 'File';

                                ?>
                                <div class="row">
                                    <div class="col-auto">
                                        <?= $this->Form->button("Output {$validArtifactCount} {$fileDesc}", $options) ?>
                                    </div>
                                    <span class="col-auto pt-1 pb-1 ps-0 pe-0">to</span>
                                    <div class="col-auto">
                                        <?= $this->Form->control('Output Processor', $optionsSelect) ?>
                                    </div>

                                    <div id="output-spinner" class="col-auto pt-1 pb-1 ps-0 pe-0 d-none">
                                        <div class="spinner-grow spinner-grow-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-auto">
                                            <div id="output-success" class="col-auto pt-1 pb-1 ps-0 pe-0 d-none">
                                                <span class="text-success"><span
                                                        class="errand-count"></span> Errands have been created to process this request.</span>
                                            </div>

                                            <div id="output-warning" class="col-auto pt-1 pb-1 ps-0 pe-0 d-none">
                                                <span class="text-warning"><span
                                                        class="errand-count"></span> Errands have been created to process this request.</span>
                                            </div>

                                            <div id="output-error" class="col-auto pt-1 pb-1 ps-0 pe-0 d-none">
                                                <span class="text-danger">Sorry, could not process this request.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            } else {
                                echo "Sorry, there are no files to output.";
                            }
                            ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <?php
            $counter = 1;
            foreach ($orderData->jobs as $job) {
                if ($counter === $jobCount) {
                    $marginBottom = 'mb-0';
                } else {
                    $marginBottom = 'mb-4';
                }

                $missingInJob = 0;
                foreach ($job->documents as $document) {
                    if (!isset($validArtifacts[$document->artifact_token])) {
                        $missingInJob++;
                    }
                }
                $missingSpan = '';
                if ($missingInJob > 0) {
                    $missingSpan = '<span class="text-warning"><i class="fas fa-exclamation-circle"></i></span>';
                }
                ?>
                <div id="<?= "card-{$job->id}" ?>" class="card <?= $marginBottom ?>">
                    <div id="<?= "card-header-{$job->id}" ?>" class="card-header text-black-50 pt-2 pb-2">
                        <?= "Job #{$job->id}: {$job->name}" ?> <?= $missingSpan ?>
                        <div class="float-end body-collapse nav-link-icon">
                            <div class="pointer action-open d-inline-block">
                                <i class="fa-solid fa-chevron-up"></i>
                            </div>
                            <div class="pointer action-close d-inline-block d-none">
                                <i class="fa-solid fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                    <div id="<?= "card-body-{$job->id}" ?>" class="card-body pt-2 pb-2 d-none">
                        <p class="mb-0 d-none">Documents</p>
                        <ul class="mb-0">
                            <?php
                            foreach ($job->documents as $document) {
                                ?>
                                <li>
                                    <?= "Document #{$document->id}: {$document->name}" ?>
                                    <?php
                                    if (!isset($validArtifacts[$document->artifact_token])) {
                                        echo '<span class="text-warning">(File Missing)</span>';
                                    }
                                    ?>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <?php
                $counter++;
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12">


        </div>
    </div>
</div>


