<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';

use arajcany\PhotoPackageAdapter\Adapters\PackageReader;
use arajcany\PhotoPackageAdapter\Adapters\PackageWriter;
use arajcany\PhotoPackageAdapter\Names\Namer;

$PackageReader = new PackageReader();
$PackageWriter = new PackageWriter();
$Namer = new Namer();

//print_r($Namer->getFirstNames());
//print_r($Namer->getLastNames());
//print_r($Namer->isFirstName("Jack"));
//print_r($Namer->isFirstName("Reacher"));


$files = [
//    'W:\\arajcany_Projects\\PhotoPackageAdapter\\CustomerSamples\\Fujifilm_C8\\condition.txt',
//    'W:\\arajcany_Projects\\PhotoPackageAdapter\\CustomerSamples\\Fujifilm_C8\\condition2.txt',
//    'W:\\arajcany_Projects\\PhotoPackageAdapter\\CustomerSamples\\FCIM_Pic_Pro_2\\Hazel_4782.TXT',
    'W:\arajcany_Projects\PhotoPackageAdapter\CustomerSamples\NTO\RIP12870.nto'
];

$ts = microtime(true);
foreach ($files as $k => $filename) {
    $mf = $PackageReader->readToMasterFormat($filename);
    $mf->setImages_Humanise();
//    $mf->setImages_Sort('back_print_2');
}
$te = microtime(true);

print_r($te - $ts . "Seconds \r\n");


//dd($mf->getImages_Information()[1]);
//dd($mf->getImages_DelimitedBackprints());