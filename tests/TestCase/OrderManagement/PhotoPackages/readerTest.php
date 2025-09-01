<?php
require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('CUSTOMER_SAMPLES', ROOT . DS . 'CustomerSamples' . DS);

use arajcany\PhotoPackageAdapter\Adapters\PackageReader;
use arajcany\PhotoPackageAdapter\Adapters\PackageWriter;

$PackageReader = new PackageReader();
$PackageWriter = new PackageWriter();

$paths = [
//    'a' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\JDS\\JDS File\\PrintTime - Ind - ShalomColl-Q080222-1_7066.jds',
//    'b' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\JDS\\JDS File\\PrintTime - JPG-10x12-5Groups_9775.jds',
//    'c' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\JDS\\JDS File\\PrintTime - JPG-8x10-Groups_1492.jds',
//    'd' => 'M:\\AcademyPhotography\\PackageSamples\\JDS\\JDS\\NeoPackProfessional - StThereseSch-S191020-1_4496.jds',
//
//    'e' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\Fuji C8\\JPG-10x12-2003\\condition.txt',
//    'e' => 'W:\\arajcany_Projects\\PhotoPackageAdapter\\CustomerSamples\\Fujifilm_C8\\condition3.txt',
//    'f' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\Fuji C8\\JPG-8x102058\\condition.txt',
//    'g' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\Fuji C8\\TimestoneP2859\\condition.txt',
//    'h' => 'M:\\AcademyPhotography\\PackageSamples\\Fuji C8\\Fuji C8\\condition.txt',
//
//    'i' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\NTO\\NTO File\\RIP13746.nto',
//    'j' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\NTO\\NTO File\\RIP14489.nto',
//    'k' => 'M:\\AcademyPhotography\\PackageSamples\\Dylan Sent 20220506\\NTO\\NTO File\\RIP15290.nto',
    'k' => 'W:\\arajcany_Projects\\PhotoPackageAdapter\\CustomerSamples\\NTO\\Stripes.nto',
//
//    'l' => 'M:\\AcademyPhotography\\PackageSamples\\FCIM _ Pic Pro 2\\FCIM _ Pic Pro 2\\StTherese_2513.TXT',
//    'm' => 'M:\\AcademyPhotography\\PackageSamples\\FCIM _ Pic Pro 2\\MissingImagesSchool\\MissingImages_1234.TXT',

//    'n' => 'W:\arajcany_Projects\PhotoPackageAdapter\CustomerSamples\NTO\RIP12870.nto',
//    'n' => 'C:\\Users\\arajcany\\Desktop\\SampleTestOrder\\Ord_123_ABC.txt',
//    'n' => 'M:\\GenericRepository\\_seascape\\_Input\\Simple JPG Hotfolder\\condition.txt',
//    'o' => 'M:\\GenericRepository\\_seascape\\_Input\\Error_ApostropheIssue\\condition.txt',
//    'p' => 'W:\\arajcany_Projects\\PhotoPackageAdapter\\CustomerSamples\\SimpleFolderOfFiles\\',
];
//
////@mkdir(TMP . "_Output/");
//
$jobMakerMap = json_decode(file_get_contents(CONFIG . "printSizesMap_JobMaker.json"), JSON_OBJECT_AS_ARRAY);
$PackageWriter->setPrintSizesMap($jobMakerMap, 'jobmaker');

foreach ($paths as $k => $path) {

    $mf = $PackageReader->readToMasterFormat($path);
    $masterFormats = $mf->splitMasterFormatFullyExpanded();
    dd($masterFormats);
    //dump($mf->getImages_CommonBackprintText(['-'], true));
}


