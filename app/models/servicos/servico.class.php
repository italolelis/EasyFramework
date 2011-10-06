<?php

/**
 * Description of project
 *
 * @author italo
 */
class Servico {

    private $id, $nome, $dataCadastro;

    function __construct($id = null, $nome = null, $dataCadastro = null) {
        $this->id = $id;
        $this->nome = $nome;
        $this->dataCadastro = $dataCadastro;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getDataCadastro() {
        return $this->dataCadastro;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
    }

}

?>
