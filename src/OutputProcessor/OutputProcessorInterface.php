<?php

namespace App\OutputProcessor;

use App\Model\Entity\Errand;

interface OutputProcessorInterface
{
    /**
     * Classes must implement a process() method.
     * The process() determines:
     *  - if to create an Errand or run in real time.
     *  - if Errand, multiple or a single Errands to run
     *      - Multiple - best for concurrency and speed
     *      - Single - best for FIFO and maintain a group structure.
     *
     * @param array $outputItems
     * @return void
     */
    public function process(array $outputItems): void;


}
