<?php

namespace App\Database\Schema;

use Cake\Database\Schema\TableSchema;

class DummySchemaDialect extends \Cake\Database\Schema\SchemaDialect
{

    /**
     * @inheritDoc
     */
    public function listTablesSql(array $config): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function describeColumnSql(string $tableName, array $config): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function describeIndexSql(string $tableName, array $config): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function describeForeignKeySql(string $tableName, array $config): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function convertColumnDescription(TableSchema $schema, array $row): void
    {
    }

    /**
     * @inheritDoc
     */
    public function convertIndexDescription(TableSchema $schema, array $row): void
    {
    }

    /**
     * @inheritDoc
     */
    public function convertForeignKeyDescription(TableSchema $schema, array $row): void
    {
    }

    /**
     * @inheritDoc
     */
    public function createTableSql(TableSchema $schema, array $columns, array $constraints, array $indexes): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function columnSql(TableSchema $schema, string $name): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function addConstraintSql(TableSchema $schema): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function dropConstraintSql(TableSchema $schema): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function constraintSql(TableSchema $schema, string $name): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function indexSql(TableSchema $schema, string $name): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function truncateTableSql(TableSchema $schema): array
    {
        return [];
    }
}
