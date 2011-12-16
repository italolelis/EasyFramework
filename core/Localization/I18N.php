<?php

/**
 * Classe simples para internacionalizar sua aplicação em diversas linguagens, para cada linguagem existirá um arquivo Xml contendo as traduções.
 * O script é muito simples(porém eficiente), ele lê o arquivo Xml de tradução, faz um loop e adiciona no array($arrayLabel) os índices(atributo <name>) e seus valores(atributo <value>).
 * Através desse índice ele retorna a tradução da linguagem selecionada.
 * O padrão de nomenclatura dos arquivos Xmls serão esses:
 *    phpi18n.xml -> Linguagem padrão
 *   phpi18n_xx_XX.xml -> Ondes xxXX são a abreviação da linguagem e país.
 * Para maiores detalhes sobre abreviaturas dos países acessem(http://ftp.ics.uci.edu/pub/ietf/http/related/iso639.txt, http://userpage.chemie.fu-berlin.de/diverse/doc/ISO_3166.html, http://www.iso.org/iso/en/prods-services/iso3166ma/index.html).
 * Autor: Rodrigo Rodrigues
 * Email: web-rodrigo@bol.com.br
 * Versão: 1
 * IMPORTANTE: PRECISA TER INSTALADO O PHP 5 PORQ USA O COMPONENTE SimpleXml(http://br.php.net/manual/pt_BR/ref.simplexml.php).
 */
class PhpI18N {

    /**
     * Variável Array privada com os valores da tradução.
     */
    private $arrayLabel = array();

    /**
     * Variável privada com o nome da linguagem.
     */
    private $language;

    /**
     * Variável privada com o nome do país.
     */
    private $country;

    /**
     * Variável privada com o nome do arquivo Xml de tradução.
     */
    private $xml = null;
    protected static $instance;

    public static function instance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }
        return self::$instance;
    }

    /**
     * Método para carregar o xml da linguagem selecionada.
     */
    private function loadXml($xml) {
        if (!App::path("Languages", $xml, "xml")) {
            $xml = "phpi18n"; // Language Default
        }

        $simpleXml = @simplexml_load_file(App::path("Languages", $xml, "xml"));

        if ($simpleXml) {
            foreach ($simpleXml->label as $loadLabel) {
                $this->arrayLabel["$loadLabel->name"] = $loadLabel->value;
            }
        }
    }

    /**
     * Método que retorna o nome do arquivo Xml.
     */
    public function getXml() {
        return $this->xml;
    }

    public function setLocale($locale) {
        $arrayLocale = explode("_", $locale);
        $this->language = $arrayLocale[0];
        $this->country = $arrayLocale[1];

        $this->xml = $this->language . "_" . $this->country;
        $this->loadXml($this->xml);
    }

    public function getLocale() {
        return $this->language . "_" . $this->country;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

    public function getCountry() {
        return $this->country;
    }

    public function setCountry($country) {
        $this->country = $country;
    }

    /**
     * Método que retorna o valor da tradução.
     */
    public function getLabel($keyName) {
        return $this->label($keyName);
    }

    /**
     * Alias to getLabel function
     * @param string $keyName
     * @return string 
     */
    public function _t($keyName) {
        return $this->getLabel($keyName);
    }

    /**
     * Método privado que verifica se o parâmetro existe na chave(índice) do array, caso exista retorna seu valor.
     */
    private function label($keyName) {
        return array_key_exists($keyName, $this->arrayLabel) ? $this->arrayLabel[$keyName] : "empty";
    }

    /**
     * Método para destruir o array de tradução.
     */
    function __destruct() {
        unset($this->arrayLabel);
    }

}

?>
