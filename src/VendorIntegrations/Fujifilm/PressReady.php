<?php

namespace App\VendorIntegrations\Fujifilm;

use App\Utility\Feedback\ReturnAlerts;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use Throwable;

class PressReady
{
    use ReturnAlerts;

    private null|false|ConnectionInterface $connection = null;

    public function __construct(array $connectionOptions = [])
    {
        $this->setupConnection($connectionOptions);
    }


    protected function setupConnection(array $connectionOptions = []): void
    {
        //see if Connection has already been initiated by the application
        try {
            $ConnectionManagerPressReady = ConnectionManager::get('PressReady');
            $ConnectionManagerPressReady->getDriver()->connect();
            $this->connection = $ConnectionManagerPressReady;
            return;
        } catch (Throwable $connectionError) {
        }

        $connectionOptionsToUse = [
            'className' => 'Cake\\Database\\Connection',
            'driver' => 'Cake\\Database\\Driver\\Sqlserver',
            'persistent' => false,
            'host' => '.\\PRESSREADY',
            'port' => '',
            'username' => '',
            'password' => '',
            'database' => 'jobflow',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ];

        $ConnectionManagerAppDefault = ConnectionManager::get('default');
        $connectionOptionsAppDefault = ($ConnectionManagerAppDefault->config());

        //override with options used by the app
        if (str_contains($connectionOptionsAppDefault['driver'], 'Sqlserver')) {
            $connectionOptionsToUse['host'] = $connectionOptionsAppDefault['host'];
            $connectionOptionsToUse['port'] = $connectionOptionsAppDefault['port'];
            $connectionOptionsToUse['username'] = $connectionOptionsAppDefault['username'];
            $connectionOptionsToUse['password'] = $connectionOptionsAppDefault['password'];
            $connectionOptionsToUse['timezone'] = $connectionOptionsAppDefault['timezone'];
            $connectionOptionsToUse['cacheMetadata'] = $connectionOptionsAppDefault['cacheMetadata'];
            $connectionOptionsToUse['quoteIdentifiers'] = $connectionOptionsAppDefault['quoteIdentifiers'];
        }

        //override with options passed into the class
        if (str_contains($connectionOptionsAppDefault['driver'], 'Sqlserver')) {
            $connectionOptionsToUse['host'] = $connectionOptions['host'] ?? $connectionOptionsToUse['host'];
            $connectionOptionsToUse['port'] = $connectionOptions['port'] ?? $connectionOptionsToUse['port'];
            $connectionOptionsToUse['username'] = $connectionOptions['username'] ?? $connectionOptionsToUse['username'];
            $connectionOptionsToUse['password'] = $connectionOptions['password'] ?? $connectionOptionsToUse['password'];
            $connectionOptionsToUse['timezone'] = $connectionOptions['timezone'] ?? $connectionOptionsToUse['timezone'];
            $connectionOptionsToUse['cacheMetadata'] = $connectionOptions['cacheMetadata'] ?? $connectionOptionsToUse['cacheMetadata'];
            $connectionOptionsToUse['quoteIdentifiers'] = $connectionOptions['quoteIdentifiers'] ?? $connectionOptionsToUse['quoteIdentifiers'];
        }

        try {
            ConnectionManager::setConfig('PressReady', $connectionOptionsToUse);
            $ConnectionManagerPressReady = ConnectionManager::get('PressReady');
            $ConnectionManagerPressReady->getDriver()->connect();
            $this->connection = $ConnectionManagerPressReady;
        } catch (Throwable $connectionError) {
            $this->connection = false;
        }
    }


    public function getHotFolders(): false|array
    {
        if (!$this->connection) {
            return false;
        }

        $statement = 'SELECT * FROM jobflow_hotfolders';

        $results = $this->connection
            ->execute($statement)
            ->fetchAll('assoc');

        return $results;
    }


    public function getCsvHotFolders(): false|array
    {
        if (!$this->connection) {
            return [];
        }

        $st = "
SELECT HF.*,
       WF.workflow_oid,
       WF.workflow_name
FROM (SELECT jobflow.dbo.jobflow_hotfolders.jobflow_hotfolder_oid,
             jobflow.dbo.jobflow_hotfolders.jobflow_hotfolder_name,
             jobflow.dbo.jobflow_hotfolders.jobflow_hotfolder_path,
             isnull(jobflow.dbo.jobflow_hotfolders.submit_workflow_oid,
                    jobflow.dbo.hotfolder_workflow_paths.path_workflow_oid) as submit_workflow_oid,
             jobflow.dbo.jobflow_hotfolders.is_enabled,
             jobflow.dbo.jobflow_hotfolders.created_datetime,
             jobflow.dbo.jobflow_hotfolders.last_modified_datetime
      FROM jobflow.dbo.jobflow_hotfolders
               LEFT JOIN jobflow.dbo.hotfolder_workflow_paths
                         on jobflow_hotfolders.jobflow_hotfolder_oid =
                            hotfolder_workflow_paths.jobflow_hotfolder_oid) as HF
         LEFT JOIN jobflow.dbo.m_workflows as WF on HF.submit_workflow_oid = WF.workflow_oid
WHERE HF.submit_workflow_oid IN (SELECT workflow_oid
                                 FROM jobflow.dbo.m_wf_processes
                                 WHERE wf_process_type = 'parse-csv')";

        $results = $this->connection
            ->execute($st)
            ->fetchAll('assoc');

        return $results;
    }


    public function getPdfHotFolders(): false|array
    {
        if (!$this->connection) {
            return [];
        }

        $st = "
SELECT HF.*,
       WF.workflow_oid,
       WF.workflow_name
FROM (SELECT jobflow.dbo.jobflow_hotfolders.jobflow_hotfolder_oid,
             jobflow.dbo.jobflow_hotfolders.jobflow_hotfolder_name,
             jobflow.dbo.jobflow_hotfolders.jobflow_hotfolder_path,
             isnull(jobflow.dbo.jobflow_hotfolders.submit_workflow_oid,
                    jobflow.dbo.hotfolder_workflow_paths.path_workflow_oid) as submit_workflow_oid,
             jobflow.dbo.jobflow_hotfolders.is_enabled,
             jobflow.dbo.jobflow_hotfolders.created_datetime,
             jobflow.dbo.jobflow_hotfolders.last_modified_datetime
      FROM jobflow.dbo.jobflow_hotfolders
               LEFT JOIN jobflow.dbo.hotfolder_workflow_paths
                         on jobflow_hotfolders.jobflow_hotfolder_oid =
                            hotfolder_workflow_paths.jobflow_hotfolder_oid) as HF
         LEFT JOIN jobflow.dbo.m_workflows as WF on HF.submit_workflow_oid = WF.workflow_oid
WHERE HF.submit_workflow_oid NOT IN (SELECT workflow_oid
                                 FROM jobflow.dbo.m_wf_processes
                                 WHERE wf_process_type = 'parse-csv')";

        $results = $this->connection
            ->execute($st)
            ->fetchAll('assoc');

        return $results;
    }

    /**
     * @return array
     */
    public function getPdfHotFoldersOptionsList(): array
    {
        return $this->_getHotFoldersOptionsList('pdf');
    }

    /**
     * @return array
     */
    public function getCsvHotFoldersOptionsList(): array
    {
        return $this->_getHotFoldersOptionsList('csv');
    }

    /**
     * multi-dimensional array used in HTML->controls
     *
     * @param $type
     * @return array
     */
    private function _getHotFoldersOptionsList($type): array
    {
        if (strtolower($type) === 'pdf') {
            $pressReadyHotFolders = $this->getPdfHotFolders();
            if (!$pressReadyHotFolders) {
                return [];
            }
        } elseif (strtolower($type) === 'csv') {
            $pressReadyHotFolders = $this->getCsvHotFolders();
            if (!$pressReadyHotFolders) {
                return [];
            }
        } else {
            return [];
        }

        $hotFolderListSingleWorkflow = [];
        $hotFolderListConditionalWorkflow = [];
        foreach ($pressReadyHotFolders as $pressReadyHotFolder) {
            //single workflow
            $id = $pressReadyHotFolder['jobflow_hotfolder_oid'];
            $name = "[HF] " . $pressReadyHotFolder['jobflow_hotfolder_name'] . " [WF] " . $pressReadyHotFolder['workflow_name'];
            $hotFolderListSingleWorkflow[$id] = $name;

            //conditional workflow
            $group = "[HF] " . $pressReadyHotFolder['jobflow_hotfolder_name'];
            $id = $pressReadyHotFolder['jobflow_hotfolder_oid'] . "-" . $pressReadyHotFolder['submit_workflow_oid'];
            $name = "[WF] " . $pressReadyHotFolder['workflow_name'];
            $hotFolderListConditionalWorkflow[$group][$id] = $name;
        }

        //if ((count($hotFolderListSingleWorkflow) * 2) === (count($hotFolderListConditionalWorkflow, COUNT_RECURSIVE))) {
        //    $hotFolderOptions = $hotFolderListConditionalWorkflow;
        //} else {
        //    $hotFolderOptions = $hotFolderListConditionalWorkflow;
        //}

        return $hotFolderListConditionalWorkflow;
    }


    /**
     * @param int $hfId
     * @param int $wfId
     * @return array|false
     */
    public function getPdfHotFolderByIdAndWorkflowId(int $hfId, int $wfId): array|false
    {
        $pressReadyPdfHotFolders = $this->getPdfHotFolders();

        $hotFolderList = [];
        foreach ($pressReadyPdfHotFolders as $pressReadyPdfHotFolder) {
            $hotFolderList[$pressReadyPdfHotFolder['jobflow_hotfolder_oid']][$pressReadyPdfHotFolder['submit_workflow_oid']] = $pressReadyPdfHotFolder;
        }

        if (isset($hotFolderList[$hfId])) {
            if (isset($hotFolderList[$hfId][$wfId])) {
                return $hotFolderList[$hfId][$wfId];
            }
        }

        return false;
    }

    /**
     * @param int $hfId
     * @param int $wfId
     * @return array|false
     */
    public function getCsvHotFolderByHotFolderIdAndWorkflowId(int $hfId, int $wfId): array|false
    {
        $pressReadyCsvHotFolders = $this->getCsvHotFolders();
        $pressReadyCsvHotFolders = $this->compileCsvHotFolders();

        $hotFolderList = [];
        foreach ($pressReadyCsvHotFolders as $pressReadycsvHotFolder) {
            $hotFolderList[$pressReadycsvHotFolder['jobflow_hotfolder_oid']][$pressReadycsvHotFolder['submit_workflow_oid']] = $pressReadycsvHotFolder;
        }

        if (isset($hotFolderList[$hfId])) {
            if (isset($hotFolderList[$hfId][$wfId])) {
                return $hotFolderList[$hfId][$wfId];
            }
        }

        return false;
    }

    public function getCsvParseRules(): false|array
    {
        if (!$this->connection) {
            return false;
        }

        $st = "
SELECT *
FROM jobflow.dbo.csv_parse_rules";

        $results = $this->connection
            ->execute($st)
            ->fetchAll('assoc');

        $csvParseRules = [];
        foreach ($results as $result) {
            $csvParseRules[$result['rule_name']] = json_decode($result['rule_detail'], JSON_OBJECT_AS_ARRAY);
        }

        return $csvParseRules;
    }

    public function compileCsvHotFolders(): false|array
    {
        $csvHoFolders = $this->getCsvHotFolders();
        $parseRules = $this->getCsvParseRules();

        if (!$csvHoFolders || !$parseRules) {
            return [];
        }

        $st = "
SELECT workflow_oid, wf_process_prop_json
FROM jobflow.dbo.m_wf_processes
WHERE wf_process_type = 'parse-csv'";

        $results = $this->connection
            ->execute($st)
            ->fetchAll('assoc');

        $pointers = [];
        foreach ($results as $result) {
            $pointer = $result['wf_process_prop_json'];
            $pointer = json_decode($pointer, JSON_OBJECT_AS_ARRAY);

            if (isset($parseRules[$pointer['CsvParseRuleName']])) {
                $pointers[$result['workflow_oid']] = $parseRules[$pointer['CsvParseRuleName']];
            } else {
                $pointers[$result['workflow_oid']] = null;
            }
        }

        foreach ($csvHoFolders as $k => $csvHoFolder) {
            if (isset($pointers[$csvHoFolder['submit_workflow_oid']])) {
                $parseRule = $pointers[$csvHoFolder['submit_workflow_oid']];
                $csvHoFolders[$k]['csv_schema'] = $this->csvRuleToSchema($parseRule);
                $csvHoFolders[$k]['csv_parse_rule_checksum'] = sha1(serialize($parseRule));
                $csvHoFolders[$k]['csv_parse_rule'] = $parseRule;
            } else {
                $csvHoFolders[$k]['csv_schema'] = false;
                $csvHoFolders[$k]['csv_parse_rule_checksum'] = false;
                $csvHoFolders[$k]['csv_parse_rule'] = false;
            }

            $csvHoFolders[$k]['is_enabled'] = asBool($csvHoFolders[$k]['is_enabled']);
        }

        return $csvHoFolders;
    }

    public function csvRuleToSchema(array $csvParseRule): array
    {
        $schema = [];

        //SECTION - FullPath
        $colNum = $csvParseRule['JobFileAcquisitionMethod']['FullPathColumnNum'];
        $schema[$colNum] = [
            'columnName' => 'Full Path',
            'columnNameSlug' => $colNum . '-full-path',
            'columnNum' => $colNum,
            'isEnabled' => true,
            'dataType' => 'string',
        ];

        //SECTION - StandardAttrMappingRules
        foreach ($csvParseRule['StandardAttrMappingRules'] as $rule) {
            $rule['IsEnabled'] = asBool($rule['IsEnabled']);
            if (!$rule['IsEnabled']) {
                continue;
            }
            $name = $this->humaniseWorkflowName($rule['AttrName']);

            $dataType = 'string';
            $intTypes = ['wfjob.copies', 'biz.customIntAttr1', 'biz.customIntAttr2', 'biz.customIntAttr3', 'biz.customIntAttr4'];
            if (in_array($rule['AttrName'], $intTypes)) {
                $dataType = 'integer';
            }

            $schema[$rule['ColumnNum']] = [
                'columnName' => $name,
                'columnNameSlug' => $rule['ColumnNum'] . "-" . Inflector::dasherize(Inflector::variable($name)),
                'columnNum' => $rule['ColumnNum'],
                'isEnabled' => $rule['IsEnabled'],
                'dataType' => $dataType,
            ];
        }

        //SECTION - UserDefinedAttrMappingRules
        foreach ($csvParseRule['UserDefinedAttrMappingRules'] as $rule) {
            $rule['IsEnabled'] = asBool($rule['IsEnabled']);
            if (!$rule['IsEnabled']) {
                continue;
            }
            $name = $this->humaniseWorkflowName($rule['AttrName']);

            $schema[$rule['ColumnNum']] = [
                'columnName' => $name,
                'columnNameSlug' => $rule['ColumnNum'] . "-" . Inflector::dasherize(Inflector::variable($name)),
                'columnNum' => $rule['ColumnNum'],
                'isEnabled' => $rule['IsEnabled'],
                'dataType' => $rule['DataType'],
            ];
        }

        ksort($schema);

        return $schema;
    }

    private function humaniseWorkflowName(string $workflowName): string
    {
        if (str_contains($workflowName, '.')) {
            $workflowName = explode('.', $workflowName)[1];
        }

        $workflowName = Inflector::humanize(Inflector::delimit($workflowName));

        $letters = array_merge(range('a', 'z'), range('A', 'Z'));
        $numbers = range(0, 9);

        foreach ($letters as $letter) {
            foreach ($numbers as $number) {
                $workflowName = str_replace("{$letter}{$number}", "{$letter} {$number}", $workflowName);
            }
        }

        return $workflowName;
    }
}
