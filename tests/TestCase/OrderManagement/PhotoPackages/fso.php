<?php
require __DIR__ . '/../config/paths.php';
require __DIR__ . '/../vendor/autoload.php';

use League\Flysystem;


//$in = "M:\\AcademyPhotography\\PackageSamples\\";
//$out = "M:\\AcademyPhotography\\PackageSamplesX\\";
//$s = microtime(true);
//rename($in, $out);
//rename($out, $in);
//$e = microtime(true);
//dump($e - $s);



//----file creation speed tests -------------------------------------------
//$base = "M:\\RandomFolderStruture\\";
//$case = "test_" . mt_rand(11, 99) . "\\";
//$subs = range('a', 'e');
//
//$a = microtime(true);
//
//$counts = range(1, 10000);
//foreach ($counts as $count) {
//    $subDir = $subs;
//    shuffle($subDir);
//    $subDir = implode("\\", $subDir) . "\\";
//    $path = $base . $case . $subDir;
//    @mkdir($path, 0777, true);
//
//    $rnd = mt_rand(111, 999) . ".txt";
//    $path = $path . $rnd;
//    file_put_contents($path, sha1($path));
//}
//$b = microtime(true);



//----deletion speed tests -------------------------------------------
//$adapter = new League\Flysystem\Local\LocalFilesystemAdapter($base);
//$filesystem = new League\Flysystem\Filesystem($adapter);
//$filesystem->deleteDirectory($case);
//$c = microtime(true);
//
//dump($b - $a);
//dump($c - $b);


//--- dir listing speed tests
$a = microtime(true);
$cmd = 'dir "M:\RandomFolderStruture\test_97" /B /S ';
$out = [];
$ret = null;
exec($cmd, $out, $ret);
$b = microtime(true);
dump(count($out));
dump($b - $a);

$a = microtime(true);
$adapter = new League\Flysystem\Local\LocalFilesystemAdapter("M:\\RandomFolderStruture\\");
$filesystem = new League\Flysystem\Filesystem($adapter);
$sortedListing = $filesystem->listContents('test_97', true)
    ->sortByPath()
    ->toArray();
$b = microtime(true);
dump(count($sortedListing));
dump($b - $a);
