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
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;

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
            mkdir($dirNameNew);

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
            //rename($sourceFile, $destinationFile);
            $this->moveFile($sourceFile, $destinationFile);

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
            //rename($hotFolderEntry->path, $tmpFolderOrder);
            $this->moveDirectory($hotFolderEntry->path, $tmpFolderOrder);

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

    /**
     * Move a file to a new location. 2-step operation with validation.
     * This way can read form UNC to Local as php rename() function can't do this.
     *
     * @param string $sourceFile
     * @param string $destinationFile
     * @param bool $deleteSourceFile
     * @return bool
     */
    private function moveFile(string $sourceFile, string $destinationFile, bool $deleteSourceFile = true): bool
    {
        // Initialize Flysystem Adapters
        $sourceAdapter = new LocalFilesystemAdapter(dirname($sourceFile));
        $destinationAdapter = new LocalFilesystemAdapter(dirname($destinationFile));

        $sourceFs = new Filesystem($sourceAdapter);
        $destinationFs = new Filesystem($destinationAdapter);

        // Extract filenames from paths
        $sourceFileName = basename($sourceFile);
        $destinationFileName = basename($destinationFile);

        // Check if source file exists
        try {
            if (!$sourceFs->fileExists($sourceFileName)) {
                return false;
            }
        } catch (\Throwable $exception) {
            return false;
        }

        try {
            // Open stream from source file
            $stream = $sourceFs->readStream($sourceFileName);
            if (!$stream) {
                return false;
            }

            // Write stream to destination
            $destinationFs->writeStream($destinationFileName, $stream);
            fclose($stream);

            // Verify destination file exists
            if (!$destinationFs->fileExists($destinationFileName)) {
                return false;
            }

            // Delete the original file if $deleteSourceFile is true
            if ($deleteSourceFile) {
                $sourceFs->delete($sourceFileName);

                // Verify source file is deleted
                if ($sourceFs->fileExists($sourceFileName)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }


    /**
     * Move a directory to a new location.
     *
     * @param string $sourceDir
     * @param string $destinationDir
     * @param bool $deleteSourceDir
     * @return bool
     */
    private function moveDirectory(string $sourceDir, string $destinationDir, bool $deleteSourceDir = true): bool
    {
        $sourceFs = new Filesystem(new LocalFilesystemAdapter($sourceDir));
        $destinationFs = new Filesystem(new LocalFilesystemAdapter($destinationDir));

        try {
            $contents = $sourceFs->listContents('', true);
        } catch (\Throwable $exception) {
            return false;
        }

        $failCounter = 0;

        foreach ($contents as $item) {
            $relativePath = $item->path(); // Path relative to the source directory
            $destinationPath = ltrim($relativePath, '/'); // Ensure correct destination path

            try {
                if ($item->isDir()) {
                    $destinationFs->createDirectory($destinationPath);

                    // Check if directory exists
                    if (!$destinationFs->directoryExists($destinationPath)) {
                        $failCounter++;
                    }
                } elseif ($item->isFile()) {
                    $stream = $sourceFs->readStream($relativePath);
                    $destinationFs->writeStream($destinationPath, $stream);
                    fclose($stream);

                    // Check if file exists
                    if (!$destinationFs->fileExists($destinationPath)) {
                        $failCounter++;
                    }
                }
            } catch (\Throwable $exception) {
                $failCounter++;
            }
        }

        if ($deleteSourceDir) {
            $deleteResult = $this->deleteDirectory($sourceDir);
            if (!$deleteResult) {
                $failCounter++;
            }
        }

        return $failCounter === 0;
    }


    /**
     * Recursively delete a directory.
     *
     * @param $dir
     * @return bool
     */
    private function deleteDirectory($dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $adapter = new LocalFilesystemAdapter($dir);
        $tmpFileSystem = new Filesystem($adapter);
        $path = '';
        try {
            $tmpFileSystem->deleteDirectory($path);
            if (is_dir($dir)) {
                return false;
            } else {
                return true;
            }
        } catch (\Throwable $exception) {
            return false;
        }
    }


}
