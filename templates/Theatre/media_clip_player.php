<?php
/**
 * @var AppView $this
 * @var \App\Model\Entity\MediaClip[]|CollectionInterface $mediaClips
 * @var \App\Model\Entity\Artifact[]|CollectionInterface $artifacts
 *
 */

use App\View\AppView;
use Cake\Collection\CollectionInterface;
use Cake\Routing\Router;

foreach ($artifacts as $artifact) {
    $artifactsById[$artifact->id] = $artifact;
}

?>
<div class="container-fluid">

    <div id="control-data" class="d-none"></div>

    <div class="content">

        <div class="theatre-header">
            <h1><?= APP_NAME ?> Media Clips</h1>
        </div>

        <div class="video-gallery">
            <?php
            foreach ($mediaClips as $mediaClip) {
                $artifact = $artifactsById[$mediaClip->artifact_link] ?? null;
                if (!$artifact) {
                    continue;
                }

                if (str_starts_with($artifact->mime_type, "video/")) {
                    $mediaUrl = $artifact->full_url;
                } elseif (str_starts_with($artifact->mime_type, "image/")) {
                    $mediaUrl = $artifact->sample_url_mr;
                } else {
                    continue;
                }
                ?>
                <div class="gallery-item"
                     data-media-url="<?= $mediaUrl ?>"
                     data-media-name="<?= $mediaClip->name ?>"
                     data-media-description="<?= $mediaClip->description ?>"
                     data-mime="<?= $artifact->mime_type ?>">
                    <img src="<?= $artifact->sample_url_preview ?>" alt="<?= $mediaClip->description ?>"/>
                    <div class="gallery-item-caption">
                        <h2 class="text-white"><?= $mediaClip->name ?></h2>
                        <p class="text-white"><?= $mediaClip->description ?></p>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div class="login-button">
            <a href="<?= Router::url(['controller' => 'login',]) ?>">
                <i class="fa fa-user fa-lg"></i>
            </a>
        </div>

        <div class="exit-button">
            <a href="<?= Router::url(['controller' => 'theatre',]) ?>">
                <i class="fa fa-arrow-left fa-lg"></i>
            </a>
        </div>

    </div>

</div>


<div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaModalLabel">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <video class="media-video d-none" controls style="max-width: 100%; max-height: 100%;">
                    <source src="" type="">
                </video>
                <img class="media-image d-none img-fluid mx-auto d-block" src="" alt="" style="max-width: 100%; max-height: 100%;"/>
            </div>
        </div>
    </div>
</div>


<?php
$this->append('viewCustomScripts');
?>
<script>
    $(document).ready(function () {
        var currentMedia = null;

        $('.gallery-item').on('click', function () {
            var mediaUrl = $(this).attr('data-media-url');
            var mediaName = $(this).attr('data-media-name');
            var mediaDesc = $(this).attr('data-media-description');
            var mimeType = $(this).attr('data-mime');

            var $video = $('.media-video');
            var $videoSource = $video.find('source').first();
            var $image = $('.media-image');

            // Reset media
            if (currentMedia && typeof currentMedia.pause === 'function') {
                currentMedia.pause();
            }
            $video.addClass('d-none');
            $image.addClass('d-none');

            // Set modal title
            $('#mediaModalLabel').text(mediaName + ': ' + mediaDesc);

            if (mimeType && mimeType.startsWith('image/')) {
                // Display image
                $image.attr('src', mediaUrl)
                    .attr('alt', mediaName + " " + mediaDesc)
                    .removeClass('d-none');
                currentMedia = null;
            } else if (mimeType && mimeType.startsWith('video/')) {
                // Display video
                $videoSource.attr('src', mediaUrl);
                $videoSource.attr('type', mimeType);
                $video[0].load();
                $video.removeClass('d-none');
                $video[0].play();
                currentMedia = $video[0];
            }

            // Show the modal
            var modal = new bootstrap.Modal(document.getElementById('mediaModal'));
            modal.show();
        });

        document.getElementById('mediaModal').addEventListener('hidden.bs.modal', function () {
            if (currentMedia && typeof currentMedia.pause === 'function') {
                currentMedia.pause();
            }
        });
    });
</script>

<?php
$this->end();
?>

