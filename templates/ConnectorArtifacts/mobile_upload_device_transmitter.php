<?php
/**
 * @var AppView $this
 * @var Artifact $artifact
 * @var Seed $seed
 */

use App\Model\Entity\Artifact;
use App\Model\Entity\Seed;
use App\View\AppView;
use chillerlan\QRCode\QRCode;

//control what Libraries are loaded
$coreLib = [
    'dropzone' => true,
];
$this->set('coreLib', $coreLib);

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

    .qr-holder {
        max-width: 300px;
        min-height: 300px;
    }
</style>


<div class="container-fluid">

    <div class="mt-5 mb-5">
        <h1 class="text-center"><?= __('Mobile Upload') ?></h1>
    </div>

    <div id="dropzoneArtifactUpload" class="drop-area mt-5 mb-0">
        <?php
        $opts = [
            'class' => "dropzone needsclick",
            'id' => "artifact-upload",

        ];
        echo $this->Form->create(null, $opts);
        ?>
        <div class="dz-message needsclick text-center p-5">
                        <span class="text text-center">
                            <span>Drop files here or click to upload.</span>
                        </span>
            <span class="plus">+</span>
        </div>
        <?php
        $this->Form->end();
        ?>
    </div>

    <p class="text-center small">
        <?=
        __('Uploading closes in <span class="reload-countdown">{0}</span>', $seed->getTTL());
        ?>
    </p>


</div>

<?php
/**
 * Scripts in this section are output towards the end of the HTML file.
 */
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        var reloadSeconds = <?= $seed->getTTL() ?>;
        var reloadMilliSeconds = reloadSeconds * 1000;

        reloadPage();
        updateTimer();

        var dropzoneArtifactUpload = new Dropzone('#artifact-upload',
            {
                parallelUploads: 6,
                thumbnailHeight: 120,
                thumbnailWidth: 120,
                maxFilesize: 8,
                maxFiles: <?= $seed->bid_limit ?>,
                filesizeBase: 1000,
                addRemoveLinks: true,
                //acceptedFiles: ".jpeg,.jpg,.png,.gif",
                sending: function (file, xhr, formData) {
                    // Add extra parameters to the upload request
                    formData.append("uuid", file.upload.uuid);
                    formData.append("action", "upload");
                },
                thumbnail: function (file, dataUrl) {
                    if (file.previewElement) {
                        file.previewElement.classList.remove("dz-file-preview");
                        var images = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
                        for (var i = 0; i < images.length; i++) {
                            var thumbnailElement = images[i];
                            thumbnailElement.alt = file.name;
                            thumbnailElement.src = dataUrl;
                        }
                        setTimeout(function () {
                            file.previewElement.classList.add("dz-image-preview");
                        }, 1);
                    }
                },
                init: function () {
                    this.on("complete", function (file) {
                        //console.log("File added:", file);
                    });
                    this.on("removedfile", function (file) {
                        removeFile(file.upload.uuid)
                        //console.log("File removed:", file);
                    });
                }
            }
        );

        function removeFile(uuid) {
            var formData = new FormData();
            formData.append("action", "delete");
            var targetUrl = pageUrl + "/" + uuid;

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
                beforeSend: function () {
                },
                success: function (response) {
                    //console.log(response);
                },
                error: function (e) {
                    //console.log(e);
                },
                complete: function (e) {
                }
            });
        }

        function reloadPage() {
            setTimeout(function () {
                window.location.reload(true);
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
    });
</script>
<?php
$this->end();
?>
