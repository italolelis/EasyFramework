<?php

/**
 *  MysqlDatasource é a camada de abstração para bancos de dados
 *  MySQL. A classe provê métodos para criação e execução de consultas e retorno
 *  dos respectivos dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, Easy Framework
 *
 */
class MysqlDatasource extends Datasource {

    /**
     *  Conexão utilizada pelo banco de dados.
     */
    protected $connection;

    /**
     *  Resultado das consultas ao banco de dados.
     */
    protected $results;

    /**
     *  Verifica se o banco de dados está conectado.
     */
    protected $connected = false;

    /**
     * Verifica a ultima consulta realizada
     * 
     */
    protected $last_query;

    /**
     *  Retorna a conexão com o banco de dados, ou conecta caso a conexão ainda
     *  não tenha sido estabelecida.
     *
     *  @return resource Conexão com o banco de dados
     */
    public function &getConnection() {
        if (!$this->connected):
            $this->connect();
        endif;
        return $this->connection;
    }

    /**
     *  Conecta ao banco de dados.
     *
     *  @return resource Conexão com o banco de dados
     */
    public function connect() {
        //Realiza a conexão com o mysql
        $this->connection = mysqli_connect($this->config["host"], $this->config["user"], $this->config["password"], $this->config["database"]);
        //Se tudo ocorrer normalmente informa a váriavel que o banco está conectado
        $this->connected = true;
        //Habilita a opção de autocommit
        $this->autocommit();
        //Compatibilidade de Caracteres
        $this->setNames();
        //Retorna a conexão
        return $this->connection;
    }

    /**
     *  Desconecta do banco de dados.
     *
     *  @return boolean Verdadeiro caso a conexão tenha sido desfeita
     */
    public function disconnect() {
        //Se a conexão for fechada corretamente
        if ($this->connection->close()) {
            //informa a variável que o banco está desconectado
            $this->connected = false;
            //Seta o link como null
            $this->connection = null;
        }
        //Retorna o resultado da operação
        return!$this->connected;
    }

    public function autocommit($state = true) {
        return $this->connection->autocommit($state);
    }

    /**
     *  Executa uma consulta SQL.
     *
     *  @param string $sql Consulta SQL
     *  @return mixed Resultado da consulta
     */
    public function query($sql = null) {
        $this->getConnection();
        //Salva a consulta
        $this->last_query = $sql;
        //Realiza a consulta
        $this->results = $this->connection->query($sql);
        //Confirma se a consulta foi bem sucedida
        $this->confirm_query();
        //Retorna o resultado da consulta
        return $this->results;
    }

    private function confirm_query() {
        //Se o resultado da consulta for falso
        if (!$this->results) {
            //Informa o erro
            $output = "Database query error" . mysql_error() . "<br/>
                       Last query: $this->last_query";
            die($output);
        }
    }

    /**
     *  Retorna a quantidade de linhas afetadas pela última consulta.
     *
     *  @return integer Quantidade de linhas afetadas
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows();
    }

    public function fetch_array() {
        return $this->results->fetch_array();
    }

    public function fetch_assoc() {
        return $this->results->fetch_assoc();
    }

    public function fetch_object($className = null) {
        return $this->results->fetch_object($className);
    }

    /**
     * Set all fields to the desired type of encoding
     * @param string $encode 
     */
    private function setNames($encode = 'UTF8') {
        return $this->query("SET NAMES '$encode'");
    }

}

?>