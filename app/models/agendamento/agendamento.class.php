<?php

/**
 * Description of project
 *
 * @author italo
 */
class Agendamento {

    private $id, $dia, $hora, $servicos, $status, $cliente, $dataCadastro;

    function __construct($id = null, $dia= null, $hora= null, $servicos= null, $status= null, $cliente= null, $dataCadastro= null) {
        $this->id = $id;
        $this->dia = $dia;
        $this->hora = $hora;
        $this->servicos = $servicos;
        $this->status = $status;
        $this->cliente = $cliente;
        $this->dataCadastro = $dataCadastro;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDia() {
        return $this->dia;
    }

    public function setDia($dia) {
        $this->dia = $dia;
    }

    public function getHora() {
        return $this->hora;
    }

    public function setHora($hora) {
        $this->hora = $hora;
    }

    public function getServicos() {
        return $this->servicos;
    }

    public function setServicos($servicos) {
        $this->servicos = $servicos;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function getDataCadastro() {
        return $this->dataCadastro;
    }

    public function setDataCadastro($dataCadastro) {
        $this->dataCadastro = $dataCadastro;
    }

}

?>
