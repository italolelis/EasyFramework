<?php

/**
 *  Datasource é o reposnsável pela conexão com o banco de dados, gerenciando
 *  o estado da conexão com o banco de dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
abstract class Datasource {

    public function __construct($config) {
        $this->config = $config;
    }

    abstract public function connect();

    abstract public function disconnect();

    abstract public function query($sql = null);

    public function __destruct() {
        $this->disconnect();
    }

}

?>