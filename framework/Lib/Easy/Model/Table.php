<?php

class Table extends Object {

    protected $primaryKey;
    protected $schema;
    protected $name;
    protected $model;
    protected static $cache = array();

    public function __construct($model) {
        $this->model = $model;
    }

    public static function load($model) {
        $model_name = get_class($model);

        if (!array_key_exists($model_name, self::$cache)) {
            self::$cache[$model_name] = new self($model);
        }

        return self::$cache[$model_name];
    }

    public function getName() {
        $this->name = $this->model->table;
        if (is_null($this->name)) {
            $this->name = Inflector::tableize(get_class($this->model));
        }
        return $this->name;
    }

    public function schema() {
        if ($this->getName() && is_null($this->schema)) {
            $db = $this->model->getConnection();
            $sources = $db->listSources();
            if (!in_array($this->name, $sources)) {
                throw new MissingTableException($this->name . ' could not be founded on.');
                return false;
            }

            if (empty($this->schema)) {
                $this->describe();
            }
        }

        return $this->schema;
    }

    public function primaryKey() {
        if ($this->getName() && $this->schema()) {
            return $this->primaryKey;
        }
    }

    protected function describe() {
        $db = $this->model->getConnection();
        $schema = $db->describe($this->name);
        if (is_null($this->primaryKey)) {
            foreach ($schema as $field => $describe) {
                if ($describe['key'] == 'PRI') {
                    $this->primaryKey = $field;
                    break;
                }
            }
        }

        return $this->schema = $schema;
    }

}