<?php

namespace Easy\Model;

use Easy\Core\Object;
use Easy\Utility\Inflector;
use Easy\Model\Metadata\TableMetadata;
use Easy\Cache\Cache;

class Table extends Object
{

    protected $primaryKey;
    protected $prefix;
    protected $schema;
    protected $name = null;

    /**
     * Entity Manager Object
     * @var EntityManager 
     */
    protected $connection;
    protected $model;
    protected $metadata;

    public function __construct($connection, $model, $prefix = null)
    {
        $this->connection = $connection;
        $this->model = $model;
        $this->prefix = $prefix;
        $this->metadata = new TableMetadata();
    }

    public function getName()
    {
        if ($this->model !== null) {
            $name = $this->metadata->getName($this->model);
            list(, $name) = namespaceSplit($name);
            $this->name = $name;
            if (is_null($this->name)) {
                list(, $name) = namespaceSplit(get_class($this->model));
                $this->name = Inflector::tableize($name);
            }
        }
        return $this->prefix . $this->name;
    }

    public function schema()
    {
        $this->sources = Cache::read('sources', '_easy_model_');
        if (empty($this->sources)) {
            if ($this->getName() && is_null($this->schema)) {
                $sources = $this->connection->listSources();
                if (!in_array($this->name, $sources)) {
                    throw new Error\MissingTableException(array(
                        "table" => $this->name,
                        "datasource" => $this->connection->useDbConfig
                    ));
                }

                if (empty($this->schema)) {
                    $this->schema = $this->describe();
                }
            }
            Cache::write('sources', $this->sources, '_easy_model_');
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
        $this->schema = Cache::read('describe', '_easy_model_');
        if (empty($this->schema)) {
            $schema = $this->connection->describe($this->name);
            if (is_null($this->primaryKey)) {
                foreach ($schema as $field => $describe) {
                    if ($describe['key'] == 'PRI') {
                        $this->primaryKey = $field;
                        break;
                    }
                }
            }
            Cache::write('describe', $this->schema, '_easy_model_');
        }
        return $schema;
    }

}