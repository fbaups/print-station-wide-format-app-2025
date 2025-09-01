<?php

namespace App\Utility\Storage;

use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use Throwable;

class SftpInspector
{
    use ReturnAlerts;

    private array $inspectionReport;

    public function __construct()
    {
    }

    /**
     * Test connectivity to an sFTP Server.
     *
     * A successful connection will return a true result, however, one should be careful as
     * change directories CRUD operations may have failed.
     * The $this->sftpInspectionReport report should be read to confirm operations.
     *
     * Example $settings =
     * [
     * 'sftp_host' => 'sftp.example.com',
     * 'sftp_port' => 22,
     * 'sftp_username' => 'myUsername',
     * 'sftp_password' => 'myS3cr#tPass',
     * 'sftp_timeout' => 2,
     * 'sftp_path' => 'images',
     * 'http_host' = 'https://example.com'
     * ]
     *
     * If a http_host is passed in, a round trip write to sFTP and read from HTTP/S will be performed.
     * In the above example, if a file is placed in 'sftp.example.com/images/test_file.txt'
     * It should be able to be read back from 'https://example.com/test_file.txt'.
     * Note, the sftp_path IS NOT appended to the http_host URL.
     *
     * @param array $settings
     * @return bool
     */
    public function inspectSftpServer(array $settings = []): bool
    {
        $settings = array_merge($this->getDefaultSftpSettings(), $settings);

        $this->inspectionReport = [
            'connection' => null,
            'chdir_basePath' => null,
            'put_toBasePath' => null,
            'get_inBasePath' => null,
            'urlReadFileInBasePath' => null,
            'rename_fileInBasePath' => null,
            'delete_fileInBasePath' => null,
            'mkdir_subPath' => null,
            'chdir_toSubPath' => null,
            'put_toSubPath' => null,
            'urlReadFileInSubPath' => null,
            'get_inSubPath' => null,
            'rename_fileInSubPath' => null,
            'delete_fileInSubPath' => null,
            'delete_subPath' => null,
        ];

        //check for public/private keys
        if (!empty($settings['sftp_privateKey']) && !empty($settings['sftp_publicKey'])) {
            $username = $settings['sftp_username'];
            $password =  PublicKeyLoader::loadPrivateKey($settings['sftp_privateKey']);
        } else {
            $username = $settings['sftp_username'];
            $password =  $settings['sftp_password'];
        }

        try {
            $SFTP = new SFTP($settings['sftp_host'], $settings['sftp_port'], $settings['sftp_timeout']);
            if (!@$SFTP->login($username, $password)) {
                $this->addDangerAlerts(__("Could not login to the sFTP server {0}.", $settings['sftp_host']));
                $this->inspectionReport['connection'] = false;
                return false;
            }
            $this->addSuccessAlerts(__("Success, Logged into the sFTP server {0}.", $settings['sftp_host']));
            $this->inspectionReport['connection'] = true;
        } catch (Throwable $e) {
            $this->addWarningAlerts("sFTP Connection Error: {$e->getMessage()}");
            $this->inspectionReport['connection'] = false;
            return false;
        }

        $rnd = sha1(mt_rand(100000, 999999));
        $testFile = "test_{$rnd}.txt";
        $testContent = "Test content {$rnd}";
        $subDir = "test_subdir_{$rnd}";

        // Base path checks
        if (!$SFTP->chdir($settings['sftp_path'])) {
            $this->addWarningAlerts(__("Could not chdir to the path {0}.", $settings['sftp_path']));
            $this->inspectionReport['chdir_basePath'] = false;

            //return true as a connection was formed, we just could not do anything
            return true;
        }
        $this->addSuccessAlerts(__("Success, chdir to path {0}", $settings['sftp_path']));
        $this->inspectionReport['chdir_basePath'] = true;

        // Put test file to base path
        if ($SFTP->put($testFile, $testContent)) {
            $this->addSuccessAlerts(__("Success, put file to {0}", $testFile));
            $this->inspectionReport['put_toBasePath'] = true;

            $localFile = TMP . $testFile;
            if ($SFTP->get($testFile, $localFile)) {
                $this->addSuccessAlerts(__("Success, get file from {0}", $testFile));
                $this->inspectionReport['get_inBasePath'] = true;
            } else {
                $this->addWarningAlerts(__("Cannot get file from {0}", $testFile));
                $this->inspectionReport['get_inBasePath'] = false;
            }

            if (!empty($settings['http_host'])) {
                $url = TextFormatter::makeDirectoryTrailingForwardSlash($settings['http_host']) . $testFile;
                $httpCheck = @file_get_contents_guzzle($url);
                if ($httpCheck === $testContent) {
                    $this->addSuccessAlerts(__("Success, read file from URL {0}", $url));
                    $this->inspectionReport['urlReadFileInBasePath'] = true;
                } else {
                    $this->addWarningAlerts(__("Cannot read file from URL {0}", $url));
                    $this->inspectionReport['urlReadFileInBasePath'] = false;
                }
            }

            $renamed = "renamed_{$testFile}";
            if ($SFTP->rename($testFile, $renamed)) {
                $this->addSuccessAlerts(__("Success, rename file to {0}", $renamed));
                $this->inspectionReport['rename_fileInBasePath'] = true;
                $testFile = $renamed;
            } else {
                $this->addWarningAlerts(__("Cannot rename file to {0}", $renamed));
                $this->inspectionReport['rename_fileInBasePath'] = false;
            }


            if ($SFTP->delete($testFile)) {
                $this->addSuccessAlerts(__("Success, delete file {0}", $testFile));
                $this->inspectionReport['delete_fileInBasePath'] = true;
            } else {
                $this->addWarningAlerts(__("Cannot delete file {0}", $testFile));
                $this->inspectionReport['delete_fileInBasePath'] = false;
            }
        } else {
            $this->addWarningAlerts(__("Cannot put file to {0}", $testFile));
            $this->inspectionReport['put_toBasePath'] = false;
        }

        // Create and test subdir
        if ($SFTP->mkdir($subDir)) {
            $this->addSuccessAlerts("Success, mkdir {$subDir}.");
            $this->inspectionReport['mkdir_subPath'] = true;

            if ($SFTP->chdir($subDir)) {
                $this->addSuccessAlerts(__("Success, chdir to path {0}", $subDir));
                $this->inspectionReport['chdir_toSubPath'] = true;

                $subFile = "sub_{$testFile}";
                if ($SFTP->put($subFile, $testContent)) {
                    $this->addSuccessAlerts(__("Success, put file to {0}", $subFile));
                    $this->inspectionReport['put_toSubPath'] = true;

                    if (!empty($settings['http_host'])) {
                        $url = TextFormatter::makeDirectoryTrailingForwardSlash($settings['http_host']) . $subDir . '/' . $subFile;

                        $httpCheck = @file_get_contents_guzzle($url);
                        if ($httpCheck === $testContent) {
                            $this->addSuccessAlerts(__("Success, read file from URL {0}", $url));
                            $this->inspectionReport['urlReadFileInSubPath'] = true;
                        } else {
                            $this->addWarningAlerts(__("Cannot read file from URL {0}", $url));
                            $this->inspectionReport['urlReadFileInSubPath'] = false;
                        }
                    }

                    if ($SFTP->get($subFile, TMP . $subFile)) {
                        $this->addSuccessAlerts(__("Success, get file from {0}", $subFile));
                        $this->inspectionReport['get_inSubPath'] = true;
                    } else {
                        $this->addWarningAlerts(__("Cannot get file from {0}", $subFile));
                        $this->inspectionReport['get_inSubPath'] = false;
                    }

                    $renamed = "renamed_{$subFile}";
                    if ($SFTP->rename($subFile, $renamed)) {
                        $this->addSuccessAlerts(__("Success, rename file to {0}", $renamed));
                        $this->inspectionReport['rename_fileInSubPath'] = true;
                    } else {
                        $this->addWarningAlerts(__("Cannot rename file to {0}", $renamed));
                        $this->inspectionReport['rename_fileInSubPath'] = false;
                    }

                    if ($SFTP->delete($renamed)) {
                        $this->addSuccessAlerts(__("Success, delete file {0}", $renamed));
                        $this->inspectionReport['delete_fileInSubPath'] = true;
                    } else {
                        $this->addWarningAlerts(__("Cannot delete file {0}", $renamed));
                        $this->inspectionReport['delete_fileInSubPath'] = false;
                    }

                } else {
                    $this->addWarningAlerts(__("Cannot put file to {0}", $subFile));
                    $this->inspectionReport['put_toSubPath'] = false;
                }
            } else {
                $this->addWarningAlerts(__("Cannot change path to {0}", $subDir));
                $this->inspectionReport['chdir_toSubPath'] = false;
            }

            $SFTP->chdir('..');

            if ($SFTP->delete($subDir)) {
                $this->addSuccessAlerts(__("Success, delete directory {0}", $subDir));
                $this->inspectionReport['delete_subPath'] = true;
            } else {
                $this->addWarningAlerts(__("Cannot delete directory {0}", $subDir));
                $this->inspectionReport['delete_subPath'] = false;
            }
        } else {
            $this->addWarningAlerts("Could not mkdir {$subDir}.");
            $this->inspectionReport['mkdir_subPath'] = false;
        }

        return true;
    }

    public function getDefaultSftpSettings(): array
    {
        return [
            'sftp_host' => '',
            'sftp_port' => 22,
            'sftp_username' => '',
            'sftp_password' => '',
            'sftp_timeout' => 2,
            'sftp_path' => '',
            'sftp_privateKey' => '',
            'sftp_publicKey' => '',
        ];
    }

    public function getInspectionReport(): array
    {
        return $this->inspectionReport;
    }
}
