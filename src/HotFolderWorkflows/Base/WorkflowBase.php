<?php

namespace App\HotFolderWorkflows\Base;

use App\Model\Entity\HotFolderEntry;
use App\Model\Table\HotFolderEntriesTable;
use App\Utility\Feedback\ReturnAlerts;
use arajcany\ToolBox\Utility\TextFormatter;
use arajcany\ToolBox\ZipPackager;
use Cake\Console\ConsoleIo;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class WorkflowBase
{
    use ReturnAlerts;

    protected ConsoleIo $io;

    protected Table|HotFolderEntriesTable $HotFolderEntryTable;

    public function __construct()
    {
        $this->HotFolderEntryTable = TableRegistry::getTableLocator()->get('HotFolderEntries');

        $this->io = new ConsoleIo();
    }

    /**
     * Default execute() so that Workflows don't fail.
     * The execute method assumes the FSO object is ready to be processed - i.e. writes have been completed.
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

        return true;
    }

    /**
     * Calculates the checksums of the given entry.
     *
     * @param string $hotFolderEntry
     * @return array
     */
    public function getPathChecksums(string $hotFolderEntry): array
    {
        $ZP = new ZipPackager();
        $path_hash_sum = sha1($hotFolderEntry);

        if (is_dir($hotFolderEntry)) {
            $listing = $ZP->rawFileAndFolderList($hotFolderEntry);
            $listing_hash_sum = sha1(json_encode($listing));
        } else {
            $listing_hash_sum = $path_hash_sum;
        }

        $options = [
            'directory' => true,
            'file' => true,
            'sha1' => true,
            'crc32' => false,
            'mime' => true,
            'size' => true,
            'contents' => false,
        ];
        $stats = $ZP->fileStats($hotFolderEntry, null, $options);
        $contents_hash_sum = sha1(json_encode($stats));

        return [
            'path_hash_sum' => $path_hash_sum,
            'listing_hash_sum' => $listing_hash_sum,
            'contents_hash_sum' => $contents_hash_sum,
        ];
    }
}
