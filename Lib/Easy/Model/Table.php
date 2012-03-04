<?php

class Table {

    protected $primaryKey;
    protected $schema;
    protected $table;
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

    public function name() {
        $this->table = $this->model->table;
        if (is_null($this->table)) {
            $this->table = Inflector::underscore(get_class($this->model));
        }
        return $this->table;
    }

    public function schema() {
        if ($this->name() && is_null($this->schema)) {
            $db = $this->model->getConnection();
            $sources = $db->listSources();
            if (!in_array($this->table, $sources)) {
                throw new MissingTableException($this->table . ' could not be founded on.');
                return false;
            }

            if (empty($this->schema)) {
                $this->describe();
            }
        }

        return $this->schema;
    }

    public function primaryKey() {
        if ($this->name() && $this->schema()) {
            return $this->primaryKey;
        }
    }

    protected function describe() {
        $db = $this->model->getConnection();
        $schema = $db->describe($this->table);
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

?>
