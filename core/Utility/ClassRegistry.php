<?php

/**
 *  ClassRegistry faz o registro e gerenciamento de instâncias das classes utilizadas
 *  pelo EasyFramework, evitando a criação de várias instâncias de uma mesma classe.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
class ClassRegistry {

    /**
     *  Nome das classes a serem utilizados pelo EasyFramework
     */
    public $objects = array();

    /**
     * Names of class names mapped to the object in the registry.
     *
     * @var array
     */
    protected $_map = array();
    protected static $instance;

    public static function instance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    /**
     *  Carrega a classe, registrando o objeto, retornando uma instância
     *  para a mesma.
     *
     *  @param string $class Classe a ser inicializada
     *  @param string $type Tipo da classe
     *  @return object Instância da classe
     */
    public static function &load($class, $type = "Model") {
        $self = self::instance();
        if ($object = & $self->duplicate($class, $class)):
            return $object;
        elseif (!class_exists($class)):
            App::import($type, Inflector::underscore($class));
        endif;
        if (class_exists($class)):
            ${$class} = new $class;
        endif;
        return ${$class};
    }

    /**
     * Add $object to the registry, associating it with the name $key.
     *
     * @param string $key	Key for the object in registry
     * @param mixed $object	Object to store
     * @return boolean True if the object was written, false if $key already exists
     */
    public static function addObject($key, $object) {
        $_this = self::instance();
        $key = Inflector::underscore($key);
        if (!isset($_this->objects[$key])) {
            $_this->objects[$key] = $object;
            return true;
        }
        return false;
    }

    /**
     * Remove object which corresponds to given key.
     *
     * @param string $key	Key of object to remove from registry
     * @return void
     */
    public static function removeObject($key) {
        $_this = self::instance();
        $key = Inflector::underscore($key);
        if (isset($_this->objects[$key])) {
            unset($_this->objects[$key]);
        }
    }

    /**
     * Returns true if given key is present in the ClassRegistry.
     *
     * @param string $key Key to look for
     * @return boolean true if key exists in registry, false otherwise
     */
    public static function isKeySet($key) {
        $_this = self::instance();
        $key = Inflector::underscore($key);
        if (isset($_this->objects[$key])) {
            return true;
        } elseif (isset($_this->_map[$key])) {
            return true;
        }
        return false;
    }

    /**
     * Get all keys from the registry.
     *
     * @return array Set of keys stored in registry
     */
    public static function keys() {
        $_this = self::instance();
        return array_keys($_this->objects);
    }

    /**
     * Return object which corresponds to given key.
     *
     * @param string $key Key of object to look for
     * @return mixed Object stored in registry or boolean false if the object does not exist.
     */
    public static function &getObject($key) {
        $_this = self::instance();
        $key = Inflector::underscore($key);
        $return = false;
        if (isset($_this->objects[$key])) {
            $return = $_this->objects[$key];
        } else {
            $key = $_this->_getMap($key);
            if (isset($_this->objects[$key])) {
                $return = $_this->objects[$key];
            }
        }
        return $return;
    }

    /**
     *  Retorna uma cópia de uma instância já registrada.
     * 
     *  @param string $key
     *  @param object $class
     *  @return mixed
     */
    public static function &duplicate($key, $class) {
        $self = self::instance();
        $duplicate = false;
        if (self::isKeySet($key)):
            $object = & self::getObject($key);
            if ($object instanceof $class):
                $duplicate = & $object;
            endif;
            unset($object);
        endif;
        return $duplicate;
    }

    /**
     * Get all keys from the map in the registry.
     *
     * @return array Keys of registry's map
     */
    public static function mapKeys() {
        $_this = self::instance();
        return array_keys($_this->_map);
    }

    /**
     * Return the name of a class in the registry.
     *
     * @param string $key Key to find in map
     * @return string Mapped value
     */
    protected function _getMap($key) {
        if (isset($this->_map[$key])) {
            return $this->_map[$key];
        }
    }

    /**
     * Flushes all objects from the ClassRegistry.
     *
     * @return void
     */
    public static function flush() {
        $_this = self::instance();
        $_this->objects = array();
        $_this->_map = array();
    }

}

?>