<?php

namespace App\Database\Driver;

use App\Database\Schema\DummySchemaDialect;
use Cake\Database\Driver;
use Cake\Database\DriverFeatureEnum;

class Dummy extends Driver
{
    /**
     * The schema dialect class for this driver
     *
     * @var DummySchemaDialect|null
     */
    protected \Cake\Database\Schema\SchemaDialect $_schemaDialect;

    /**
     * @inheritDoc
     */
    public function connect(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function releaseSavePointSQL($name): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function savePointSQL($name): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function rollbackSavePointSQL($name): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function disableForeignKeySQL(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function enableForeignKeySQL(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function supportsDynamicConstraints(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function supportsSavePoints(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function queryTranslator($type): \Closure
    {
        return function ($query) use ($type) {
            return $query;
        };
    }

    /**
     * @inheritDoc
     */
    public function schemaDialect(): \Cake\Database\Schema\SchemaDialect
    {
        if ($this->_schemaDialect === null) {
            $this->_schemaDialect = new DummySchemaDialect($this);
        }

        return $this->_schemaDialect;
    }

    /**
     * @inheritDoc
     */
    public function quoteIdentifier($identifier): string
    {
        return $identifier;
    }

    public function supports(DriverFeatureEnum $feature): bool
    {
        return true;
    }
}
