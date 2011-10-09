<?php

/**
 *  Mapper é o responsável por cuidar de URLs e roteamento dentro do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework
 *
 */
class Mapper extends Object {

    /**
     *  URL base da aplicação.
     */
    private $base = null;

    /**
     *  URL atual da aplicação.
     */
    private $here = null;

    /**
     *  Controller padrÃ£o da aplicaÃ§Ã£o.
     */
    public $root = null;

    /**
     * Parâmetros da url
     * @var array 
     */
    private $params;

    /**
     *  Define o controller padrÃ£o da aplicaÃ§Ã£o.
     *
     *  @param string $controller Controller a ser definido como padrÃ£o
     *  @return true
     */
    public static function root($controller) {
        $self = self::getInstance();
        $self->root = $controller;
        return true;
    }

    /**
     *  Getter para Mapper::here.
     *
     *  @return string Valor de Mapper:here
     */
    public static function here() {
        $self = self::getInstance();
        return $self->here;
    }

    /**
     *  Getter para Mapper::here.
     *
     *  @return string Valor de Mapper:here
     */
    public static function atual() {
        return str_replace("/", "", str_replace(basename(APP_FOLDER), "", $_SERVER['REQUEST_URI']));
    }

    /**
     *  Getter para Mapper::root
     *
     *  @return string Controller padrÃ£o da aplicaÃ§Ã£o
     */
    public static function getRoot() {
        $self = self::getInstance();
        return $self->root;
    }

    public function getParams() {
        return $this->params;
    }

    public function __construct($url = null) {
        if ($url != null) {
            if (is_null($this->base)) {
                $this->base = dirname($_SERVER["PHP_SELF"]);
                while (in_array(basename($this->base), array("app", "webroot"))) {
                    $this->base = dirname($this->base);
                }
                if ($this->base == DS || $this->base == ".") {
                    $$this->base = "/";
                }
            }
            if (is_null($this->here)) {
                $start = strlen($this->base);
                $this->here = str_replace("/", "", substr($url, $start));
            }
            $this->parse();
        }
    }

    public static function &getInstance() {
        static $instance = array();
        if (!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Mapper();
        endif;
        return $instance[0];
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
            $this->params['controller'] = self::getRoot();       //Pega o nome do controller
            $this->params['action'] = 'index';          //Pega a ação
        }
    }

    /**
     *  Normaliza uma URL, removendo barras duplicadas ou no final de strings e
     *  adicionando uma barra inicial quando necessário.
     *
     *  @param string $url URL a ser normalizada
     *  @return string URL normalizada
     */
    public static function normalize($url) {
        if (preg_match("/^[a-z]+:/", $url)):
            return $url;
        endif;
        $url = "/" . $url;
        while (strpos($url, "//") !== false):
            $url = str_replace("//", "/", $url);
        endwhile;
        $url = rtrim($url, "/");
        if (empty($url)):
            $url = "/";
        endif;
        return $url;
    }

}

?>
