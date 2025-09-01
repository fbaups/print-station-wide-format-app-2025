<?php

namespace App\Database;

/**
 * Help define database limits based on the driver type
 *
 */
class Limits
{

    private string $dbDriver;

    private string $largeTextType;
    private string|int|null $largeTextLimit;
    private string $largeTextTypeIndex;
    private string|int|null $largeTextLimitIndex;
    private int $boundParamLimit;

    public function __construct($dbDriver = null)
    {
        if ($dbDriver) {
            $this->setDbDriver($dbDriver);
        } else {
            $this->setDbDriver('sqlite');
        }
    }

    /**
     * @param mixed|string $dbDriver
     */
    public function setDbDriver(mixed $dbDriver): void
    {
        $mysql = ['mysql', 'Cake\Database\Driver\Mysql'];
        $sqlite = ['sqlite', 'Cake\Database\Driver\Sqlite'];
        $sqlsrv = ['sqlsrv', 'sqlserver', 'Cake\Database\Driver\Sqlserver'];
        $pgsql = ['pgsql', 'Cake\Database\Driver\Postgres'];

        if (in_array($dbDriver, $mysql)) {
            $this->dbDriver = 'mysql';
        } elseif (in_array($dbDriver, $sqlite)) {
            $this->dbDriver = 'sqlite';
        } elseif (in_array($dbDriver, $sqlsrv)) {
            $this->dbDriver = 'sqlsrv';
        } elseif (in_array($dbDriver, $pgsql)) {
            $this->dbDriver = 'pgsql';
        } else {
            $this->dbDriver = 'sqlite';
        }

        if ($this->dbDriver === 'mysql') {
            $this->largeTextType = 'text';
            $this->largeTextLimit = null;

            $this->largeTextTypeIndex = 'string';
            $this->largeTextLimitIndex = 750;

            $this->boundParamLimit = 5000;
        } elseif ($this->dbDriver === 'sqlite') {
            $this->largeTextType = 'text';
            $this->largeTextLimit = null;

            $this->largeTextTypeIndex = 'string';
            $this->largeTextLimitIndex = 1500;

            $this->boundParamLimit = 100;
        } elseif ($this->dbDriver === 'sqlsrv') {
            $this->largeTextType = 'text';
            $this->largeTextLimit = null;

            $this->largeTextTypeIndex = 'string';
            $this->largeTextLimitIndex = 850;

            $this->boundParamLimit = 2000;
        } elseif ($this->dbDriver === 'pgsql') {
            $this->largeTextType = 'text';
            $this->largeTextLimit = null;

            $this->largeTextTypeIndex = 'string';
            $this->largeTextLimitIndex = 850;

            $this->boundParamLimit = 2000;
        }
    }

    /**
     * @return string
     */
    public function getLargeTextType(): string
    {
        return $this->largeTextType;
    }

    /**
     * @return string|int
     */
    public function getLargeTextLimit(): string|int|null
    {
        return $this->largeTextLimit;
    }

    /**
     * @return string
     */
    public function getLargeTextTypeIndex(): string
    {
        return $this->largeTextTypeIndex;
    }

    /**
     * @return string|int
     */
    public function getLargeTextLimitIndex(): string|int|null
    {
        return $this->largeTextLimitIndex;
    }

    /**
     * @return int
     */
    public function getBoundParamLimit(): int
    {
        return $this->boundParamLimit;
    }

    /**
     * @return string
     */
    public function getDbDriver(): string
    {
        return $this->dbDriver;
    }

}
