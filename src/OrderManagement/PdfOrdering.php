<?php

namespace App\OrderManagement;

use arajcany\ToolBox\Flysystem\Adapters\LocalFilesystemAdapter;
use arajcany\ToolBox\Utility\Security\Security;
use arajcany\ToolBox\Utility\TextFormatter;
use Cake\I18n\DateTime;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;

class PdfOrdering extends OrderManagementBase
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $orderData
     * @param array $orderOptions
     * @return int[]
     */
    public function loadOrder($orderData, array $orderOptions = []): array
    {
        /*
         * Depending on the folder structure is how we process the Order.
         *
         * Multi-level with Order and Job Level:
         * OrderFolder/
         * ├── JobFolder1/
         * │   ├── SubFolderA/
         * │   │   ├── file1.pdf
         * │   │   ├── file2.pdf
         * │   │   └── file3.pdf
         * │   ├── SubFolderB/
         * │   │   ├── file4.pdf
         * │   │   ├── file5.pdf
         * │   │   └── file6.pdf
         * │   └── SubFolderC/
         * │       ├── file7.pdf
         * │       ├── file8.pdf
         * │       └── file9.pdf
         * ├── JobFolder2/
         * │   ├── document1.pdf
         * │   ├── document2.pdf
         * │   ├── document3.pdf
         * │   └── document4.pdf
         * └── JobFolder3/
         *     ├── report1.pdf
         *     ├── report2.pdf
         *     └── report3.pdf
         * All files will be processed but only the first 2 folder levels are
         * taken into account as the Order and Job Number
         *
         *
         *
         * Top level folder with files only:
         * Folder1/
         * ├── document1.pdf
         * ├── document2.pdf
         * ├── document3.pdf
         * └── document4.pdf
         * Assumed to be an Order folder and PDF files will be grouped into a single Job
         *
         *
         *
         * Top level folder with files and folder:
         * Folder1/
         * ├── document1.pdf
         * ├── document2.pdf
         * ├── Folder2/
         * └── Folder3/
         * Assumed to be an Order folder and PDF files/folders will be grouped into a single Job
         */

        $ojdStructure = $this->preformatOjd($orderData);

        if ($ojdStructure) {
            return $this->saveOjdStructure($ojdStructure, $orderOptions);
        } else {
            return ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];
        }

    }


    /**
     * Preformat is converting and saving a structured OJD was too big for one function
     *
     * @param $storagePath
     * @return array|array[]|\array[][]|false
     */
    private function preformatOjd($storagePath): array|false
    {
        $storagePath = TextFormatter::makeDirectoryTrailingSmartSlash($storagePath);

        $adapter = new LocalFilesystemAdapter($storagePath);
        $fs = new Filesystem($adapter);
        $filesTopLevel = [];
        $foldersTopLevel = [];
        try {
            $listing = $fs->listContents('', false)->sortByPath();
            foreach ($listing as $item) {
                if ($item instanceof FileAttributes) {
                    $filesTopLevel[] = $item->path();
                } elseif ($item instanceof DirectoryAttributes) {
                    $foldersTopLevel[] = $item->path();
                }
            }
        } catch (\Throwable $exception) {
            $this->addDangerAlerts(__("Could not get directory listing"));
        }

        $filesRecursive = [];
        $foldersRecursive = [];
        try {
            $listing = $fs->listContents('', true)->sortByPath();
            foreach ($listing as $item) {
                if ($item instanceof FileAttributes) {
                    $filesRecursive[] = $item->path();
                } elseif ($item instanceof DirectoryAttributes) {
                    $foldersRecursive[] = $item->path();
                }
            }
        } catch (\Throwable $exception) {
            $this->addDangerAlerts(__("Could not get recursive directory listing"));
            return false;
        }

        //no files at all levels so abort
        if (empty($filesRecursive)) {
            $this->addDangerAlerts(__("No files supplied in the input directory"));
            return false;
        }

        //no files or folders in top level so abort
        if (empty($foldersTopLevel) && empty($filesTopLevel)) {
            $this->addDangerAlerts(__("Empty input directory."));
            return false;
        }


        if (!empty($foldersTopLevel) && empty($filesTopLevel)) {
            $orderId = pathinfo($storagePath, PATHINFO_BASENAME);
            $compiled = [$orderId => []];
            foreach ($foldersTopLevel as $folder) {
                try {
                    $listing = $fs->listContents($folder, true);
                    foreach ($listing as $item) {
                        if ($item instanceof FileAttributes) {
                            $fullPath = $storagePath . $item->path();
                            $fullPath = TextFormatter::normaliseSlashes($fullPath);
                            $compiled[$orderId][$folder][] = $fullPath;
                        }
                    }
                } catch (\Throwable $exception) {
                }
            }
            return $compiled;
        } else {
            $orderId = pathinfo($storagePath, PATHINFO_BASENAME);
            $jobId = time();
            $jobId = "J{$jobId}";
            $compiled = [$orderId => [$jobId => []]];
            foreach ($filesRecursive as $file) {
                $fullPath = $storagePath . $file;
                $fullPath = TextFormatter::normaliseSlashes($fullPath);
                $compiled[$orderId][$jobId][] = $fullPath;
            }
            return $compiled;
        }

    }


    /**
     * @param $payload
     * @param $orderOptions
     * @return array
     */
    private function saveOjdStructure($payload, $orderOptions): array
    {
        $tallies = ['order_count' => 0, 'job_count' => 0, 'document_count' => 0];

        $creationDate = new DateTime();
        $orderId = array_key_first($payload);

        $orderOptions = array_merge($this->getDefaultOrderOptions(), $orderOptions);
        $name = $orderOptions['name'] ?? "PDF-Order-{$orderId}";
        $description = $orderOptions['description'] ?? "PDF Order {$orderId}";
        $external_order_number = $orderOptions['external_order_number'] ?? $orderId;

        $orderStructured = [
            'guid' => Security::guid(),
            'order_status_id' => $this->getOrderStatusIdByName('Received'),
            'name' => $name,
            'description' => $description,
            'external_system_type' => 'PDF Folder',
            'external_order_number' => $external_order_number,
            'external_creation_date' => $creationDate,
            'payload' => $payload,
            'priority' => 3,
            'hash_sum' => sha1(json_encode($payload)),
            'jobs' => [],
        ];

        $jobCounter = 0;
        foreach ($payload[$orderId] as $jobId => $documents) {
            $jobStructured = [
                'guid' => Security::guid(),
                'job_status_id' => $this->getJobStatusIdByName('Received'),
                'name' => "PDF-Job-{$jobId}",
                'description' => "PDF Job {$jobId}",
                'quantity' => 1,
                'external_job_number' => $jobId,
                'external_creation_date' => $creationDate,
                'payload' => null,
                'priority' => 3,
                'documents' => [],
            ];

            $documentCounter = 0;
            foreach ($documents as $documentPath) {
                $documentName = pathinfo($documentPath, PATHINFO_BASENAME);
                $documentStructured = [
                    'guid' => Security::guid(),
                    'document_status_id' => $this->getDocumentStatusIdByName('Requested'),
                    'name' => $documentName,
                    'description' => $documentName,
                    'quantity' => 1,
                    'artifact_token' => null,
                    'external_document_number' => null,
                    'external_creation_date' => $creationDate,
                    'external_url' => $documentPath,
                    'payload' => null,
                    'priority' => 3,
                ];
                $jobStructured['documents'][$documentCounter] = $documentStructured;

                $documentCounter++;
            }

            $orderStructured['jobs'][$jobCounter] = $jobStructured;

            $jobCounter++;
        }

        $result = parent::loadOrder($orderStructured);

        $tallies = $this->addArraysByKey($tallies, $result);
        $this->addInfoAlerts($tallies);

        return $tallies;
    }

    public function getDefaultOrderOptions(): array
    {
        return [
            'numberOfJobs' => 'single',
        ];
    }

}
