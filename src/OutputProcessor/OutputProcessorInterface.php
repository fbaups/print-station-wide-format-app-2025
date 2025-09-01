<?php

namespace App\OutputProcessor;

use App\Model\Entity\Errand;

interface OutputProcessorInterface
{
    /**
     * Classes must implement an output method.
     *
     * $fileOrFolderPath the object to be sent to the output
     * $outputConfiguration if for example, was a sftp site, $outputConfiguration might contain url, username and password
     *
     * @param string $inputFileOrFolderPath either a file or folder path
     * @param array $outputConfiguration the config for the output destination
     * @return bool
     */
    public function output(string $inputFileOrFolderPath, array $outputConfiguration): bool;

    /**
     * Classes must implement an outputErrand method.
     * This will create an Errand to Output Process the OJD
     *
     * $fileOrFolderPath the object to be sent to the output
     * $outputConfiguration if for example, was a sftp site, $outputConfiguration might contain url, username and password
     *
     * @param string $inputFileOrFolderPath either a file or folder path
     * @param array $outputConfiguration the config for the output destination
     * @return false|Errand
     */
    public function outputErrand(string $inputFileOrFolderPath, array $outputConfiguration): false|Errand;

}
