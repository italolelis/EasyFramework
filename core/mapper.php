<?php

/**
 *  Mapper é o responsável por cuidar de URLs e roteamento dentro do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
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
    public $params;

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

    public function __construct($url = null) {
        if ($url != null) {
            if (is_null($this->base)) {
                $this->base = dirname($_SERVER["PHP_SELF"]);
                while (in_array(basename($this->base), array("app", "webroot"))) {
                    $this->base = dirname($this->base);
                }
                if ($this->base == DS || $this->base == ".") {
                    $this->base = "/";
                }
            }
            if (is_null($this->here)) {
                $start = strlen($this->base);
                $this->here = str_replace("/", "", substr($url, $start));
            }
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
    function parse() {
        //Se existir alguma coisa na url atual
        if (!empty($this->here)) {
            //Mostamos um array com os parametros passados na URL que são separados por "-"
            $parts = explode("-", $this->here);
            $this->params['controller'] = isset($parts[0]) ? urldecode($parts[0]) : self::getRoot();
            $this->params['action'] = isset($parts[1]) ? urldecode($parts[1]) : 'index';
            if (count($parts) > 1) {
                for ($i = 2; $i <= count($parts); $i++) {
                    $this->params["params"][] = isset($parts[$i]) ? urldecode($parts[$i]) : null;                //Pega a id
                }
            } else {
                $this->params["params"][] = null;
            }
        } else {
            $this->params['controller'] = self::getRoot();          //Pega o nome do controller
            $this->params['action'] = 'index';                      //Pega a ação
            $this->params["params"] = array();
        }

        return $this->params;
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
