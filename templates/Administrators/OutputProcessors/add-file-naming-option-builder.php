<?php
/**
 * @var AppView $this
 * @var OutputProcessor $outputProcessor
 * @var array $outputProcessorTypes
 * @var array $epsonPresets
 * @var array $epsonPresetsByUser
 */

use App\Model\Entity\OutputProcessor;
use App\View\AppView;
use Cake\I18n\DateTime;

?>
<div id="file-naming-option-builder"
     class="d-none file-naming-option row border mt-3 ms-0 me-0 p-2 pb-3">
    <div class="col-12">
        <h5 class="mb-2 pt-2">Custom File Name Builder</h5>
        <p class="text-muted mb-3">
            At minimum use
            <?= $this->Html->outputProcessorFileBuilderDefault() ?>
            to output as the original filename.
        </p>

        <div class="row">
            <div class="col-12">
                <?php
                $optsPrefix = [
                    //'label' => ['text' => 'Filename Builder', 'class' => 'form-check-label'],
                    'label' => false,
                    'class' => 'form-control mb-0',
                    'value' => !empty($outputProcessor->parameters['filenameBuilder']) ? $outputProcessor->parameters['filenameBuilder'] : '',
                    'data-type' => 'string',
                ];
                echo $this->Form->control('filename-builder', $optsPrefix);
                ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">

                <table class="table table-sm table-bordered small text-muted">
                    <thead>
                    <tr>
                        <th scope="col">Variable</th>
                        <th scope="col">Description</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <?= $this->Html->outputProcessorFileBuilderSpan('OrderID') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('JobID') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DocumentID') ?>
                        </td>
                        <td>
                            Numerical IDs assigned by <?= APP_NAME ?> when loaded into the Dashboard.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= $this->Html->outputProcessorFileBuilderSpan('OrderName') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('JobName') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DocumentName') ?>
                        </td>
                        <td>
                            Name of the Order/Job/Document in <?= APP_NAME ?>.
                            <br>
                            May have been assigned by the External System or an extract of the folder/filename on
                            import.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= $this->Html->outputProcessorFileBuilderSpan('OrderDescription') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('JobDescription') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DocumentDescription') ?>
                        </td>
                        <td>
                            Description of the Order/Job/Document in <?= APP_NAME ?>.
                            <br>
                            May have been assigned by the External System or an extract of the folder/filename on
                            import.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DocumentFileName') ?>
                            <br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DocumentFileExtension') ?>
                        </td>
                        <td>
                            The Document's filename without the extension.
                            <br>
                            The 3 letter extension of the Document's filename.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= $this->Html->outputProcessorFileBuilderSpan('ExternalOrderNumber') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('ExternalJobNumber') ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('ExternalDocumentNumber') ?>
                        </td>
                        <td>
                            Alphanumeric IDs assigned by the External System that generated the Order/Job/Document.
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= $this->Html->outputProcessorFileBuilderSpan('Counter') ?>
                            <br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('RandomNumber6') ?>
                            <br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('RandomString6') ?>
                            <br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('GUID') ?>
                            <br>
                        </td>
                        <td>
                            Padded counter (e.g. 001,002,003...) to allow easy file sorting.
                            <br>
                            Random 6 digit number. You can control the length by altering the 6.
                            <br>
                            Random 6 character alphanumeric string. You can control the length by altering the 6.
                            <br>
                            Random GUID.
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php
                            $currenDT = (new DateTime())->setTimezone(LCL_TZ);
                            ?>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DateStamp') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('TimeStamp') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DateTimeStamp') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('YearNumber') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('MonthNumber') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DayNumber') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('MonthName') ?><br>
                            <?= $this->Html->outputProcessorFileBuilderSpan('DayName') ?><br>
                        </td>
                        <td>
                            <?php
                            echo date("Y-m-d");
                            echo "<br>";
                            echo date("H:i:s");
                            echo "<br>";
                            echo $currenDT->format("Y-m-d-H-i-s-u");
                            echo "<br>";
                            echo $currenDT->format("Y");
                            echo "<br>";
                            echo $currenDT->format("m");
                            echo "<br>";
                            echo $currenDT->format("d");
                            echo "<br>";
                            echo $currenDT->format("F");
                            echo "<br>";
                            echo $currenDT->format("l");
                            ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
