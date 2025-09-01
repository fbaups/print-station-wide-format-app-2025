<?php

namespace App\HotFolderWorkflows;

use App\HotFolderWorkflows\Base\WorkflowBase;
use App\Model\Entity\HotFolderEntry;
use App\Model\Table\ArtifactsTable;
use App\Model\Table\ErrandsTable;
use App\OrderManagement\PhotoPackageOrdering;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;

/**
 * Example Workflow that can be copied and modified to suit.
 */
class ProcessPhotoPackageOrders extends WorkflowBase
{
    /**
     * Every Workflow class must have an execute() method.
     * This is automatically called when the Workflow is run.
     *
     * The Parent class has an execute() method to prevent failure.
     *
     * @param int|HotFolderEntry $hotFolderEntry ID or Hot Folder Entry Entity
     * @param array $options
     * @return bool
     */
    public function execute(int|HotFolderEntry $hotFolderEntry, array $options = []): bool
    {
        /** @var HotFolderEntry $hotFolderEntry */
        $hotFolderEntry = $this->HotFolderEntryTable->asEntity($hotFolderEntry);
        if (!$hotFolderEntry) {
            $this->setReturnValue(1);
            $this->addDangerAlerts(__("The supplied input is not a valid Hot Folder Entry. Exiting!"));
            return false;
        }

        //good idea to check that entry is a folder or file
        if (!is_dir($hotFolderEntry->path) && !is_file($hotFolderEntry->path)) {
            $this->setReturnValue(1);
            $this->addDangerAlerts(__("The supplied input is neither a File or Folder. Exiting!"));
            return false;
        }

        //check for necessary directory permissions
        if (!is_readable($hotFolderEntry->path) && !is_writeable($hotFolderEntry->path)) {
            $this->setReturnValue(1);
            $this->addDangerAlerts(__("Insufficient directory permissions. Exiting!"));
            return false;
        }

        $entryType = is_dir($hotFolderEntry->path) ? "Folder" : "File";


        $PhotoPackageOrdering = new PhotoPackageOrdering();
        /** @var ArtifactsTable $Artifacts */
        $Artifacts = TableRegistry::getTableLocator()->get('Artifacts');
        /** @var ErrandsTable $Errands */
        $Errands = TableRegistry::getTableLocator()->get('Errands');


        //do some action if this is a File
        if (is_file($hotFolderEntry->path)) {
            /*
             * Single image in the top folder is not allowed, move to a sub-folder and update $hotFolderEntry
             * The next if() will pick it up and process as simple folder of files
             */
            $dirName = TextFormatter::makeDirectoryTrailingSmartSlash((pathinfo($hotFolderEntry->path, PATHINFO_DIRNAME)));
            $baseName = pathinfo($hotFolderEntry->path, PATHINFO_BASENAME);
            $fileName = pathinfo($hotFolderEntry->path, PATHINFO_FILENAME);
            $newFolderGuid = $fileName . " " . microtimestamp();
            $dirNameNew = TextFormatter::makeDirectoryTrailingSmartSlash("{$dirName}{$newFolderGuid}");
            $mkdirResult = mkdir($dirNameNew);
            if (!$mkdirResult) {
                $this->setReturnValue(1);
                $this->addDangerAlerts(__("Creation of {0} directory failed. Exiting!", $dirNameNew));
                return false;
            }

            $sourceFile = $hotFolderEntry->path;

            //create a dummy Entity so that the rename() does not create a race condition with the SQL data
            $hotFolderEntryDummy = $hotFolderEntry->toArray();
            unset($hotFolderEntryDummy['id']);
            $hotFolderEntryDummy = $this->HotFolderEntryTable->newEntity($hotFolderEntryDummy);
            $this->HotFolderEntryTable->save($hotFolderEntryDummy);

            //update the original Entity
            $hotFolderEntry->path = $dirNameNew;                //unique entries are based on this
            $hotFolderEntry->path_hash_sum = sha1($dirNameNew); //unique entries are based on this
            $this->HotFolderEntryTable->save($hotFolderEntry);

            //move the file
            $destinationFile = $dirNameNew . $baseName;
            $renameResult = rename($sourceFile, $destinationFile);
            if (!$renameResult) {
                $this->setReturnValue(1);
                $this->addDangerAlerts(__("Could not move the file into {0} directory. Exiting!", $dirNameNew));
                return false;
            }

            //update the remaining hash sums in the original Entity
            $hashSums = $this->getPathChecksums($dirNameNew);
            $hotFolderEntry->listing_hash_sum = $hashSums['listing_hash_sum'];
            $hotFolderEntry->contents_hash_sum = $hashSums['contents_hash_sum'];
            $this->HotFolderEntryTable->save($hotFolderEntry);

            //delete the dummy Entity
            $this->HotFolderEntryTable->delete($hotFolderEntryDummy);
        }

        //do some action if this is a Folder
        if (is_dir($hotFolderEntry->path)) {
            //do something here
            $dts = date("Ymd-His");
            $newFolderGuid = Security::purl(6);
            $topDirectoryName = pathinfo($hotFolderEntry->path, PATHINFO_FILENAME);
            $tmpFolderTimeStamped = $Artifacts->getRepoUncTmpInput() . "{$dts}-{$newFolderGuid}-{$topDirectoryName}";
            @mkdir($tmpFolderTimeStamped, 0777, true);
            $tmpFolderOrder = TextFormatter::makeDirectoryTrailingSmartSlash($tmpFolderTimeStamped) . $topDirectoryName;

            $ZP = new ZipPackager();
            $fileListing = $ZP->rawFileList($hotFolderEntry->path);
            if (isset($fileListing[0])) {
                $description = $topDirectoryName . " " . pathinfo($fileListing[0], PATHINFO_DIRNAME);
                $description = trim($description, ". \t\n\r\0\x0B");
            } else {
                $description = $topDirectoryName;
            }

            //move folder to a TMP location
            rename($hotFolderEntry->path, $tmpFolderOrder);

            //delete the original $hotFolderEntry->path
            if (is_dir($hotFolderEntry->path)) {
                unlink($hotFolderEntry->path);
            }

            //delete the copied $tmpFolderTimeStamped via an Errand
            $options = [
                'name' => "Delete {$tmpFolderTimeStamped}",
                'class' => '\\App\\OrderManagement\\OrderManagementBase',
                'method' => 'cleanupTmpFolder',
                'parameters' => [$tmpFolderTimeStamped],
                'activation' => (new DateTime())->addDays(1),
                'expiration' => (new DateTime())->addDays(1)->addDays(1)
            ];
            $errand = $Errands->createErrand($options);

            //finally load the Order
            $orderOptions = [
                'name' => $topDirectoryName,
                'description' => "Photo Package - {$description}",
                'external_order_number' => $topDirectoryName,
            ];
            $order = $PhotoPackageOrdering->loadOrder($tmpFolderOrder, $orderOptions);

            //flag as completed
            $this->HotFolderEntryTable->flagEntryStatusAsSuccess($hotFolderEntry);

            //don't forget to exit with message and true/false
            $this->setReturnValue(0);
            $this->addSuccessAlerts(__("All good processing the {0}.", $entryType));
            $this->mergeAlerts($PhotoPackageOrdering->getAllAlertsForMerge());
            return true;
        }

        //got here so return an error;
        $this->setReturnValue(1);
        $this->addDangerAlerts(__("Error processing the {0}. Exiting!", $entryType));
        return false;
    }
}
