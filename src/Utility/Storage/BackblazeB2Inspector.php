<?php

namespace App\Utility\Storage;

use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Network\CACert;
use arajcany\BackblazeB2Client\BackblazeB2\Client;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\Utility\Text;
use League\Flysystem\Filesystem;
use Throwable;
use Zaxbux\BackblazeB2\Object\Bucket\BucketInfo;
use Zaxbux\Flysystem\BackblazeB2Adapter;

class BackblazeB2Inspector
{
    use ReturnAlerts;

    private array $inspectionReport;
    private array $Flysystem = [];
    private array $validateKeyPairCache = [];

    public function __construct()
    {
    }

    /**
     * Test connectivity to a Backblaze B2 Bucket.
     *
     * A successful connection will return a true result, however, one should be careful as
     * change directories CRUD operations may have failed.
     * The $this->inspectionReport report should be read to confirm operations.
     *
     * Example $settings =
     * [
     * "b2_key_id" => '',
     * "b2_key" => '',
     * "b2_bucket" => '',
     * "b2_path" => 'images',
     * "http_host" => 'https://f004.backblazeb2.com/file/unique-bucket-name/images/',
     * ]
     *
     * If a http_host is passed in, a round trip write to B2 and read from HTTP/S will be performed.
     * In the above example, if a file is placed in the bucket 'images/test_file.txt'
     * It should be able to be read back from 'https://f004.backblazeb2.com/file/unique-bucket-name/images/test_file.txt'.
     * Note, the b2_path IS NOT appended to the http_host URL.
     *
     * @param array $settings
     * @return bool
     */
    public function inspectBackBlazeB2Server(array $settings = []): bool
    {
        $cacheKey = sha1(serialize($settings));

        $settings = array_merge($this->getDefaultB2Settings(), $settings);

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

        $b2KeyId = $settings['b2_key_id'];
        $b2Key = $settings['b2_key'];
        $b2Bucket = $settings['b2_bucket'];
        $b2Path = $this->cleanupPath($settings['b2_path']);
        $httpHost = TextFormatter::makeDirectoryTrailingForwardSlash($settings['http_host']);

        //BackblazeB2 Client options
        $config = [
            'applicationKeyId' => $b2KeyId,
            'applicationKey' => $b2Key,
            //'authorizationCache' => false, //uncomment to stop using the AuthorizationCache, but why would you?
        ];

        //Guzzle options
        $guzzleConfig = [
            'verify' => (new CACert())->getCertPath()
        ];
        try {
            $client = new Client($config, $guzzleConfig);
            $adapter = new BackblazeB2Adapter($client, $b2Bucket);
            $B2Filesystem = new Filesystem($adapter);

            $this->addSuccessAlerts(__("Success, connected to Bucket ID {0}.", $b2Bucket));
            $this->inspectionReport['connection'] = true;

            $this->Flysystem[$cacheKey] = $B2Filesystem;

            $rnd = substr(sha1(mt_rand(100000, 999999)), 0, 8);
            $testFile = "test_{$rnd}.txt";
            $testContent = "Test content {$rnd}";
            $subDir = "test_subdir_{$rnd}";

            $basePathFile = $b2Path . $testFile;
            $renamedFile = $b2Path . 'renamed_' . $testFile;
            $subDirPath = $b2Path . $subDir;
            $subFilePath = $subDirPath . '/' . $testFile;

            $urlBase = $httpHost . $testFile;
            $urlSubDir = $httpHost . $subDir . '/' . $testFile;

            //testing the base path
            if (!empty($b2Path)) {
                try {
                    $isB2PathExists = $B2Filesystem->directoryExists($b2Path);
                } catch (\Throwable $exception) {
                    $this->addWarningAlerts(__("Error checking base path {0}.", $b2Path));
                    $isB2PathExists = false;
                }

                if ($isB2PathExists) {
                    $this->addSuccessAlerts(__("Success, base path exists {0}.", $b2Path));
                    $this->inspectionReport['chdir_basePath'] = true;
                } else {
                    try {
                        $bzEmptyFile = "{$b2Path}.bzEmpty";
                        $bzEmptyFileContents = '';
                        $B2Filesystem->write($bzEmptyFile, $bzEmptyFileContents);
                        $this->addSuccessAlerts(__("Success, created base path {0}.", $bzEmptyFile));
                        $this->inspectionReport['chdir_basePath'] = true;
                        $this->inspectionReport['write_toBasePath'] = true;
                        $isB2PathExists = true;
                    } catch (Throwable $e) {
                        $this->addWarningAlerts(__("Cannot create base path {0}.", $basePathFile));
                        $this->inspectionReport['chdir_basePath'] = false;
                        $this->inspectionReport['write_toBasePath'] = false;
                        $isB2PathExists = false;
                    }
                }
            } else {
                $this->addSuccessAlerts(__("Base path is the bucket root."));
                $this->inspectionReport['chdir_basePath'] = true;
                $isB2PathExists = true;
            }

            //CRUD operations
            if ($isB2PathExists) {
                try {
                    $B2Filesystem->write($basePathFile, $testContent);
                    $this->addSuccessAlerts(__("Success, wrote file to {0}.", $basePathFile));
                    $this->inspectionReport['write_toBasePath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot write file to {0}.", $basePathFile));
                    $this->inspectionReport['write_toBasePath'] = false;
                }

                try {
                    $readContent = $B2Filesystem->read($basePathFile);
                    if ($readContent === $testContent) {
                        $this->addSuccessAlerts(__("Success, read file {0}.", $basePathFile));
                        $this->inspectionReport['read_inBasePath'] = true;
                    } else {
                        $this->addWarningAlerts(__("File contents does not match {0}.", $basePathFile));
                        $this->inspectionReport['read_inBasePath'] = false;
                    }
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot read file {0}.", $basePathFile));
                    $this->inspectionReport['read_inBasePath'] = false;
                }

                if (!empty($httpHost)) {
                    $httpContent = @file_get_contents_guzzle($urlBase);
                    if ($httpContent === $testContent) {
                        $this->addSuccessAlerts(__("Success, read file from URL {0}.", $urlBase));
                        $this->inspectionReport['urlReadFileInBasePath'] = true;
                    } else {
                        $this->addWarningAlerts(__("Cannot read file from URL {0}.", $urlBase));
                        $this->inspectionReport['urlReadFileInBasePath'] = false;
                    }
                }

                try {
                    $B2Filesystem->move($basePathFile, $renamedFile);
                    $this->addSuccessAlerts(__("Success, renamed file to {0}.", $renamedFile));
                    $this->inspectionReport['rename_fileInBasePath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot rename file to {0}.", $renamedFile));
                    $this->inspectionReport['rename_fileInBasePath'] = false;
                }

                try {
                    $B2Filesystem->delete($renamedFile);
                    $this->addSuccessAlerts(__("Success, deleted file {0}.", $renamedFile));
                    $this->inspectionReport['delete_fileInBasePath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot delete file {0}.", $renamedFile));
                    $this->inspectionReport['delete_fileInBasePath'] = false;
                }


                try {
                    $B2Filesystem->createDirectory($subDirPath);
                    $this->addSuccessAlerts(__("Success, created subdirectory {0}.", $subDirPath));
                    $this->inspectionReport['create_subPath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot create subdirectory {0}.", $subDirPath));
                    $this->inspectionReport['create_subPath'] = false;
                }


                try {
                    $B2Filesystem->write($subFilePath, $testContent);
                    $this->addSuccessAlerts(__("Success, wrote file to {0}.", $subFilePath));
                    $this->inspectionReport['write_toSubPath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot write file to {0}.", $subFilePath));
                    $this->inspectionReport['write_toSubPath'] = false;
                }

                if (!empty($httpHost)) {
                    $httpContent = @file_get_contents_guzzle($urlSubDir);
                    if ($httpContent === $testContent) {
                        $this->addSuccessAlerts(__("Success, read file from sub URL {0}.", $urlSubDir));
                        $this->inspectionReport['urlReadFileInSubPath'] = true;
                    } else {
                        $this->addWarningAlerts(__("Cannot read file from sub URL {0}.", $urlSubDir));
                        $this->inspectionReport['urlReadFileInSubPath'] = false;
                    }
                }

                try {
                    $readContent = $B2Filesystem->read($subFilePath);
                    if ($readContent === $testContent) {
                        $this->addSuccessAlerts(__("Success, read file {0}.", $subFilePath));
                        $this->inspectionReport['read_inSubPath'] = true;
                    } else {
                        $this->addWarningAlerts(__("Cannot read file {0}.", $subFilePath));
                        $this->inspectionReport['read_inSubPath'] = false;
                    }
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot read file {0}.", $subFilePath));
                    $this->inspectionReport['read_inSubPath'] = false;
                }

                $renamedSubFile = $subDirPath . '/renamed_' . $testFile;
                try {
                    $B2Filesystem->move($subFilePath, $renamedSubFile);
                    $this->addSuccessAlerts(__("Success, renamed file to {0}.", $renamedSubFile));
                    $this->inspectionReport['rename_fileInSubPath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot rename file to {0}.", $renamedSubFile));
                    $this->inspectionReport['rename_fileInSubPath'] = false;
                }

                try {
                    $B2Filesystem->delete($renamedSubFile);
                    $this->addSuccessAlerts(__("Success, deleted file {0}.", $renamedSubFile));
                    $this->inspectionReport['delete_fileInSubPath'] = true;
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot delete file {0}.", $renamedSubFile));
                    $this->inspectionReport['delete_fileInSubPath'] = false;
                }

                try {
                    $isSubDirPath = $B2Filesystem->directoryExists($subDirPath);
                    if ($isSubDirPath) {
                        $bzSubDirEmptyFile = "{$subDirPath}/.bzEmpty";
                        $isSubDirPathEmptyFile = $B2Filesystem->fileExists($bzSubDirEmptyFile);
                        if ($isSubDirPathEmptyFile) {
                            $B2Filesystem->delete($bzSubDirEmptyFile);
                            $this->addSuccessAlerts(__("Success, deleted subdirectory {0}.", $subDirPath));
                            $this->inspectionReport['delete_subPath'] = true;
                        }
                    } else {
                        $this->addWarningAlerts(__("Cannot locate subdirectory {0}.", $subDirPath));
                        $this->inspectionReport['delete_subPath'] = false;
                    }
                } catch (Throwable $e) {
                    $this->addWarningAlerts(__("Cannot delete subdirectory {0}.", $subDirPath));
                    $this->inspectionReport['delete_subPath'] = false;
                }
            }

        } catch (Throwable $exception) {
            $this->addWarningAlerts(__("Could not connect to Bucket ID {0}.", $b2Bucket));
            $this->inspectionReport['connection'] = false;
        }

        return $this->inspectionReport['connection'];
    }

    public function getDefaultB2Settings(): array
    {
        return [
            "b2_key_id" => '',
            "b2_key" => '',
            "b2_bucket" => '',
            "b2_path" => '',
            "http_host" => '',
        ];
    }

    public function getInspectionReport(): array
    {
        return $this->inspectionReport;
    }

    /**
     * Uses the B2 API to validate the key pair.
     *
     * Minimum $settings is a follows:
     * $settings = [
     *      "b2_key_id" => 'XXXXXXX',
     *      "b2_key" => 'YYYYYY',
     * ];
     *
     * If the key pair fails to validate, the following is returned:
     * (bool)false
     *
     * If the key pair validates and is locked to a single bucket, the following will be returned:
     * $validated = [
     *      "account_authorisation" => [...authorisation array from B2...],
     *       "bucket_list" => [], //empty list
     * ];
     *
     * If the key pair validates is valid across all buckets, the following will be returned:
     * $validated = [
     *       "account_authorisation" => [...authorisation array from B2...],
     *      "bucket_list" => [...buckets array from B2...],
     * ];
     * In such cases you will need to read the account_authorisation to extract one of the bucket names and properties
     *
     * @param array $settings
     * @param bool $forceRefresh
     * @return false|array
     */
    public function getAccountAuthorisation(array $settings = [], bool $forceRefresh = false): false|array
    {
        $b2KeyId = $settings['b2_key_id'] ?? null;
        $b2Key = $settings['b2_key'] ?? null;
        //$b2Path = $settings['b2_path'] ?? null;

        if (!$b2Key || !$b2KeyId) {
            $this->addDangerAlerts(__("B2 key pair not supplied."));
            return false;
        }

        $cacheKey = sha1($b2Key . $b2KeyId);
        if (isset($this->validateKeyPairCache[$cacheKey]) && $forceRefresh === false) {
            return $this->validateKeyPairCache[$cacheKey];
        }

        $settings = array_merge($this->getDefaultB2Settings(), $settings);

        $config = [
            'applicationKeyId' => $settings['b2_key_id'],
            'applicationKey' => $settings['b2_key'],
        ];

        //Guzzle options
        $guzzleConfig = [
            'verify' => (new CACert())->getCertPath()
        ];

        try {
            $B2Client = new Client($config, $guzzleConfig);
            $B2Client->refreshAccountAuthorization();
            $accountAuthorisation = $B2Client->accountAuthorization();
            $accountAuthorisationData = $accountAuthorisation->jsonSerialize();
            $this->addSuccessAlerts(__("Successfully authorised the key pair."));
        } catch (Throwable $exception) {
            $this->addDangerAlerts(__("Could not authorise the key pair."));
            return false;
        }

        try {
            $buckets = $B2Client->listBuckets();
            $this->addSuccessAlerts(__("Successfully listed the buckets."));
        } catch (Throwable $exception) {
            $buckets = [];
        }
        $bucketList = [];
        foreach ($buckets as $bucket) {
            $bucketList[$bucket->id()] = json_decode(json_encode($bucket), true);
        }

        $validated = [
            "account_authorisation" => $accountAuthorisationData,
            "bucket_list" => $bucketList,
        ];

        $this->validateKeyPairCache[$cacheKey] = $validated;

        return $validated;
    }

    /**
     * Standardise - remove starting slashes and add trailing slash
     *
     * @param string|null $path
     * @return string|null
     */
    public function cleanupPath(string|null $path = null): ?string
    {
        if ($path) {
            $path = ltrim($path, "\\/");
            $path = str_replace("\\", "/", $path);
            $path = TextFormatter::makeDirectoryTrailingForwardSlash($path);
        }

        if ($path === '/') {
            $path = '';
        }

        return $path;
    }

    /**
     * Get a Flysystem compatible Filesystem
     *
     * @param array $settings
     * @return Filesystem|false
     */
    public function getFlysystem(array $settings = []): Filesystem|false
    {
        $cacheKey = sha1(serialize($settings));

        if (isset($this->Flysystem[$cacheKey])) {
            return $this->Flysystem[$cacheKey];
        }

        $settings = array_merge($this->getDefaultB2Settings(), $settings);
        $b2KeyId = $settings['b2_key_id'];
        $b2Key = $settings['b2_key'];
        $b2Bucket = $settings['b2_bucket'];

        //BackblazeB2 Client options
        $config = [
            'applicationKeyId' => $b2KeyId,
            'applicationKey' => $b2Key,
            //'authorizationCache' => false, //uncomment to stop using the AuthorizationCache, but why would you?
        ];

        //Guzzle options
        $guzzleConfig = [
            'verify' => (new CACert())->getCertPath()
        ];

        try {
            $client = new Client($config, $guzzleConfig);
            $adapter = new BackblazeB2Adapter($client, $b2Bucket);
            $B2Filesystem = new Filesystem($adapter);
            if ($B2Filesystem) {
                $this->addSuccessAlerts(__("Created a Filesystem."));
                $this->Flysystem[$cacheKey] = $B2Filesystem;
            } else {
                $this->addDangerAlerts(__("Unable to create a Filesystem."));
            }
        } catch (Throwable $e) {
            $this->addDangerAlerts(__("Unable to create a Filesystem."));
        }

        return $this->Flysystem[$cacheKey];
    }
}
