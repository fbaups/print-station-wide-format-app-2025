<?php
/**
 * @var AppView $this
 * @var Artifact $artifact
 * @var string $pushUrl
 * @var string $convertToOrderUrl
 * @var int $reloadSeconds
 * @var Seed $seed
 */

use App\Model\Entity\Artifact;
use App\Model\Entity\Seed;
use App\View\AppView;
use Cake\Core\Configure;
use chillerlan\QRCode\QRCode;

$this->set('headerShow', true);
$this->set('headerIcon', __(""));
$this->set('headerTitle', __("", APP_NAME));
$this->set('headerSubTitle', __(""));


$qrCode = (new QRCode())->render($pushUrl);
?>
<style>
    .upload-container {
        margin: 0;
    }

    .dropzone {
        border: 2px dashed #3498db !important;
        border-radius: 5px;
        transition: 0.2s;
    }

    .dropzone.dz-drag-hover {
        border: 2px solid #3498db !important;
    }

    .dz-message.needsclick img {
        width: 50px;
        display: block;
        margin: auto;
        opacity: 0.6;
        margin-bottom: 15px;
    }

    span.plus {
        display: none;
    }

    .dropzone.dz-started .dz-message {
        display: inline-block !important;
        width: 120px;
        float: right;
        border: 1px solid rgba(238, 238, 238, 0.36);
        border-radius: 30px;
        height: 120px;
        margin: 16px;
        transition: 0.2s;
    }

    .dropzone.dz-started .dz-message span.text {
        display: none;
    }

    .dropzone.dz-started .dz-message span.plus {
        display: block;
        font-size: 70px;
        color: #AAA;
        line-height: 110px;
    }

    .drop-area {
        margin-bottom: 4rem;
    }

    .footer-margin {
        height: 100px;
    }

    .pointer {
        cursor: pointer;
    }

    .qr-code {
        max-width: 300px;
        min-height: 300px;
    }
</style>

<?php
$this->append('backLink');
?>
<?php
if ($this->AuthUser->hasAccess(['action' => 'index'])) {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Artifacts'), ['action' => 'index'], ['class' => '', 'escape' => false]) ?>
    </div>
    <?php
} else {
    ?>
    <div class="p-0 m-1 float-end">
        <?= $this->Html->link(__('&larr; Back to Home'), ['controller' => '/'], ['class' => '', 'escape' => false]) ?>
    </div>
    <?php
}
?>
<?php
$this->end();
?>

<div class="container-fluid qr-holder">
    <div class="row">
        <div class="col mb-5">
            <div class="qr-code ms-auto me-auto d-flex flex-grow-1 justify-content-center align-items-center">
                <img src="<?= $qrCode ?>" alt="Scan QR Code to upload">
            </div>
            <div class="ms-auto me-auto d-flex flex-grow-1 justify-content-center align-items-center mt-2">
                <div class="text-center">
                    <?= __('Scan this QR code to upload Artifacts into the Repository.'); ?>
                    <?= __('QR Code expires in <span class="reload-countdown">{0}</span>', $reloadSeconds); ?>
                </div>
            </div>
            <?php if (in_array(strtolower(Configure::read('mode')), ['dev', 'development'])) { ?>
                <div class="ms-auto me-auto d-flex flex-grow-1 justify-content-center align-items-center mt-2">
                    <p>
                        <?= $pushUrl ?>
                    </p>
                </div>
            <?php } ?>
            <div id="convert-link"
                 class="ms-auto me-auto d-flex flex-grow-1 justify-content-center align-items-center mt-2 d-none">
                <p>
                    <?= $this->Html->link("Convert to Order", $convertToOrderUrl) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid artifact-sample-holder w-75">
    <div class="row">

    </div>
</div>

<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        var reloadSeconds = <?= $reloadSeconds ?>;
        var reloadMilliSeconds = reloadSeconds * 1000;
        var seedToken = '<?= $seed->token ?>';

        hideQrCode();
        updateTimer();

        function hideQrCode() {
            setTimeout(function () {
                $('.qr-holder').hide();
            }, reloadMilliSeconds);
        }

        var intervalId = setInterval(function () {
            reloadSeconds -= 1;
            updateTimer();

            // Check if countdown reaches 0
            if (reloadSeconds <= 0) {
                window.location.reload(true);
            }
        }, 1000);

        function updateTimer() {
            var minutes = Math.floor(reloadSeconds / 60);
            var seconds = reloadSeconds % 60;
            var formattedTime = padNumber(minutes) + ':' + padNumber(seconds);
            $('.reload-countdown').text(formattedTime);
        }

        function padNumber(number) {
            return (number < 10 ? '0' : '') + number;
        }

        var getMetaGroupInfoID = setTimeout(getMetaGroupInfo, 5000);

        function getMetaGroupInfo() {
            var targetUrl = homeUrl + 'administrators/artifacts/meta-group/' + seedToken;
            var imageHolder = $('.artifact-sample-holder').find('.row');
            $.ajax({
                url: targetUrl,
                dataType: 'json',
                success: function (data) {
                    var imageCount = data.length;
                    var colCss;
                    if (imageCount <= 4) {
                        colCss = 'col-4';
                    } else if (imageCount >= 5 && imageCount <= 6) {
                        colCss = 'col-2';
                    } else {
                        colCss = 'col-2';
                    }
                    imageHolder.empty();
                    $.each(data, function (key, artifact) {
                        let imageUrl = artifact['full_url'];
                        let imageName = artifact['name'];
                        let imgElement = $(`<div class="${colCss} mt-4 mb-4 m-auto"><img class="img-fluid" alt="${imageName}" src="${imageUrl}"></div>`);
                        imageHolder.append(imgElement);
                    });

                    if (imageCount > 0) {
                        $('#convert-link').removeClass('d-none');
                    } else {
                        $('#convert-link').addClass('d-none');
                    }
                },
                error: function (xhr, status, error) {
                },
                complete: function (xhr, status) {
                },
                timeout: function () {
                }
            });

            var serviceInfoQueryId = setTimeout(getMetaGroupInfo, 5000);
        }

    });
</script>
<?php
$this->end();
?>

