<?php

App::import('Lib', 'easy_email');

class Email {

    private $nome, $email, $telefone, $dataAgendamento, $hora, $servicos, $data;

    public function getNome() {
        return $this->nome;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getTelefone() {
        return $this->telefone;
    }

    public function getDataAgendamento() {
        return $this->dataAgendamento;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getServicos() {
        return $this->servicos;
    }

    public function getData() {
        return $this->data;
    }

    function __construct($values) {
        $this->nome = $values['nome'];
        $this->email = $values['email'];
        $this->telefone = $values['telefone'];
        $this->dataAgendamento = $values['dataAgendamento'];
        $this->hora = $values['Cbohora'];
        $this->servicos = $values['servicos']; //Array de serviços
        $this->data = date("d/m/y");
    }

    function SendEmail() {

        $assunto = "Solicitação de Agendamento";

        $msg = "<html>
        <body>
        <h3>Requisição de agendamento</h3> <br />
        <strong>Nome: </strong>$this->nome<br />
        <strong>E-mail: </strong>$this->email<br />
        <strong>Telefone: </strong>$this->telefone<br /><br />
        <strong>Data da Requisição: </strong>$this->data<br />
        <strong>Data desejada: </strong>$this->dataAgendamento<br />
        <strong>Horário desejado: </strong>$this->hora<br />
        <strong>Serviços: </strong>" . implode(", ", $this->servicos) . "
        </body>
        </html>";

        $siteMail = "contato@joaopaulohairdesign.com";  //O e-mail que aparecerá na caixa postal do visitante

        $easy_email = new Easy_Email($siteMail, $siteMail);
        $easy_email->sendEmail($assunto, $msg);

        //Aqui são as configurações para enviar o e-mail para o visitante
        $assunto = "Contato João Paulo Hair Design";   //Titulo da mensagem enviada para o visitante
        $msg = "<html>
        <body>
        <h3>Contato João Paulo Hair Design</h3> <br />
        Presado $this->nome, obrigado por entrar em contato conosco, em breve entraremos em contato.
        </body>
        </html>"; //Menssagem que irá aparecer para o visitante

        $easy_email = new Easy_Email($this->email, $siteMail);
        $easy_email->sendEmail($assunto, $msg);

        return true;
    }

}

?>
