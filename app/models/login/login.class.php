<?php

class Login extends ModelDAO {

    private $user;
    private $pass;

    function __construct($user, $pass) {
        parent::__construct('usuarios');
        $this->user = $user;
        $this->pass = Security::hash($pass, 'md5'); //criptografamos a senha para comparação no banco
    }

    public function RealizarLogin() {

        $sql = "SELECT `usuarios`.id, usuarios.username, usuarios.admin
                FROM `usuarios` 
                WHERE `username` = '{$this->user}' AND `password` = '{$this->pass}' LIMIT 1";

        $this->query($sql);
        $row = $this->fetch_assoc();

        if ($row) {
            $reg = array(
                'id' => $row['id'],
                'usuario' => $row['username'],
                'admin' => $this->isAdmin($row['admin']),
            );

            Session::write('usuarios', $reg);
        } else {
            throw new invalidLoginException("Usuário e senha incorretos");
        }
    }

    private static function isAdmin($comparer) {
        return $comparer === '1' ? true : false;
    }

    public static function RealizarLogoff() {
        // Remove as variáveis da sessão (caso elas existam)
        Session::delete('usuarios');
        Session::destroy();
    }

    function inicialize($result) {
        return;
    }

}

?>
