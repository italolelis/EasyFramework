<?php

/**
 * Description of projectsDAO
 * 
 * @author italo
 */
class ServicoDAO extends ModelDAO {

    private $servicos;

    function __construct(Servico $servicos = null) {
        parent::__construct('servicos');
        if ($servicos != null) {
            $this->servicos = $servicos;
        }
    }

    public function add() {
        $sql = "INSERT INTO {$this->table} (nome)
                VALUES('{$this->servicos->getNome()}')";
        return $this->query($sql);
    }

    public function update() {
        $sql = "UPDATE {$this->table} SET 
                    nome = '{$this->servicos->getNome()}'
                WHERE id='{$this->servicos->getId()}'";
        return $this->query($sql);
    }

    public function delete() {
        $sql = "DELETE FROM {$this->table} WHERE id='{$this->servicos->getId()}'";
        return $this->query($sql);
    }

    public function getAll() {
        $sql = "SELECT id, nome FROM {$this->table}";
        return $this->find_by_sql($sql);
    }

    public function getById($id) {
        $result_array = $this->find_by_sql("SELECT id, nome FROM {$this->table}
                                            WHERE id='$id' LIMIT 1");
        return!empty($result_array) ? array_shift($result_array) : false;
    }

    public function inicialize($record) {
        return $object = new Servico(isset($record['id']) ? $record['id'] : null,
                        isset($record['nome']) ? $record['nome'] : null,
                        isset($record['dataCadastro']) ? $record['dataCadastro'] : null
        );
    }

}

?>
