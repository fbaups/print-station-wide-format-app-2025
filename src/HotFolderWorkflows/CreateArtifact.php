<?php

namespace App\HotFolderWorkflows;

use App\HotFolderWorkflows\Base\WorkflowBase;
use App\Model\Entity\Artifact;
use App\Model\Entity\HotFolderEntry;
use App\Model\Table\ArtifactsTable;
use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use League\Flysystem\Filesystem;

/**
 * Example Workflow that creates Artifacts from an input in the Hot Folder.
 * If the input is a directory, it will be searched for files.
 *
 */
class CreateArtifact extends WorkflowBase
{
    private ArtifactsTable|Table $Artifacts;

    /**
     * Main method.
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

        $entryType = is_dir($entryPath) ? "Folder" : "File";


        $this->Artifacts = TableRegistry::getTableLocator()->get('Artifacts');

        //do some action if this is a Folder
        if (is_dir($entryPath)) {
            //find all files in the folder and create an artifact for each
            $ZP = new ZipPackager();
            $path = TextFormatter::makeDirectoryTrailingSmartSlash($entryPath);
            $fileList = $ZP->rawFileList($path, true);
            foreach ($fileList as $file) {
                $this->createArtifact($path . $file);
            }

            //delete Folder otherwise it will be processed again!
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
            //process the singe file
            $this->createArtifact($entryPath);

            //delete File otherwise it will be processed again!
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
     * @param $file
     * @return bool|array|Artifact
     */
    private function createArtifact($file): bool|array|Artifact
    {
        $blob = file_get_contents_guzzle($file);

        if (!$blob) {
            return false;
        }

        $data = [
            'blob' => $blob,
            'name' => pathinfo($file, PATHINFO_BASENAME),
        ];

        $result = $this->Artifacts->createArtifact($data);
        $this->mergeAlerts($this->Artifacts->getAllAlertsForMerge());

        return $result;
    }

}
