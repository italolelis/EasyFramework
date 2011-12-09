<?php

class PdoDatasource extends Datasource {

    /**
     *  Verifica se o banco de dados está conectado.
     */
    protected $connected = false;

    /**
     *  Conexão utilizada pelo banco de dados.
     */
    protected $connection;

    /**
     *  Resultado das consultas ao banco de dados.
     */
    protected $results;

    /**
     * Verifica a ultima consulta realizada
     * 
     */
    protected $last_query;
    protected $params = array(
        //'fields' => '*',
        'joins' => array(),
        'conditions' => array(),
        'groupBy' => null,
        'having' => null,
        'order' => null,
        'offset' => null,
        'limit' => null
    );

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
        //Compatibilidade de Caracteres
        $this->setNames();
        //Retorna a conexão
        return $this->connection;
    }

    /**
     *  Retorna a conexão com o banco de dados, ou conecta caso a conexão ainda
     *  não tenha sido estabelecida.
     *
     *  @return resource Conexão com o banco de dados
     */
    public function getConnection() {
        if (!$this->connected)
            $this->connect();

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
        $this->logQuery($sql);
        //Habilita a opção de autocommit
        $this->autocommit();
        //Realiza a consulta
        $this->results = $this->connection->query($sql);
        //Confirma se a consulta foi bem sucedida
        $this->confirm_query($this->results);
        //Retorna o resultado da consulta
        return $this->results;
    }

    public function logQuery($sql) {
        $this->last_query = $sql;
    }

    private function confirm_query($result) {
        if (Config::read("debug")) {
            //Se o resultado da consulta for falso
            if (!$result) {
                //Informa o erro
                $output = "Database query error" . mysql_error() . "<br/>
                       Last query: {$this->last_query}";
                die($output);
            }
        }
    }

    /**
     * Set all fields to the desired type of encoding
     * @param string $encode 
     */
    private function setNames($encode = 'UTF8') {
        return $this->query("SET NAMES $encode");
    }

    /**
     * Realiza uma consulta SQL
     * 
     * @param string $sql
     * @return array 
     */
    public function fetchAll($result = null) {
        //Cria um array que receberá os objetos
        $object_array = array();
        //Percorre o resultado da consulta
        while ($row = $result->fetch_object()) {
            //Instância um objeto e coloca no array
            $object_array[] = $row;
        }
        //Retorna o array
        return $object_array;
    }

    /**
     *  Retorna a quantidade de linhas afetadas pela última consulta.
     *
     *  @return integer Quantidade de linhas afetadas
     */
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    public function fetch_array() {
        return $this->results->fetch_array();
    }

    public function fetch_assoc($result = null) {
        if (!is_null($result)) {
            return $result->fetch_assoc();
        } else {
            return $this->results->fetch_assoc();
        }
    }

    /**
     *  Insere um registro na tabela do banco de dados.
     *
     *  @param string $table Tabela a receber os dados
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se os dados foram inseridos
     */
    public function create($params = array()) {
        foreach ($params["data"] as $field => $value) {
            $insertValues ['fields'][] = $field;
            $insertValues ['values'][] = "'" . $value . "'";
        }
        $insertValues['table'] = $params['table'];
        $query = $this->renderInsert($insertValues);
        return $this->query($query);
    }

    /**
     *  Busca registros em uma tabela do banco de dados.
     *
     *  @param string $table Tabela a ser consultada
     *  @param array $params ParÃ¢metros da consulta
     *  @return array Resultados da busca
     */
    public function read($params) {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();

        $query = $this->renderSelect($params);
        $result = $this->query($query);

        $fetchedResult = $this->fetchAll($result);
        $result->close();

        return $fetchedResult;
    }

    /**
     *  Atualiza registros em uma tabela do banco de dados.
     *
     *  @param string $table Tabela a receber os dados
     *  @param array $params Parâmetros da consulta
     *  @return boolean Verdadeiro se os dados foram atualizados
     */
    public function update($params = array()) {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();

        $query = $this->renderUpdate($params);
        return $this->query($query);
    }

    /**
     *  Remove registros da tabela do banco de dados.
     *
     *  @param string $table Tabela onde estÃ£o os registros
     *  @param array $params Parâmetros da consulta
     *  @return boolean Verdadeiro se os dados foram excluÃ­dos
     */
    public function delete($params = array()) {
        $params += $this->params;

        $query = new ValueParser($params['conditions']);
        $params['conditions'] = $query->conditions();

        $query = $this->renderDelete($params);
        echo $query;
        if (!empty($params["conditions"]))
            return $this->query($query);
        else
            return false;
    }

}

?>
