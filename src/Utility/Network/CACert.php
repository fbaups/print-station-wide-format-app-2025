<?php

namespace App\Utility\Network;

use App\Utility\Feedback\ReturnAlerts;
use Cake\I18n\DateTime;

class CACert
{
    use ReturnAlerts;

    private string $certPath;
    private string $certInfoPath;
    private int $expiryDays = 45;

    public function __construct()
    {
        $this->certPath = TMP . 'cacert.pem';
        $this->certInfoPath = TMP . 'cacert.info.json';

        $this->updateCertificateAuthorityPemFile();
    }

    /**
     * @return string
     */
    public function getCertPath(): string
    {
        if (is_file($this->certPath)) {
            return $this->certPath;
        } else {
            return false;
        }
    }

    /**
     * @return void
     */
    private function updateCertificateAuthorityPemFile(): void
    {
        $currentTimestamp = new DateTime();

        //force a download if info file or cacert.pem file is not present
        if (!is_file($this->certInfoPath) || !is_file($this->certPath)) {
            $this->addInfoAlerts("CA Cert file not present, will perform a download.");
            $this->downloadCertificateAuthorityPemFile();
        }

        //read info
        $caInfo = $this->readInfoFile();
        $caInfoTimestamp = new DateTime($caInfo['download_date']);
        $caInfoIsDownloadable = $caInfo['is_downloadable'];
        $diffInHours = $currentTimestamp->diffInHours($caInfoTimestamp);

        //check if not downloadable and more than 24hrs - perhaps it will work now
        if (!$caInfoIsDownloadable && $diffInHours >= 24) {
            $this->addInfoAlerts("CA Cert failed to download on a previous occasion, will try to download.");
            $this->downloadCertificateAuthorityPemFile();
        }

        //read updated info
        $caInfo = $this->readInfoFile();
        $caInfoTimestamp = new DateTime($caInfo['download_date']);
        $caInfoIsDownloadable = $caInfo['is_downloadable'];
        $diffInDays = $currentTimestamp->diffInDays($caInfoTimestamp);

        //check if older than expiry time and
        if ($caInfoIsDownloadable && $diffInDays >= $this->expiryDays) {
            $this->addInfoAlerts("CA Cert file is older than {$this->expiryDays} days, will perform a download.");
            $this->downloadCertificateAuthorityPemFile();
        } else {
            $this->addInfoAlerts("CA Cert file looks to be current.");
        }
    }

    /**
     * Downloads the cacert.pem file from CURL site and creates a parallel info file.
     *
     * @return array
     */
    private function downloadCertificateAuthorityPemFile(): array
    {
        $caUrl = 'https://curl.se/ca/cacert.pem';
        $options = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];
        $caData = @file_get_contents($caUrl, false, stream_context_create($options));
        if (!$caData) {
            return $this->writeFailInfoFile();
        }

        $writeResult = @file_put_contents($this->certPath, $caData);
        if (!$writeResult) {
            return $this->writeFailInfoFile();
        }

        $readBackData = file_get_contents($this->certPath);
        if ($caData !== $readBackData) {
            return $this->writeFailInfoFile();
        }

        return $this->writeSuccessInfoFile($caData);
    }

    private function readInfoFile(): false|array
    {
        if (!is_file($this->certInfoPath)) {
            return false;
        }

        $caInfo = json_decode(file_get_contents($this->certInfoPath), JSON_OBJECT_AS_ARRAY);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $caInfo;
    }

    /**
     * @param $caData
     * @return array
     */
    private function writeSuccessInfoFile($caData): array
    {
        //generate the success info file
        $info = [
            'is_downloadable' => true,
            'download_date' => time(),
            'download_date_human' => date("Y-m-d H:i:s"),
            'checksum' => sha1($caData),
        ];
        file_put_contents($this->certInfoPath, json_encode($info, JSON_PRETTY_PRINT));

        $this->addSuccessAlerts("CA Cert saved to {$this->certPath}.");

        return $info;
    }

    /**
     * @return array
     */
    private function writeFailInfoFile(): array
    {
        //generate the fail info file
        $info = [
            'is_downloadable' => false,
            'download_date' => time(),
            'download_date_human' => date("Y-m-d H:i:s"),
            'checksum' => false,
        ];
        file_put_contents($this->certInfoPath, json_encode($info, JSON_PRETTY_PRINT));

        $this->addDangerAlerts("CA Cert failed to save.");

        return $info;
    }


}
