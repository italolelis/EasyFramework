<?php

/**
 * Description of Usuario
 *
 * @author Ítalo Lelis de Vietro
 */
class Usuario {

    private $id, $username, $password, $admin, $nome, $endereco, $complemento, $bairro, $cidade, $tel, $cel, $email;

    function __construct($id = null, $username = null, $password = null, $admin = null, $nome = null, $endereco = null, $complemento = null, $bairro = null, $cidade = null, $tel = null, $cel = null, $email = null) {
        $this->id = $id;
        $this->username = $username;
        if ($password != null) {
            if ($this->isValidPassword($password)) {
                $this->setPassword($password);
            } else {
                throw new invalidPasswordException('Senha inválida');
            }
        }
        $this->admin = $admin;
        $this->nome = $nome;
        $this->endereco = $endereco;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->tel = $tel;
        $this->cel = $cel;
        $this->email = $email;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = Security::hash($password, 'md5');
    }

    public function getAdmin() {
        return $this->admin;
    }

    public function setAdmin($admin) {
        $this->admin = $admin;
    }

    public function getNome() {
        return $this->nome;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function getEndereco() {
        return $this->endereco;
    }

    public function setEndereco($endereco) {
        $this->endereco = $endereco;
    }

    public function getComplemento() {
        return $this->complemento;
    }

    public function setComplemento($complemento) {
        $this->complemento = $complemento;
    }

    public function getBairro() {
        return $this->bairro;
    }

    public function setBairro($bairro) {
        $this->bairro = $bairro;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function setCidade($cidade) {
        $this->cidade = $cidade;
    }

    public function getTel() {
        return $this->tel;
    }

    public function setTel($tel) {
        $this->tel = $tel;
    }

    public function getCel() {
        return $this->cel;
    }

    public function setCel($cel) {
        $this->cel = $cel;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Verify if the password is valid
     * @param <string> $pass
     * @return <bool> true if the password is valid, false if is invalid
     */
    function isValidPassword($pass) {
        $lenght = 6;

        if (strlen($pass) >= $lenght && !empty($pass))
            return true;
        else
            return false;
    }

}

?>