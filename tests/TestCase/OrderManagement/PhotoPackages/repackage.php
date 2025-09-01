<?php
require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';

use arajcany\PhotoPackageAdapter\PrePackager\PrePackager;


$rp = new PrePackager();
$testZipA = 'M:\\AcademyPhotography\\FCIM_Pic_Pro_2_StTherese.zip';
$testExtractionA = 'M:\\AcademyPhotography\\test_extraction\\FCIM_Pic_Pro_2_StTherese\\';

$report = $rp->unzipPhotoPackage($testZipA, $testExtractionA);
//print_r($report);

$report = $rp->getImages($testExtractionA);
print_r($report);

$report = $rp->getControlFiles($testExtractionA);
print_r($report);






//$report = $rp->unzipPhotoPackage('M:\\AcademyPhotography\\Fuji_C8_StTherese.zip', 'M:\\AcademyPhotography\\test_extraction');
//print_r($report['package']);
//$report = $rp->unzipPhotoPackage('M:\\AcademyPhotography\\JDS_StTherese.zip', 'M:\\AcademyPhotography\\test_extraction');
//print_r($report['package']);

//$r = parse_ini_file(__DIR__ . '/tests/format_samples/Fujifilm_C8/condition.txt', true);
//print_r($r);
//$r = parse_ini_file(__DIR__ . '/tests/format_samples/FCIM_Pic_Pro_2/School_Job_2513.TXT', true);
//print_r($r);
//$r = parse_ini_file(__DIR__ . '/tests/format_samples/NeoPackProfessional_JDS/NeoPackProfessional - StThereseSch-S191020-1_4496.jds');
//print_r($r);
