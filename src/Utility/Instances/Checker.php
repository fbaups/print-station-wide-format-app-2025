<?php

namespace App\Utility\Instances;

use App\Utility\Feedback\ReturnAlerts;
use App\Utility\Network\NetworkConnection;
use App\Utility\Storage\SftpInspector;
use Cake\ORM\TableRegistry;
use phpseclib3\Net\SFTP;
use Throwable;

/**
 * Class Checker
 *
 * @package App\Utility\Installer
 *
 * @property \App\Model\Table\SettingsTable $Settings
 * @property \App\Model\Table\InternalOptionsTable $InternalOptions
 */
class Checker
{
    use ReturnAlerts;


    /**
     * DefaultApplication constructor.
     */
    public function __construct()
    {

    }


    /**
     * Check if the username is styled like a domain
     *  - user@company
     *  - company\user
     *
     * @param string $user
     * @return bool
     */
    public function checkNameIsDomainStyle(string $user = ''): bool
    {
        if (str_contains($user, "@") || str_contains($user, "\\")) {
            $isDomainUsername = true;
        } else {
            $isDomainUsername = false;
        }

        return $isDomainUsername;
    }


    /**
     * Check if the passed in $user is a valid Windows Administrator
     * There is no way to test a Username/Password combination
     *
     * @param string $user
     * @return bool
     */
    public function checkNameIsValidWindowsAdmin(string $user = ''): bool
    {
        $cmdCheckAdmin = "net user {$user} 2>&1";
        $userData = [];
        $ret = '';
        exec($cmdCheckAdmin, $userData, $ret);
        if ($ret == 0) {
            foreach ($userData as $dataChunk) {
                if (substr($dataChunk, 0, strlen('Local Group Memberships')) == 'Local Group Memberships') {
                    $localGroupMemberships = str_replace("Local Group Memberships", "", $dataChunk);
                    $localGroupMemberships = trim($localGroupMemberships, " *");
                    $localGroupMemberships = explode("*", $localGroupMemberships);

                    foreach ($localGroupMemberships as $localGroupMembership) {
                        $localGroupMembership = trim($localGroupMembership);

                        if ($localGroupMembership == 'Administrators') {
                            return true;
                        }
                    }
                    return false;
                }
            }
        } else {
            return false;
        }

        return false;
    }

}
