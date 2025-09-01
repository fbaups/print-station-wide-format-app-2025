<?php
$fbSpec = [
    //Job name (in order of precedence) on the RIP
    'JDF/CustomerInfo/@CustomerJobName' => ['type' => 'string', 'options' => '', 'default' => ''],
    'JDF/@DescriptiveName' => ['type' => 'string', 'options' => '', 'default' => ''],
    'JDF/@JobID' => ['type' => 'string', 'options' => '', 'default' => ''],

    //Copies to print
    '/JDF/ResourceLinkPool/ComponentLink/@Amount' => ['type' => 'integer', 'options' => '', 'default' => 1],

    //Plex (number of sides printed)
    '/JDF/ResourcePool/LayoutPreparationParams/@Sides' => ['type' => 'string', 'options' => ['None' => 'OneSidedFront', 'Flip on Long Edge' => 'TwoSidedFlipY', 'Flip on Short Edge' => 'TwoSidedFlipX',], 'default' => 'OneSidedFront'],
];

return $fbSpec;
