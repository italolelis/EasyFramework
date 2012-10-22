<?php

namespace Easy\Model\Dbal;

use Easy\Cache\Cache;
use Easy\Collections\Dictionary;
use Easy\Collections\IDictionary;
use Easy\Core\Object;
use Easy\Error;
use Easy\Model\Dbal\IDriver;
use Easy\Model\Dbal\Table;

class Schema extends Object implements ISchema
{

    /**
     * @var IDriver The driver object 
     */
    protected $driver;

    /**
     * @var IDictionary 
     */
    protected $tables;

    public function __construct(IDriver $driver)
    {
        $this->driver = $driver;
        $this->tables = new Dictionary();
        $config = $driver->getConfig();
        $this->prefix = $config['prefix'];
    }

    /**
     * Add a table to list
     * @param Table $table The table object to add
     * @return boolean
     */
    public function addTable(Table $table)
    {
        return $this->tables->add($table->getName(), $table);
    }

    /**
     * Remove the table form list
     * @param Table $table The table object to remove
     * @return boolean
     */
    public function removeTable(Table $table)
    {
        return $this->tables->remove($table->getName());
    }

    /**
     * Get all tables of this schema.
     * @return IDictionary
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param string $tableName
     * @return Table
     */
    public function getTable($tableName)
    {
        return $this->tables->offsetGet($tableName);
    }

    /**
     * @return IDriver Gets the Driver object
     */
    public function getDriver()
    {
        return $this->driver;
    }

    public function listTables()
    {
        $sources = Cache::read('sources', '_easy_model_');
        if (empty($this->sources)) {
            if ($this->getName() && is_null($this->schema)) {
                $sources = $this->driver->listTables();
                if (!in_array($this->name, $sources)) {
                    throw new Error\MissingTableException(array(
                        "table" => $this->name,
                        "datasource" => $this->driver->useDbConfig
                    ));
                }
            }
            Cache::write('sources', $sources, '_easy_model_');
        }
        return $sources;
    }

}