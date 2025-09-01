<?php
require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';

define('CUSTOMER_SAMPLES', ROOT . DS . 'CustomerSamples' . DS);

use arajcany\PhotoPackageAdapter\Adapters\PackageReader;
use arajcany\PhotoPackageAdapter\Adapters\PackageWriter;

$PackageReader = new PackageReader();
$PackageWriter = new PackageWriter();

$files = [
    'a' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\JDS\\JDS File\\PrintTime - Ind - ShalomColl-Q080222-1_7066.jds',
    'b' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\JDS\\JDS File\\PrintTime - JPG-10x12-5Groups_9775.jds',
    'c' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\JDS\\JDS File\\PrintTime - JPG-8x10-Groups_1492.jds',
    'd' => 'M:\\AcademyPhotography\\PackageSamples\\JDS\\JDS\\NeoPackProfessional - StThereseSch-S191020-1_4496.jds',

    'e' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\Fuji C8\\JPG-10x12-2003\\condition.txt',
    'f' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\Fuji C8\\JPG-8x102058\\condition.txt',
    'g' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\Fuji C8\\TimestoneP2859\\condition.txt',
    'h' => 'M:\\AcademyPhotography\\PackageSamples\\Fuji C8\\Fuji C8\\condition.txt',

    'i' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\NTO\\NTO File\\RIP13746.nto',
    'j' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\NTO\\NTO File\\RIP14489.nto',
    'k' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\NTO\\NTO File\\RIP15290.nto',

    'l' => 'M:\\AcademyPhotography\\PackageSamples\\FCIM _ Pic Pro 2\\FCIM _ Pic Pro 2\\StTherese_2513.TXT',
    'm' => 'M:\\AcademyPhotography\\PackageSamples\\FCIM _ Pic Pro 2\\MissingImagesSchool\\MissingImages_1234.TXT',
];

@mkdir(TMP . "_Output/");

$printSizesUsed = [];

$jobMakerMap = json_decode(file_get_contents(CONFIG . "printSizesMap_JobMaker.json"), JSON_OBJECT_AS_ARRAY);
$PackageWriter->setPrintSizesMap($jobMakerMap, 'jobmaker');

foreach ($files as $k => $filename) {
    $mf = $PackageReader->readToMasterFormat($filename);

    $photoLabPath = pathinfo($filename, PATHINFO_DIRNAME) . "\\";
    $photoLabPath = str_replace("M:\\AcademyPhotography\\", "D:\_AndrewRajcanyPhotoSamples\\", $photoLabPath);

    $oid = $mf
        ->setOrder_ID($filename)
        ->setOrder_ImagePath($photoLabPath)
        ->getOrder_ID();

    $writeToPath = TMP . "_Output/{$k}_{$oid}.txt";
    $output = $PackageWriter->masterFormatToJobMaker($mf, $writeToPath);

    $printSizesUsed = array_merge($printSizesUsed, $mf->getMeta_PrintSizesInUse());
    $printSizesUsed = array_unique($printSizesUsed);
}

file_put_contents(TMP . "_Output/_printSizes.json", json_encode($printSizesUsed, JSON_PRETTY_PRINT));