<?php

namespace App\HotFolderWorkflows;

use App\HotFolderWorkflows\Base\WorkflowBase;
use App\Model\Entity\HotFolderEntry;
use App\Model\Entity\Order;
use App\OrderManagement\WooCommerceOrdering;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use League\Flysystem\Filesystem;

/**
 * Example Workflow that can be copied and modified to suit.
 */
class ProcessWooCommerceOrders extends WorkflowBase
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

        $entryPath = $hotFolderEntry->path;

        //good idea to check that entry is a folder or file
        if (!is_dir($entryPath) && !is_file($entryPath)) {
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

        $entryType = is_dir($entryPath) ? "Folder" : "File";


        //do some action if this is a Folder
        if (is_dir($entryPath)) {
            //do something here
            $ZP = new ZipPackager();
            $path = TextFormatter::makeDirectoryTrailingSmartSlash($entryPath);
            $fileList = $ZP->rawFileList($path, true);
            foreach ($fileList as $file) {
                $this->parseOrderJson($path . $file);
            }

            //don't forget to move/delete the Folder otherwise it will be processed again!
            $adapter = new LocalFilesystemAdapter($entryPath);
            $filesystem = new Filesystem($adapter);
            try {
                $filesystem->deleteDirectory('');
            } catch (\Throwable $exception) {
            }

            //flag as completed
            $this->HotFolderEntryTable->flagEntryStatusAsSuccess($hotFolderEntry);

            //don't forget to exit with message and true/false
            $this->setReturnValue(0);
            $this->addSuccessAlerts(__("All good processing the {0}.", $entryType));
            return true;
        }

        //do some action if this is a File
        if (is_file($entryPath)) {
            //do something here
            $this->parseOrderJson($entryPath);

            //don't forget to move/delete the File otherwise it will be processed again!
            unlink($entryPath);

            //flag as completed
            $this->HotFolderEntryTable->flagEntryStatusAsSuccess($hotFolderEntry);

            //don't forget to exit with message and true/false
            $this->setReturnValue(0);
            $this->addSuccessAlerts(__("All good processing the {0}.", $entryType));
            return true;
        }

        //got here so return an error;
        $this->setReturnValue(1);
        $this->addDangerAlerts(__("Error processing the {0}. Exiting!", $entryType));
        return false;
    }

    /**
     * WooCommerce order JSON can be an array of multiple orders or a single order
     *
     * @param $jsonPath
     * @return Order[]
     */
    private function parseOrderJson($jsonPath): array
    {
        $WCO = new WooCommerceOrdering();
        $result = $WCO->loadOrder($jsonPath);
        $this->mergeAlerts($WCO->getAllAlertsForMerge());

        return $result;
    }
}
