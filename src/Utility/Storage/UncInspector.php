<?php

namespace App\Utility\Storage;

use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;
use Throwable;

class UncInspector
{
    use ReturnAlerts;

    private array $inspectionReport;

    public function __construct()
    {
    }

    /**
     * Test connectivity to an UNC Server.
     *
     * A successful connection will return a true result, however, one should be careful as
     * change directories CRUD operations may have failed.
     * The $this->inspectionReport report should be read to confirm operations.
     *
     * Example $settings =
     * [
     * 'unc_host' => '\\192.168.0.185\',
     * 'unc_path' => 'web',
     * 'http_host' = 'https://example.com'
     * ]
     *
     * If a http_host is passed in, a round trip write to UNC and read from HTTP/S will be performed.
     * In the above example, if a file is placed in '\\192.168.0.185\web\test_file.txt'
     * It should be able to be read back from 'https://example.com/test_file.txt'.
     * Note, the unc_path IS NOT appended to the http_host URL.
     *
     * @param array $settings
     * @return bool
     */
    public function inspectUncServer(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultUncSettings(), $settings);

        $this->inspectionReport = [
            'connection' => null,
            'chdir_basePath' => null,
            'write_toBasePath' => null,
            'read_inBasePath' => null,
            'urlReadFileInBasePath' => null,
            'rename_fileInBasePath' => null,
            'delete_fileInBasePath' => null,
            'create_subPath' => null,
            'write_toSubPath' => null,
            'urlReadFileInSubPath' => null,
            'read_inSubPath' => null,
            'rename_fileInSubPath' => null,
            'delete_fileInSubPath' => null,
            'delete_subPath' => null,
        ];

        $fullUncPath = TextFormatter::makeDirectoryTrailingSmartSlash($settings['unc_host']) . $settings['unc_path'];

        try {
            if (!is_dir($fullUncPath)) {
                $this->addWarningAlerts("UNC path '{$fullUncPath}' is not reachable.");
                $this->inspectionReport['connection'] = false;
                return false;
            }
            $this->addSuccessAlerts("Success, connected to UNC path '{$fullUncPath}'.");
            $this->inspectionReport['connection'] = true;
        } catch (\Throwable $exception) {
            $this->addWarningAlerts("UNC path '{$fullUncPath}' is not reachable. " . $exception->getMessage());
            $this->inspectionReport['connection'] = false;
            return false;
        }


        $rnd = sha1(mt_rand(100000, 999999));
        $testFile = "test_{$rnd}.txt";
        $testContent = "Test content {$rnd}";
        $subDir = "test_subdir_{$rnd}";
        $subPath = $fullUncPath . DIRECTORY_SEPARATOR . $subDir;

        try {

            // Try to write file
            $testFilePath = $fullUncPath . DIRECTORY_SEPARATOR . $testFile;
            $writeResult = @file_put_contents($testFilePath, $testContent);
            $this->inspectionReport['write_toBasePath'] = $writeResult !== false;

            if ($writeResult === false) {
                $this->addWarningAlerts("Cannot write test file to '{$testFilePath}'.");
            } else {
                $this->addSuccessAlerts("Successfully wrote test file to '{$testFilePath}'.");

                $readContent = @file_get_contents_guzzle($testFilePath);
                $this->inspectionReport['read_inBasePath'] = $readContent === $testContent;

                if ($readContent === $testContent) {
                    $this->addSuccessAlerts("Successfully read back test file content from '{$testFilePath}'.");
                } else {
                    $this->addWarningAlerts("Could not read back matching content from '{$testFilePath}'.");
                }

                if (!empty($settings['http_host'])) {
                    $url = TextFormatter::makeDirectoryTrailingForwardSlash($settings['http_host']) . $testFile;
                    $httpCheck = @file_get_contents_guzzle($url);
                    $this->inspectionReport['urlReadFileInBasePath'] = $httpCheck === $testContent;

                    if ($httpCheck === $testContent) {
                        $this->addSuccessAlerts("Successfully read file from HTTP URL '{$url}'.");
                    } else {
                        $this->addWarningAlerts("Could not read file from HTTP URL '{$url}'.");
                        dd($url);
                    }
                }

                $renamedFilePath = $fullUncPath . DIRECTORY_SEPARATOR . "renamed_{$testFile}";
                $this->inspectionReport['rename_fileInBasePath'] = @rename($testFilePath, $renamedFilePath);

                if ($this->inspectionReport['rename_fileInBasePath']) {
                    $this->addSuccessAlerts("Successfully renamed test file to '{$renamedFilePath}'.");
                    $testFilePath = $renamedFilePath;
                } else {
                    $this->addWarningAlerts("Could not rename file to '{$renamedFilePath}'.");
                }

                $this->inspectionReport['delete_fileInBasePath'] = @unlink($testFilePath);

                if ($this->inspectionReport['delete_fileInBasePath']) {
                    $this->addSuccessAlerts("Successfully deleted file '{$testFilePath}'.");
                } else {
                    $this->addWarningAlerts("Could not delete file '{$testFilePath}'.");
                }
            }

            // Subdir tests
            $this->inspectionReport['create_subPath'] = @mkdir($subPath);
            if ($this->inspectionReport['create_subPath']) {
                $this->addSuccessAlerts("Successfully created subdir '{$subPath}'.");

                $subFilePath = $subPath . DIRECTORY_SEPARATOR . "sub_{$testFile}";
                $subWrite = @file_put_contents($subFilePath, $testContent);
                $this->inspectionReport['write_toSubPath'] = $subWrite !== false;

                if ($subWrite !== false) {
                    $this->addSuccessAlerts("Successfully wrote file to subdir '{$subFilePath}'.");
                } else {
                    $this->addWarningAlerts("Could not write file to subdir '{$subFilePath}'.");
                }

                if (!empty($settings['http_host'])) {
                    $url = TextFormatter::makeDirectoryTrailingForwardSlash($settings['http_host']) . $subDir . '/' . "sub_{$testFile}";
                    $httpCheck = @file_get_contents_guzzle($url);
                    $this->inspectionReport['urlReadFileInSubPath'] = $httpCheck === $testContent;

                    if ($httpCheck === $testContent) {
                        $this->addSuccessAlerts("Successfully read file from HTTP subdir URL '{$url}'.");
                    } else {
                        $this->addWarningAlerts("Could not read file from HTTP subdir URL '{$url}'.");
                    }
                }

                $subRead = @file_get_contents_guzzle($subFilePath);
                $this->inspectionReport['read_inSubPath'] = $subRead === $testContent;

                if ($subRead === $testContent) {
                    $this->addSuccessAlerts("Successfully read back file in subdir '{$subFilePath}'.");
                } else {
                    $this->addWarningAlerts("Could not read matching content from subdir '{$subFilePath}'.");
                }

                $renamedSubFile = $subPath . DIRECTORY_SEPARATOR . "renamed_sub_{$testFile}";
                $this->inspectionReport['rename_fileInSubPath'] = @rename($subFilePath, $renamedSubFile);

                if ($this->inspectionReport['rename_fileInSubPath']) {
                    $this->addSuccessAlerts("Successfully renamed file in subdir to '{$renamedSubFile}'.");
                } else {
                    $this->addWarningAlerts("Could not rename file in subdir to '{$renamedSubFile}'.");
                }

                $this->inspectionReport['delete_fileInSubPath'] = @unlink($renamedSubFile);
                if ($this->inspectionReport['delete_fileInSubPath']) {
                    $this->addSuccessAlerts("Successfully deleted file in subdir '{$renamedSubFile}'.");
                } else {
                    $this->addWarningAlerts("Could not delete file in subdir '{$renamedSubFile}'.");
                }

                $this->inspectionReport['delete_subPath'] = @rmdir($subPath);
                if ($this->inspectionReport['delete_subPath']) {
                    $this->addSuccessAlerts("Successfully deleted subdir '{$subPath}'.");
                } else {
                    $this->addWarningAlerts("Could not delete subdir '{$subPath}'.");
                }
            } else {
                $this->addWarningAlerts("Could not create subdir '{$subPath}'.");
            }
        } catch (Throwable $e) {
            $this->addDangerAlerts("UNC Inspection Error: {$e->getMessage()}");
            $this->inspectionReport['connection'] = false;
            return false;
        }

        return true;
    }

    public function getDefaultUncSettings(): array
    {
        return [
            'unc_host' => '',
            'unc_path' => '',
            'http_host' => '',
        ];
    }

    public function getInspectionReport(): array
    {
        return $this->inspectionReport;
    }
}
