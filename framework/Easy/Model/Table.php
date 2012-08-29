<?php

namespace Easy\Model;

use Easy\Core\Object;
use Easy\Utility\Inflector;
use Easy\Model\Metadata\TableMetadata;

class Table extends Object
{

    protected $primaryKey;
    protected $schema;
    protected $name = null;

    /**
     * Entity Manager Object
     * @var EntityManager 
     */
    protected $entityManager;
    protected static $cache = array();
    protected $metadata;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->metadata = new TableMetadata();
    }

    public static function load($entityManager)
    {
        return new self($entityManager);
    }

    public function getName()
    {
        $model = $this->entityManager->getModel();
        if ($model !== null) {
            $name = $this->metadata->getName($model);
            list(, $name) = namespaceSplit($name);
            $this->name = $name;
            if (is_null($this->name)) {
                list(, $name) = namespaceSplit(get_class($model));
                $this->name = Inflector::tableize($name);
            }
        }
        return $this->name;
    }

    public function schema()
    {
        if ($this->getName() && is_null($this->schema)) {
            $db = $this->entityManager->getConnection();
            $sources = $db->listSources();
            if (!in_array($this->name, $sources)) {
                throw new Error\MissingTableException(array(
                    "table" => $this->name,
                    "datasource" => $this->entityManager->useDbConfig
                ));
            }

            if (empty($this->schema)) {
                $this->schema = $this->describe();
            }
        }
        return $this->schema;
    }

    public function primaryKey()
    {
        if ($this->getName() && $this->schema()) {
            return $this->primaryKey;
        }
    }

    protected function describe()
    {
        $db = $this->entityManager->getConnection();
        $schema = $db->describe($this->name);
        if (is_null($this->primaryKey)) {
            foreach ($schema as $field => $describe) {
                if ($describe['key'] == 'PRI') {
                    $this->primaryKey = $field;
                    break;
                }
            }
        }

        return $schema;
    }

}