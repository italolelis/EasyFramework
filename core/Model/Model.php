<?php

App::import("Core", array(
    "Model/Connection",
    "Model/Table",
    "Model/ValueParser",
    "Model/Datasources/Datasource",
    "Model/Datasources/PdoDatasource"
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
abstract class Model extends Hookable {

    /**
     *  Nome da tabela usada pelo modelo.
     */
    protected $table = null;

    /**
     * An model instances array
     */
    protected static $instances = array();

    public function getConnection() {
        return Table::load($this)->getConnection();
    }

    protected function table() {
        return Table::load($this)->name();
    }

    public function getTable() {
        return $this->table;
    }

    public function schema() {
        return Table::load($this)->schema();
    }

    public function primaryKey() {
        return Table::load($this)->primaryKey();
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
        $params += array("table" => $this->table());
        $results = $this->getConnection()->read($params);
        return $results;
    }

    /**
     *  Busca o primeiro registro no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function first($params = array()) {
        $params += array("limit" => 1);
        $results = $this->all($params);
        return empty($results) ? null : $results[0];
    }

    /**
     *  Conta registros no banco de dados.
     *
     *  @param array $params Parâmetros da busca
     *  @return integer Quantidade de registros encontrados
     */
    public function count($params = array()) {
        $params += array("table" => $this->table());
        return $this->getConnection()->count($params);
    }

    /**
     *  Insere um registro no banco de dados.
     *
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function insert($data) {
        $params = array("table" => $this->table(), "data" => $data);
        return $this->getConnection()->create($params);
    }

    function update($params, $data) {
        $params += array("table" => $this->table(), "values" => $data);
        return $this->getConnection()->update($params);
    }

    /**
     *  Salva um registro no banco de dados.
     *
     *  @param array $data Dados a serem salvos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function save($data) {
        $pk = $this->primaryKey();
        // verify if the record exists
        if (array_key_exists($pk, $data) && !is_null($data[$pk])) {
            $exists = true;
        } else {
            $exists = false;
        }

        if ($exists) {
            $data = array_intersect_key($data, $this->schema());

            $save = $this->update(array(
                "conditions" => array($pk => $data[$pk]),
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
        $params = array("table" => $this->table(), "conditions" => array("id" => $id));
        return $this->getConnection()->delete($params);
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
