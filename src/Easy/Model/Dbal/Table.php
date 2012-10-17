<?php

namespace Easy\Model\ORM;

use Easy\Cache\Cache;
use Easy\Core\Object;
use Easy\Error;
use Easy\Model\Dbal\IDriver;
use Easy\Utility\Inflector;

class Schema extends Object
{

    protected $primaryKey;
    protected $prefix;
    protected $schema;
    protected $name = null;
    protected $driver;
    protected $mapper;

    public function __construct(IDriver $driver, IMapper $mapper)
    {
        $this->driver = $driver;
        $this->mapper = $mapper;
        $config = $driver->getConfig();
        $this->prefix = $config['prefix'];
    }

    public function getName()
    {
        if ($this->mapper !== null) {
            $this->name = Inflector::tableize($this->mapper->getTableName());
        }
        return $this->prefix . $this->name;
    }

    public function schema()
    {
        $this->sources = Cache::read('sources', '_easy_model_');
        if (empty($this->sources)) {
            if ($this->getName() && is_null($this->schema)) {
                $sources = $this->driver->listSources();
                if (!in_array($this->name, $sources)) {
                    throw new Error\MissingTableException(array(
                        "table" => $this->name,
                        "datasource" => $this->driver->useDbConfig
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
            $schema = $this->driver->describe($this->name);
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