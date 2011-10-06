<?php

class UsuarioDAO extends ModelDAO {

    /**
     * User object
     * @var Usuario 
     */
    private $usuario;

    /**
     * Inicialize a new object of type UsuariosDAO
     * @param <Usuario> $user 
     */
    function __construct(Usuario $user = null) {
        parent::__construct('usuarios');
        if ($user != null)
            $this->usuario = $user;
    }

    /**
     * Include a user
     * 
     */
    function add() {
        $sql = "INSERT INTO {$this->table} (username, password, nome, email, tel) 
                    VALUES('{$this->usuario->getUsername()}', '{$this->usuario->getPassword()}', '{$this->usuario->getNome()}', 
                           '{$this->usuario->getEmail()}','{$this->usuario->getTel()}')";
        return $this->query($sql);
    }

    function adminAdd() {
        $sql = "INSERT INTO {$this->table} (username, password, admin, nome, email) 
                    VALUES('{$this->usuario->getUsername()}', '{$this->usuario->getPassword()}', '{$this->usuario->getAdmin()}',
                           '{$this->usuario->getNome()}', '{$this->usuario->getEmail()}')";
        return $this->query($sql);
    }

    /**
     * Alter a user
     * 
     */
    function update() {
        $sql = "UPDATE {$this->table} SET 
                    username='{$this->usuario->getUsername()}', 
                    nome='{$this->usuario->getNome()}', 
                    email='{$this->usuario->getEmail()}',
                    endereco='{$this->usuario->getEndereco()}', 
                    complemento='{$this->usuario->getComplemento()}',
                    bairro='{$this->usuario->getBairro()}',
                    cidade='{$this->usuario->getCidade()}',
                    tel='{$this->usuario->getTel()}',
                    cel='{$this->usuario->getCel()}'
                WHERE `id`='{$this->usuario->getId()}'";
        return $this->query($sql);
    }

    function adminUpdate() {
        $sql = "UPDATE {$this->table} SET 
                    username='{$this->usuario->getUsername()}', 
                    admin='{$this->usuario->getAdmin()}',
                    nome='{$this->usuario->getNome()}', 
                    email='{$this->usuario->getEmail()}',
                    endereco='{$this->usuario->getEndereco()}', 
                    complemento='{$this->usuario->getComplemento()}',
                    bairro='{$this->usuario->getBairro()}',
                    cidade='{$this->usuario->getCidade()}',
                    tel='{$this->usuario->getTel()}',
                    cel='{$this->usuario->getCel()}'
                WHERE `id`='{$this->usuario->getId()}'";
        return $this->query($sql);
    }

    /**
     * Delete a user
     * 
     */
    function delete() {
        $sql = "DELETE FROM {$this->table} 
                WHERE id='{$this->usuario->getId()}'";
        return $this->query($sql);
    }

    /**
     * Alter the password
     * 
     */
    function alterarSenha() {
        $sql = "UPDATE {$this->table} SET password='{$this->usuario->getPassword()}'
                WHERE `id`='{$this->usuario->getId()}'";

        return $this->query($sql);
    }

    /**
     * Obtêm um usuário do banco de dados
     * @param type $id
     * @return type 
     */
    function getById($id = 0) {
        $result_array = $this->find_by_sql("SELECT usuarios.id, usuarios.username, usuarios.nome, usuarios.endereco, usuarios.complemento, usuarios.bairro, 
                                                   usuarios.cidade, usuarios.tel, usuarios.cel, usuarios.email, usuarios.admin
                                            FROM {$this->table} 
                                            WHERE usuarios.id = $id LIMIT 1");
        return!empty($result_array) ? array_shift($result_array) : false;
    }

    /**
     * Obtêm uma lista de usuários
     * @return list
     */
    function getAll() {
        $sql = "SELECT usuarios.id, usuarios.nome, usuarios.username, usuarios.admin
                From {$this->table}";

        return $this->find_by_sql($sql);
    }

    public function inicialize($record) {

        return new Usuario(isset($record['id']) ? $record['id'] : null,
                        isset($record['username']) ? $record['username'] : null,
                        isset($record['password']) ? $record['password'] : null,
                        isset($record['admin']) ? $record['admin'] : null,
                        isset($record['nome']) ? $record['nome'] : null,
                        isset($record['endereco']) ? $record['endereco'] : null,
                        isset($record['complemento']) ? $record['complemento'] : null,
                        isset($record['bairro']) ? $record['bairro'] : null,
                        isset($record['cidade']) ? $record['cidade'] : null,
                        isset($record['tel']) ? $record['tel'] : null,
                        isset($record['cel']) ? $record['cel'] : null,
                        isset($record['email']) ? $record['email'] : null
        );
    }

}

?>
