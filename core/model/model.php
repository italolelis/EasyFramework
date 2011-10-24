<?php

App::import("Core", array(
    "model/connection",
    "model/table",
    "model/datasources/datasource"
));

/**
 *  Model é o responsável pela camada de dados da aplicação, fazendo a comunicação
 *  com o banco de dados através de uma camada de abstração. Possui funcionalidades
 *  CRUD, além de cuidar dos relacionamentos entre outros models.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
abstract class Model extends Object {

    /**
     *  Nome da tabela usada pelo modelo.
     */
    public $table = null;

    /**
     *  Ordenação padrão para o modelo.
     */
    protected $order = null;

    /**
     *  Limite padrão para o modelo.
     */
    protected $limit = null;

    /**
     *  Condições padrão para o modelo.
     */
    protected $conditions = array();
    protected static $instances = array();

    public function connection() {
        return Table::load($this)->connection();
    }

    protected function table() {
        return Table::load($this)->name();
    }

    public function getTable() {
        return $this->table;
    }

    /**
      Method: load
     */
    // Model::load() only helps with performance and will be removed when we begin to use late static binding
    public static function load($name) {
        if (!array_key_exists($name, Model::$instances)) {
            if (App::path("Model", strtolower($name)))
                Model::$instances[$name] = & ClassRegistry::load($name);
            else
                throw new MissingModelException(array("model" => $name));
        }
        return Model::$instances[$name];
    }

    /**
     *  Busca registros no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function all($params = array()) {
        //TODO: ao não passar nada como parâmetro, por padrão precisamos criar um SELECT * FROM
        $params = array_merge(
                array(
            "fields" => array_keys(Table::load($this)->schema()),
            "join" => isset($params['join']) ? $params['join'] : null,
            "conditions" => isset($params['conditions']) ? array_merge($this->conditions, $params['conditions']) : $this->conditions,
            "order" => $this->order,
            "groupBy" => isset($params['groupBy']) ? $params['groupBy'] : null,
            "limit" => $this->limit
                ), $params
        );
        $results = $this->connection()->read($this->table(), $params);
        return $results;
    }

    /**
     *  Busca o primeiro registro no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function first($params = array()) {
        $params = array_merge(array("limit" => 1), $params);
        $results = $this->all($params);
        return empty($results) ? array() : $results[0];
    }

    /**
     *  Conta registros no banco de dados.
     *
     *  @param array $params Parâmetros da busca
     *  @return integer Quantidade de registros encontrados
     */
    public function count($params = array()) {
        $params = array_merge(
                array(
            "fields" => "*",
            "join" => isset($params['join']) ? $params['join'] : null,
            "conditions" => isset($params['conditions']) ? array_merge($this->conditions, $params['conditions']) : $this->conditions,
            "order" => $this->order,
            "groupBy" => isset($params['groupBy']) ? $params['groupBy'] : null,
            "limit" => $this->limit,
                ), $params
        );
        return $this->connection()->count($this->table(), $params);
    }

    /**
     *  Insere um registro no banco de dados.
     *
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function insert($data) {
        return $this->connection()->create($this->table(), $data);
    }

    function update($params, $data) {
        $params = array_merge(
                array(
            "conditions" => array(),
            "order" => null,
            "limit" => null), $params
        );
        return $this->connection()->update($this->table(), array_merge($params, compact("data")));
    }

    /**
     *  Salva um registro no banco de dados.
     *
     *  @param array $data Dados a serem salvos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function save($data) {
        if (isset($data['id']) && !is_null($data['id'])) {
            $save = $this->update(array(
                "conditions" => array('id' => $data['id']),
                "limit" => 1
                    ), $data);
        } else {
            $save = $this->insert($data);
        }
        return $save;
    }

    /**
     *  Apaga registros do banco de dados.
     *
     *  @param array $id Parâmetros a serem usados na operação
     *  @return boolean Verdadeiro caso os registros tenham sido apagados.
     */
    public function delete($id) {
        $params = array(
            "conditions" => array('id' => $id),
            "order" => $this->order,
            "limit" => 1
        );
        return $this->connection()->delete($this->table(), $params);
    }

    public function getAffectedRows() {
        return $this->connection()->getAffectedRows();
    }

    public function fetch_array() {
        return $this->connection()->fetch_array();
    }

    public function fetch_assoc($result = null) {
        return $this->connection()->fetch_assoc($result);
    }

    public function fetch_object() {
        return $this->connection()->fetch_object();
    }

    public function query($query) {
        return $this->connection()->query($query);
    }

    /**
     * Converte uma data para o formato do MySQL
     * 
     * @param string $data
     * @return string 
     */
    function converter_data($data) {
        return date('Y-m-d', strtotime(str_replace("/", "-", $data)));
    }

}

?>
