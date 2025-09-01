<?php

namespace App\Migrations;

use App\Database\Limits;
use Migrations\CakeAdapter;

class AppAbstractMigration extends \Phinx\Migration\AbstractMigration
{
    private Limits|null $Limits = null;

    private function importLimits()
    {
        if (!$this->Limits) {
            /**
             * @var CakeAdapter $adapter
             */
            $adapter = $this->adapter;
            $adapterType = $adapter->getAdapterType();
            $this->Limits = new Limits($adapterType);
        }
    }

    /**
     * @return string|int|null
     */
    public function getDbDriver(): int|string|null
    {
        $this->importLimits();
        return $this->Limits->getDbDriver();
    }

    /**
     * @return string|int|null
     */
    public function getLargeTextType(): int|string|null
    {
        $this->importLimits();
        return $this->Limits->getLargeTextType();
    }

    /**
     * @return string|int|null
     */
    public function getLargeTextLimit(): int|string|null
    {
        $this->importLimits();
        return $this->Limits->getLargeTextLimit();
    }

    /**
     * @return string|int|null
     */
    public function getLargeTextTypeIndex(): int|string|null
    {
        $this->importLimits();
        return $this->Limits->getLargeTextTypeIndex();
    }

    /**
     * @return string|int|null
     */
    public function getLargeTextLimitIndex(): int|string|null
    {
        $this->importLimits();
        return $this->Limits->getLargeTextLimitIndex();
    }

    /**
     * Convert SQL Server ntext to nvarchar(max) column type.
     * Handles the column constraint.
     *
     * @param $tableName
     * @return void
     */
    public function convertNtextToNvarchar($tableName)
    {
        $driver = $this->getDbDriver();
        if ($driver !== 'sqlsrv') {
            return;
        }

        $statement = "SELECT
                            TABLE_NAME AS TableName,
                            COLUMN_NAME AS ColumnName,
                            DATA_TYPE AS DataType,
                            CHARACTER_MAXIMUM_LENGTH AS MaxLength
                        FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_NAME = '{$tableName}';";
        $columns = $this->fetchAll($statement);

        foreach ($columns as $column) {
            if ($column['DataType'] !== 'ntext') {
                continue;
            }

            try {
                $columnName = $column['ColumnName'];

                //find constraints and drop them
                $statement = "SELECT *
                        FROM sys.default_constraints
                        WHERE parent_object_id = OBJECT_ID('$tableName')
                          AND parent_column_id = (
                            SELECT column_id
                            FROM sys.columns
                            WHERE object_id = OBJECT_ID('$tableName')
                              AND name = '$columnName'
                          );";
                $result = $this->fetchRow($statement);
                if (isset($result['name']) && isset($result['definition'])) {
                    $constraintName = $result['name'];
                    $statement = "ALTER TABLE {$tableName} DROP CONSTRAINT {$constraintName};";
                    $dropped = $this->execute($statement);
                }

                //alter the table
                try {
                    $statement = "ALTER TABLE {$tableName} ALTER COLUMN {$columnName} nvarchar(max);";
                    $this->execute($statement);
                } catch (\Throwable $exception) {
                    dd("ONE", $result, $exception->getMessage());
                }

                //re-create the constraint
                if (isset($result['name']) && isset($result['definition'])) {
                    $constraintName = $result['name'];
                    $definition = trim($result['definition'], "()");
                    $statement = "ALTER TABLE {$tableName} ADD CONSTRAINT {$constraintName} DEFAULT {$definition} FOR {$columnName};";
                    $this->execute($statement);
                }
            } catch (\Throwable $exception) {
                dump($columnName);
                dd("TWO", $exception->getMessage(), $exception->getLine());
            }
        }
    }

}
