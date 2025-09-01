<?php
/**
 * @var AppView $this
 * @var MediaClip $mediaClip
 * @var Artifact $artifact
 */

use App\Model\Entity\Artifact;
use App\Model\Entity\MediaClip;
use App\View\AppView;
use Cake\Core\Configure;
use Cake\I18n\DateTime;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __('Edit Media Clip'));
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
$activation = (new DateTime())->second(0);
$months = intval(Configure::read("Settings.repo_purge"));
$expiration = (clone $activation)->addMonths($months)->second(0);

$templates = [
    'inputContainer' => '<div class="input settings {{type}}{{required}}">{{content}} <div class="mb-4 mb-md-0"><small class="form-text text-muted">{{help}}</small></div></div>',
];
?>

<?php
$this->append('backLink');
?>
<div class="p-0 m-1 float-end">
    <?= $this->Html->link(__('&larr; Back to Media Clips'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
</div>
<?php
$this->end();
?>
<div class="container-fluid px-4">
    <?= $this->Form->create($mediaClip) ?>
    <div class="card">

        <div class="card-header">
            <?= h($mediaClip->name) ?? "Media Clip Details" ?>
        </div>

        <div class="card-body">
            <div class="mediaClips form content">
                <fieldset>
                    <legend><?= __('') ?></legend>
                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    $nameControl = $this->Form->control('name', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'string',
                    ];
                    $descriptionControl = $this->Form->control('description', $opts);
                    ?>

                    <div class="row">
                        <div class="col-12 col-md-4"><?= $nameControl ?></div>
                        <div class="col-12 col-md-8"><?= $descriptionControl ?></div>
                    </div>

                    <?php
                    $this->Form->setTemplates($templates);
                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'datetime',
                        'empty' => true,
                        'default' => $activation,
                    ];
                    $activationControl = $this->Form->control('activation', $opts);

                    $expirationDefaultText = "Expiration <span class=\"small text-muted\">(default $months months)</span>";
                    $opts = [
                        'class' => 'form-control mb-0',
                        'label' => ['text' => $expirationDefaultText, 'escape' => false],
                        'data-type' => 'datetime',
                        'empty' => true,
                        'default' => $expiration,
                    ];
                    $expirationControl = $this->Form->control('expiration', $opts);
                    $this->Form->resetTemplates();

                    $opts = [
                        'class' => 'form-check-input mb-4',
                        'label' => ['class' => 'form-check-label mb-4', 'text' => __('Auto Delete on Expiration')],
                        'data-type' => 'boolean',
                        'default' => true,
                    ];
                    $this->Form->switchToCheckboxTemplate("mt-0 mt-md-4");
                    $autoDeleteControl = $this->Form->control('auto_delete', $opts);
                    $this->Form->switchBackTemplates();
                    ?>

                    <div class="row">
                        <div class="col-12 col-md-4"><?= $activationControl ?></div>
                        <div class="col-12 col-md-4"><?= $expirationControl ?></div>
                        <div class="col-12 col-md-4"><?= $autoDeleteControl ?></div>
                    </div>

                    <?php
                    if (!$artifact->isVideo($artifact) && !$artifact->isAudio($artifact)) {
                        $disableTrims = true;
                    } else {
                        $disableTrims = false;
                    }

                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => __('Trim Start <span class="small text-muted">(seconds)</span>'), 'escape' => false],
                        'data-type' => 'float',
                        'disabled' => $disableTrims,
                    ];
                    $trimStartControl = $this->Form->control('trim_start', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => __('Trim End <span class="small text-muted">(seconds)</span>'), 'escape' => false],
                        'data-type' => 'float',
                        'disabled' => $disableTrims,
                    ];
                    $trimEndControl = $this->Form->control('trim_end', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => __('Duration <span class="small text-muted">(seconds)</span>'), 'escape' => false],
                        'data-type' => 'float',
                        'data-duration-original' => $artifact->artifact_metadata->duration,
                    ];
                    $durationControl = $this->Form->control('duration', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'data-type' => 'integer',
                    ];
                    $rankControl = $this->Form->control('rank', $opts);
                    ?>

                    <div class="row">
                        <div class="col-12 col-md-3"><?= $trimStartControl ?></div>
                        <div class="col-12 col-md-3"><?= $trimEndControl ?></div>
                        <div class="col-12 col-md-3"><?= $durationControl ?></div>
                        <div class="col-12 col-md-3"><?= $rankControl ?></div>
                    </div>

                    <?php
                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => 'Fitting Options'],
                        'data-type' => 'string',
                        'type' => 'select',
                        'options' => ['fit' => 'Fit - Media my be letterboxed', 'fill' => 'Fill - Media may be cropped', 'stretch' => ' Stretch - Media may be distorted'],
                        'default' => 'fit',
                        'empty' => false
                    ];
                    $fittingControl = $this->Form->control('fitting', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => 'Muting Options'],
                        'data-type' => 'boolean',
                        'type' => 'select',
                        'options' => [1 => 'Muted on load', 0 => 'Unmuted on load'],
                        'default' => 0,
                        'empty' => false
                    ];
                    $muteControl = $this->Form->control('muted', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => 'Looping Options'],
                        'data-type' => 'boolean',
                        'type' => 'select',
                        'options' => [1 => 'Loop', 0 => 'Play Once'],
                        'default' => 0,
                        'empty' => false
                    ];
                    $loopControl = $this->Form->control('loop', $opts);

                    $opts = [
                        'class' => 'form-control mb-4',
                        'label' => ['text' => 'Autoplay Options'],
                        'data-type' => 'boolean',
                        'type' => 'select',
                        'options' => [1 => 'Play Automatically', 0 => 'User Initiated'],
                        'default' => 1,
                        'empty' => false
                    ];
                    $autoplayControl = $this->Form->control('autoplay', $opts);
                    ?>

                    <div class="row">
                        <div class="col-12 col-md-3"><?= $fittingControl ?></div>
                        <div class="col-12 col-md-3"><?= $muteControl ?></div>
                        <div class="col-12 col-md-3"><?= $loopControl ?></div>
                        <div class="col-12 col-md-3"><?= $autoplayControl ?></div>
                    </div>
                </fieldset>
            </div>
        </div>

        <div class="card-footer">
            <div class="float-end">
                <?php
                $options = [
                    'class' => 'link-secondary me-4'
                ];
                echo $this->Html->link(__('Back'), ['controller' => 'mediaClips'], $options);

                $options = [
                    'class' => 'btn btn-primary'
                ];
                echo $this->Form->button(__('Submit'), $options);
                ?>
            </div>
        </div>

    </div>
    <?= $this->Form->end() ?>
</div>


<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
if ($artifact->isVideo() || $artifact->isAudio()) {
    $this->append('viewCustomScripts');
    ?>
    <script>
        $(document).ready(function () {
            const $duration = $('#duration');
            const $trimStart = $('#trim-start');
            const $trimEnd = $('#trim-end');

            // Function to get the original duration (stored in the data attribute)
            function getOriginalDuration() {
                return parseFloat($duration.data('duration-original'));
            }

            // Format the number to respect the original decimal places
            function formatNumber(val, reference) {
                const referenceStr = reference.toString();
                const decimalPlaces = referenceStr.includes('.') ? referenceStr.split('.')[1].length : 0;
                const fixed = val.toFixed(decimalPlaces);
                return parseFloat(fixed) == parseInt(fixed) ? parseInt(fixed) : fixed;
            }

            // Enforce minimum duration and adjust the values
            function enforceMinimumDuration(start, end, original) {
                // Ensure no values fall below zero
                let trimStart = Math.max(0, start);
                let trimEnd = Math.max(0, end);

                // Calculate the duration
                let duration = original - trimStart - trimEnd;

                // Ensure the duration doesn't fall below 1 second
                if (duration < 1) {
                    duration = 1;
                    const maxTrim = original - duration;
                    trimStart = Math.max(0, maxTrim - trimEnd);
                    trimEnd = Math.max(0, maxTrim - trimStart);
                }

                // Return the adjusted values with proper formatting
                return {
                    trimStart: formatNumber(trimStart, original),
                    trimEnd: formatNumber(trimEnd, original),
                    duration: formatNumber(duration, original)
                };
            }

            // When duration changes
            $duration.on('change', function () {
                const original = getOriginalDuration();
                let durationVal = parseFloat($duration.val());

                if (isNaN(durationVal)) return;

                if (durationVal > original) {
                    // If duration exceeds original, reset both trim_start and trim_end to zero
                    $trimStart.val(0);
                    $trimEnd.val(0);
                    $duration.val(formatNumber(original, original));
                    return;
                }

                if (durationVal < 1) {
                    // Ensure the duration doesn't go below 1
                    durationVal = 1;
                }

                let trimEnd = parseFloat($trimEnd.val()) || 0;
                let trimStart = parseFloat($trimStart.val()) || 0;

                // Adjust trim_end first, if the duration is less than the original
                const maxTrimEnd = original - durationVal;
                if (trimStart > maxTrimEnd) {
                    trimStart = maxTrimEnd;
                }

                // Adjust trim_start accordingly
                trimEnd = original - durationVal - trimStart;

                const result = enforceMinimumDuration(trimStart, trimEnd, original);

                $trimStart.val(result.trimStart);
                $trimEnd.val(result.trimEnd);
                $duration.val(result.duration);
            });

            // When trim_start changes
            $trimStart.on('change', function () {
                const original = getOriginalDuration();
                let trimStartVal = parseFloat($trimStart.val()) || 0;
                let trimEndVal = parseFloat($trimEnd.val()) || 0;

                const result = enforceMinimumDuration(trimStartVal, trimEndVal, original);

                $trimStart.val(result.trimStart);
                $trimEnd.val(result.trimEnd);
                $duration.val(result.duration);
            });

            // When trim_end changes
            $trimEnd.on('change', function () {
                const original = getOriginalDuration();
                let trimStartVal = parseFloat($trimStart.val()) || 0;
                let trimEndVal = parseFloat($trimEnd.val()) || 0;

                const result = enforceMinimumDuration(trimStartVal, trimEndVal, original);

                $trimStart.val(result.trimStart);
                $trimEnd.val(result.trimEnd);
                $duration.val(result.duration);
            });
        });
    </script>
    <?php
    $this->end();
}
?>
