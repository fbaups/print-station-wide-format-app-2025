<?php
/**
 * @var AppView $this
 * @var Artifact $artifact
 * @var string[] $pdfMimeTypes
 * @var string[] $imageMimeTypes
 */

use App\Model\Entity\Artifact;
use App\View\AppView;
use arajcany\PrePressTricks\Utilities\Pages;
use arajcany\PrePressTricks\Utilities\PDFGeometry;

?>
<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('PDF Report') ?>
        </div>

        <div class="card-body">
            <div class="artifacts index content">
                <?php if (!empty($artifact->artifact_metadata)) : ?>
                    <div class="row">
                        <div class="col-12 col-lg-6">
                            <p class="mt-1 mb-1 fw-bolder">Document Properties</p>
                            <table class="table table-bordered table-sm">
                                <?php
                                foreach ($artifact->artifact_metadata->exif['doc'] as $key => $value) {
                                    ?>
                                    <tr>
                                        <th><?= $key ?></th>
                                        <td><?= $value ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>

                        <div class="col-12 col-lg-6">
                            <p class="mt-1 mb-1 fw-bolder">File Properties</p>
                            <table class="table table-bordered table-sm">
                                <?php
                                foreach ($artifact->artifact_metadata->exif['file'] as $key => $value) {
                                    ?>
                                    <tr>
                                        <th><?= ucwords($key) ?></th>
                                        <td><?= $value ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                    </div>

                    <?php
                    $Geo = new PDFGeometry();
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <p class="mt-1 mb-1 fw-bolder">
                                Page Report - <?= $artifact->artifact_metadata->exif['pages']['length'] ?>pp
                            </p>
                            <?php
                            $pagesByGeoHash = [];
                            foreach ($artifact->artifact_metadata->exif['pages']['page'] as $page) {
                                $pageGeoHash = sha1(serialize($page['geometry']));

                                if (!isset($pagesByGeoHash[$pageGeoHash])) {
                                    $pagesByGeoHash[$pageGeoHash] = [
                                        'pageRange' => [],
                                    ];
                                }

                                $pagesByGeoHash[$pageGeoHash] = [
                                    'pageRange' => array_merge($pagesByGeoHash[$pageGeoHash]['pageRange'], [$page['info']['pagenum']]),
                                    'geometry' => $page['geometry'],
                                ];
                            }
                            //dump($pagesByGeoHash);
                            ?>
                            <?php
                            if (count($pagesByGeoHash) < count($artifact->artifact_metadata->exif['pages']['page'])) {
                                echo '<p class="mt-1 mb-1">This is a condensed report as some pages have the same PDF geometry.</p>';
                            }
                            ?>
                            <table class="table table-bordered table-sm">
                                <thead>
                                <tr>
                                    <th>Page Number/s</th>
                                    <th>Trim Box</th>
                                    <th>Bleed Box</th>
                                    <th>Art Box</th>
                                    <th>Crop Box</th>
                                    <th>Media Box</th>
                                </tr>
                                </thead>
                                <?php
                                $Pages = new Pages();
                                foreach ($pagesByGeoHash as $page) {
                                    $compactedPageRange = $Pages->rangeCompact($page['pageRange'])
                                    ?>
                                    <tr>
                                        <td><?= $compactedPageRange ?></td>
                                        <td style="width: 17%">
                                            <?php
                                            $w = $Geo->convertUnit($page['geometry']['TrimBox']['width'], 'pt', 'mm', 0);
                                            $h = $Geo->convertUnit($page['geometry']['TrimBox']['height'], 'pt', 'mm', 0);

                                            if ($page['geometry']['TrimBox']['present']) {
                                                echo "{$w} x {$h}mm";
                                            } else {
                                                echo '<span class="text-muted fst-italic">Not Defined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="width: 17%">
                                            <?php
                                            $w = $Geo->convertUnit($page['geometry']['BleedBox']['width'], 'pt', 'mm', 0);
                                            $h = $Geo->convertUnit($page['geometry']['BleedBox']['height'], 'pt', 'mm', 0);

                                            if ($page['geometry']['BleedBox']['present']) {
                                                echo "{$w} x {$h}mm";
                                            } else {
                                                echo '<span class="text-muted fst-italic">Not Defined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="width: 17%">
                                            <?php
                                            $w = $Geo->convertUnit($page['geometry']['ArtBox']['width'], 'pt', 'mm', 0);
                                            $h = $Geo->convertUnit($page['geometry']['ArtBox']['height'], 'pt', 'mm', 0);

                                            if ($page['geometry']['ArtBox']['present']) {
                                                echo "{$w} x {$h}mm";
                                            } else {
                                                echo '<span class="text-muted fst-italic">Not Defined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="width: 17%">
                                            <?php
                                            $w = $Geo->convertUnit($page['geometry']['CropBox']['width'], 'pt', 'mm', 0);
                                            $h = $Geo->convertUnit($page['geometry']['CropBox']['height'], 'pt', 'mm', 0);

                                            if ($page['geometry']['CropBox']['present']) {
                                                echo "{$w} x {$h}mm";
                                            } else {
                                                echo '<span class="text-muted fst-italic">Not Defined</span>';
                                            }
                                            ?>
                                        </td>
                                        <td style="width: 17%">
                                            <?php
                                            $w = $Geo->convertUnit($page['geometry']['MediaBox']['width'], 'pt', 'mm', 0);
                                            $h = $Geo->convertUnit($page['geometry']['MediaBox']['height'], 'pt', 'mm', 0);

                                            if ($page['geometry']['MediaBox']['present']) {
                                                echo "{$w} x {$h}mm";
                                            } else {
                                                echo '<span class="text-muted fst-italic">Not Defined</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                    </div>

                    <div class="d-none">
                        <pre><?php json_encode($artifact->artifact_metadata->exif, JSON_PRETTY_PRINT) ?></pre>
                    </div>
                <?php else: ?>
                    <div>
                        <p class="mb-0"><?= __('Sorry, no PDF Report found.') ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>


<?php
$pp = intval($artifact->artifact_metadata->exif['pages']['length']);

if ($pp === 1) {
    return;
}

$plex = $plex ?? 2;

if (in_array($pp, [1, 2])) {
    $plex = 1;
}

if (empty($artifact->light_table_urls)) {
    $artifact->createLightTableImagesErrand();
    return;
}

if (!($artifact->doAllLightTableImagesExist())) {
    $artifact->createLightTableImagesErrand();
}

$spreads = [];

if ($plex === 2) {
    $spreads[] = null;
}

foreach ($artifact->artifact_metadata->exif['pages']['page'] as $page) {
    $spreads[] = $page;
}

if ($plex === 2) {
    if ($pp % 2 === 0) {
        $spreads[] = null;
    }
}

$spreads = array_chunk($spreads, $plex);
?>

<div class="container-fluid px-4 mt-5">
    <div class="card related">

        <div class="card-header">
            <?= __('PDF Spreads Viewer') ?>
        </div>

        <div class="card-body">
            <div class="artifacts index content">

                <style>
                    .spread {
                        background-color: #f8f9fa;
                        border: 1px solid #dee2e6;
                        margin: 10px 0;
                        display: flex;
                    }

                    .page {
                        flex: 1;
                        padding: 0;
                        margin: 0;
                        border-right: 1px solid #dee2e6;
                    }

                    .page:last-child {
                        border-right: none;
                    }
                </style>

                <div class="row">
                    <?php
                    $imageList = [];
                    foreach ($spreads as $spread) {
                        if ($plex === 1) {
                            $layoutClass = "col-6 col-md-4 col-lg-3 col-xl-2 col-xxl-1";
                        } else {
                            $layoutClass = "col-md-12 col-lg-6 col-xl-4 col-xxl-2";
                        }
                        ?>
                        <div class="<?= $layoutClass ?>">
                            <div class="spread text-center">
                                <?php

                                foreach ($spread as $page) {
                                    $pageNum = $page['info']['pagenum'] ?? false;

                                    if ($pageNum && isset($artifact->light_table_urls[$pageNum])) {
                                        $imgOpts = [
                                            'class' => 'img-fluid'
                                        ];
                                        $url = $artifact->light_table_urls[$pageNum];
                                        $image = $this->Html->image($url, $imgOpts);
                                    } else {
                                        $image = "";
                                    }
                                    $imageList[] = $image;

                                    ?>
                                    <div class="page">
                                        <div class="page-content">
                                            <div class="image-holder pt-2 ps-2 pe-2">
                                                <div class="page-image"><?= $image ?></div>
                                            </div>
                                            <div class="page-number "><?= $pageNum ?></div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <?php //dump($imageList); ?>
                </div>

            </div>
        </div>

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
        adjustHeights();

        // Re-adjust heights on window resize
        $(window).resize(function () {
            adjustHeights();
        });

        function adjustHeights() {
            var maxHeight = 0;

            //remove any inline heights
            $('.image-holder').css('height', '');

            // Find the tallest .image-holder
            $('.image-holder').each(function () {
                var height = $(this).height();
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            maxHeight = maxHeight + $('.page-number').first().height();

            // Set all .image-holder elements to the tallest height
            $('.image-holder').height(maxHeight);
        }
    });
</script>
<?php
$this->end();
?>
