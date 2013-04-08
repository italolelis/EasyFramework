<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\Dbal;

/**
 * Object Representation of a table
 * @since   2.0
 * @author  Ítalo Lelis de Vietro <italolelis@gmail.com>
 */
class Table
{

    /**
     * @var string The table name
     */
    protected $name = null;

    /**
     * @var string The primary key name
     */
    protected $primaryKey = false;

    /**
     * @var string The table prefix 
     */
    protected $prefix;

    /**
     * @var ISchema The schema object for this table 
     */
    protected $schema;

    /**
     *
     * @param string $tableName
     * @param ISchema $schema
     */
    public function __construct($tableName, ISchema $schema, $prefix = null)
    {
        if (empty($tableName)) {
            throw new Exceptions\DbalException('Invalid table name');
        }
        $this->name = $tableName;
        $this->schema = $schema;
        $this->prefix = $prefix;
        $this->getColumns();
    }

    public function getName()
    {
        return $this->prefix . $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Gets the columns of this table
     * @return array
     * @todo Implement a cache system
     */
    public function getColumns()
    {
        //$schema = Cache::read('columns', '_easy_model_');
        //if (empty($schema)) {
        $schema = $this->schema->getDriver()->listColumns($this->name);
        if (empty($this->primaryKey)) {
            foreach ($schema as $field => $describe) {
                if ($describe['key'] == 'PRI') {
                    $this->primaryKey = $field;
                    break;
                }
            }
        }
        //Cache::write('columns', $schema, '_easy_model_');
        //}
        return $schema;
    }

}