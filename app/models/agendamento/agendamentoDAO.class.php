<?php

/**
 * Description of projectsDAO
 * 
 * @author italo
 */
class AgendamentoDAO extends ModelDAO {

    private $agendamento;

    public function getAgendamento() {
        return $this->agendamento;
    }

    function __construct(Agendamento $agendamento = null) {
        parent::__construct('agendamentos');
        if ($agendamento != null) {
            $this->agendamento = $agendamento;
            //trata a data para ser aceita no MySQL
            $this->agendamento->setDia($this->converter_data($this->agendamento->getDia()));
        }
    }

    public function add() {
        $sql = "INSERT INTO {$this->table} (data, hora, servicos, cliente_id)
                   VALUES('{$this->agendamento->getDia()}','{$this->agendamento->getHora()}',
                   '{$this->agendamento->getServicos()}','{$this->agendamento->getCliente()}')";
        return $this->query($sql);
    }

    public function update() {
        $sql = "UPDATE {$this->table} SET 
                    data = '{$this->agendamento->getDia()}',
                    hora='{$this->agendamento->getHora()}',
                    servicos='{$this->agendamento->getServicos()}',
                    status='{$this->agendamento->getStatus()}'
                WHERE id='{$this->agendamento->getId()}'";
        return $this->query($sql);
    }

    public function delete() {
        $sql = "DELETE FROM {$this->table} WHERE id='{$this->agendamento->getId()}'";
        return $this->query($sql);
    }

    public function getAll() {
        $sql = "SELECT agendamentos.id, usuarios.nome AS cliente, DATE_FORMAT(data,'%d/%m/%Y') AS data, hora, status 
                FROM {$this->table} INNER JOIN usuarios ON cliente_id = usuarios.id
                WHERE YEAR(data) = YEAR(NOW())";
        return $this->find_by_sql($sql);
    }

    public function getAllByUserId($id) {
        $sql = "SELECT agendamentos.id, DATE_FORMAT(data,'%d/%m/%Y') AS data, hora, status FROM {$this->table} 
                INNER JOIN usuarios ON cliente_id = usuarios.id
                WHERE cliente_id = {$id} AND YEAR(data) = YEAR(NOW())
                ORDER BY 2";
        return $this->find_by_sql($sql);
    }

    public function getById($id) {
        $result_array = $this->find_by_sql("SELECT id, DATE_FORMAT(data,'%d/%m/%Y')  AS data, hora, servicos, status FROM {$this->table}
                                            WHERE id='$id' LIMIT 1");
        return!empty($result_array) ? array_shift($result_array) : false;
    }

    public function getHorarios($data) {
        $sql = "SELECT hora, status FROM {$this->table} 
                WHERE data = {$this->converter_data($data)}";
        return $this->find_by_sql($sql);
    }

    public function inicialize($record) {
        return $object = new Agendamento(isset($record['id']) ? $record['id'] : null,
                        isset($record['data']) ? $record['data'] : null,
                        isset($record['hora']) ? $record['hora'] : null,
                        isset($record['servicos']) ? $record['servicos'] : null,
                        isset($record['status']) ? $record['status'] : null,
                        isset($record['cliente']) ? $record['cliente'] : null
        );
    }

}

?>
