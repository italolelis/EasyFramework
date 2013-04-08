<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Model\ORM;

use Easy\Core\App;
use Easy\Core\Object;
use Easy\Mvc\Model\Dbal\IDriver;
use Easy\Mvc\Model\Dbal\Schema;
use Easy\Mvc\Model\Dbal\Table;

/**
 * An EntityRepository serves as a repository for entities with generic as well as business specific methods for retrieving entities.
 * This class is designed for inheritance and users can subclass this class to write their own repositories with business-specific methods to locate entities.
 * 
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class EntityRepository extends Object
{

    /**
     * @var string Schema object
     */
    protected $schema;

    /**
     * @var string 
     */
    protected $entityName;

    /**
     * @var array 
     */
    protected $mappers = array();

    public function __construct($entityName, IDriver $driver)
    {
        $this->entityName = $entityName;
        //TODO: Implementar maneira de não instanciar mapper caso não seja necessário
        if (!isset($this->mappers[$entityName])) {
            $mapperClass = App::classname($entityName, "Model/Mapping", "Mapper");
            $this->mappers[$entityName] = new $mapperClass();
        }
        $options = $driver->getConfig();
        $this->schema = new Schema($driver);
        $table = new Table($this->mappers[$entityName]->getTableName(), $this->schema, $options['prefix']);
        $this->schema->addTable($table);
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function getTable()
    {
        return $this->schema->getTable($this->mappers[$this->entityName]->getTableName());
    }

    public function getMapper()
    {
        return $this->mappers[$this->entityName];
    }

    public function getEntityName()
    {
        return $this->entityName;
    }

    public function getNamespacedEntityName()
    {
        return App::classname($this->getEntityName(), "Model");
    }

}
