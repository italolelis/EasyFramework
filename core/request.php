<?php

class Request extends Object {

    /**
     *  URL base da aplicação.
     */
    private $base = null;

    /**
     *  URL atual da aplicação.
     */
    private $here = null;

    /**
     * Parâmetros da url
     * @var array 
     */
    private $params;

    public function getParams() {
        return $this->params;
    }

    public function __construct($url) {
        $this->base = dirname(dirname(dirname($_SERVER["PHP_SELF"])));
        if ($this->base == DS || $this->base == ".") {
            $$this->base = "/";
        }

        $start = strlen($this->base);
        $this->here = str_replace("/", "", substr($url, $start));

        $this->parse();
    }

    /**
     *  Faz a interpretação da URL, identificando as partes da URL.
     * 
     *  @return array URL interpretada
     */
    private function parse() {
        //Se existir alguma coisa na url atual
        if ($this->here) {
            //Mostamos um array com os parametros passados na URL que são separados por "-"
            $part = explode("-", $this->here);

            $this->params['controller'] = isset($part[0]) ? $part[0] : 'site';      //Pega o nome do controller
            $this->params['action'] = isset($part[1]) ? $part[1] : 'index';         //Pega a ação
            $this->params['id'] = isset($part[2]) ? $part[2] : null;                //Pega a id
        } else {
            $this->params['controller'] = 'site';       //Pega o nome do controller
            $this->params['action'] = 'index';          //Pega a ação
        }
    }

}

?>
