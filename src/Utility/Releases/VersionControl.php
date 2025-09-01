<?php

namespace App\Utility\Releases;

use App\Model\Table\InternalOptionsTable;
use App\Model\Table\SettingsTable;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cake\Utility\Text;

/**
 * Class Version
 *
 * @property SettingsTable $Settings
 * @property InternalOptionsTable $InternalOptions
 *
 * @package App\Utility\Install
 */
class VersionControl
{
    public Table|InternalOptionsTable $InternalOptions;
    public Table|SettingsTable $Settings;

    public function __construct()
    {
        $this->InternalOptions = TableRegistry::getTableLocator()->get('InternalOptions');
        $this->Settings = TableRegistry::getTableLocator()->get('Settings');
    }

    /**
     * Full path and filename of the version.json file
     *
     * @return string
     */
    public function getVersionJsonFullPath()
    {
        return CONFIG . 'version.json';
    }

    /**
     * Full path and filename of the version.json file
     *
     * @return string
     */
    public function getVersionJsonFilename()
    {
        return pathinfo($this->getVersionJsonFullPath(), PATHINFO_FILENAME);
    }

    /**
     * Full path and filename of the version.json file
     *
     * @return string
     */
    public function getVersionHistoryJsonFullPath()
    {
        return CONFIG . 'version_history.json';
    }

    /**
     * Full path and filename of the version.json file
     *
     * @return string
     */
    public function getVersionHistoryJsonFilename()
    {
        return pathinfo($this->getVersionHistoryJsonFullPath(), PATHINFO_FILENAME);
    }

    /**
     * Return the contents of version.json in array format.
     * If version.json does not exist, default is created and returned.
     *
     * @return array
     */
    public function getVersionJson()
    {
        $fileToRead = $this->getVersionJsonFullPath();
        if (is_file($fileToRead)) {
            $versionData = json_decode(file_get_contents($fileToRead), JSON_OBJECT_AS_ARRAY);;
        } else {
            $versionData = $this->getDefaultVersionJson();
            $this->putVersionJson($versionData);
        }

        return $versionData;
    }

    /**
     * Write the version.json file
     *
     * @param array $data
     * @return bool
     */
    public function putVersionJson($data = [])
    {
        $fileToWrite = $this->getVersionJsonFullPath();
        return file_put_contents($fileToWrite, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Return the contents of version_history.json in array format.
     * If version_history.json does not exist, default is created and returned.
     *
     * @return array
     */
    public function getVersionHistoryJson()
    {
        $fileToRead = $this->getVersionHistoryJsonFullPath();
        if (is_file($fileToRead)) {
            $versionData = json_decode(file_get_contents($fileToRead), JSON_OBJECT_AS_ARRAY);;
        } else {
            $versionData = [$this->getDefaultVersionJson()];
            $this->putVersionHistoryJson($versionData);
        }

        return $versionData;
    }

    /**
     * Write the version_history.json file
     *
     * @param array $data
     * @return bool
     */
    public function putVersionHistoryJson($data = [])
    {
        $fileToWrite = $this->getVersionHistoryJsonFullPath();
        return file_put_contents($fileToWrite, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Return the contents of version_history.json in hashed TXT format.
     *
     * @return string
     */
    public function getVersionHistoryHashtxt()
    {
        $versionData = file_get_contents($this->getVersionHistoryJsonFullPath());
        $versionData = \arajcany\ToolBox\Utility\Security\Security::encrypt64Url($versionData);

        return $versionData;
    }

    /**
     * Format of the version.json
     *
     * @return array
     */
    public function getDefaultVersionJson()
    {
        return [
            'name' => APP_NAME,
            'tag' => '0.0.0',
            'desc' => APP_DESC,
            'codename' => '',
        ];
    }

    /**
     * Get the current version tag
     *
     * @return string
     */
    public function getCurrentVersionTag()
    {
        $version = $this->getVersionJson();
        return $version['tag'];
    }


    /**
     * Sort the Version History
     *
     * @param $unsorted
     * @return array
     */
    public function sortVersionHistoryArray($unsorted)
    {
        $keys = array_keys($unsorted);
        natsort($keys);
        $keys = array_reverse($keys);

        $sorted = [];
        foreach ($keys as $key) {
            $sorted[$key] = $unsorted[$key];
        }

        return $sorted;
    }


    /**
     * Increment a classic software version number
     *
     * @param string $number in the format xx.xx.xx
     * @param string $part the part to increment, major | minor | patch
     * @return string
     */
    public function incrementVersion($number, $part = 'patch')
    {
        $numberParts = explode('.', $number);

        if ($part == 'major') {
            $numberParts[0] += 1;
            $numberParts[1] = 0;
            $numberParts[2] = 0;
        }

        if ($part == 'minor') {
            $numberParts[1] += 1;
            $numberParts[2] = 0;
        }

        if ($part == 'patch') {
            $numberParts[2] += 1;
        }

        return implode('.', $numberParts);
    }


    /**
     * Get the versionHistory.json file in encrypted format and decrypt it.
     *
     * WARNING!
     * This function can only be used in PROD. This is because the $remote_update_url is retrieved from the DB.
     * The DB is only populated with this value when a release package is built.
     * i.e. During DEV, the remote URL is held in a JSON file and at the time of
     * packaging a release, the value is transferred into the DB.
     * Refer to App\Utility\Releases\RemoteUpdateServer class if you need this in DEV
     *
     * @return false|array
     */
    public function _getOnlineVersionHistoryHash()
    {
        $remote_update_url = $this->Settings->getRemoteUpdateUrl() . "version_history_hash.txt";

        $options = ['verify' => false];
        $versionHistoryHash = file_get_contents_guzzle($remote_update_url, $options);

        if ($versionHistoryHash) {
            $versionHistoryHash = Security::decrypt64Url($versionHistoryHash);
            $versionHistoryHash = @json_decode($versionHistoryHash, JSON_OBJECT_AS_ARRAY);
            if (is_array($versionHistoryHash)) {
                return $versionHistoryHash;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


}
