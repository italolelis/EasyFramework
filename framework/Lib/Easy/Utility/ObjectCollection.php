<?php

/**
 * Deals with Collections of objects.
 *
 * Keeping registries of those objects,
 * loading and constructing new objects and triggering callbacks. Each subclass needs
 * to implement its own load() functionality.
 *
 * All core subclasses of ObjectCollection by convention loaded objects are stored
 * in `$this->_loaded`. Enabled objects are stored in `$this->_enabled`. In addition
 * the all support an `enabled` option that controls the enabled/disabled state of the object
 * when loaded.
 *
 * @package Easy.Utility
 * @since EasyFW v 0.3
 */
App::uses('ICollection', "Utility");

abstract class ObjectCollection implements ICollection {

    /**
     * List of the currently-enabled objects
     * @var array
     */
    protected $_enabled = array();

    /**
     * A hash of loaded objects, indexed by name
     * @var array
     */
    protected $_loaded = array();

    /**
     * Loads a new object onto the collection.
     * Can throw a variety of exceptions
     *
     * Implementations of this class support a `$options['enabled']` flag which enables/disables
     * a loaded object.
     *
     * @param $name string Name of object to load.
     * @param $options array Array of configuration options for the object to be constructed.
     * @return object the constructed object
     */
    abstract public function load($name, $options = array());

    /**
     * Trigger a callback method on every object in the collection.
     * Used to trigger methods on objects in the collection. Will fire the methods in the
     * order they were attached.
     *
     * ### Options
     *
     * - `breakOn` Set to the value or values you want the callback propagation to stop on.
     * Can either be a scalar value, or an array of values to break on. Defaults to `false`.
     *
     * - `break` Set to true to enabled breaking. When a trigger is broken, the last returned value
     * will be returned. If used in combination with `collectReturn` the collected results will be
     * returned.
     * Defaults to `false`.
     *
     * - `collectReturn` Set to true to collect the return of each object into an array.
     * This array of return values will be returned from the trigger() call. Defaults to `false`.
     *
     * - `modParams` Allows each object the callback gets called on to modify the parameters to the
     * next object.
     * Setting modParams to an integer value will allow you to modify the parameter with that index.
     * Any non-null value will modify the parameter index indicated.
     * Defaults to false.
     *
     *
     * @param obj string
     *        Method to fire on all the objects. Its assumed all the objects implement
     *        the method you are calling.
     * @param objrray
     *        Array of parameters for the triggered callback.
     * @param objarray
     *        Array of options.
     * @return mixed Either the last result or all results if collectReturn is on.
     * @throws CakeException when modParams is used with an index that does not exist.
     */
    public function trigger($callback, $params = array(), $options = array()) {
        $options = array_merge(array(
            'break' => false,
            'breakOn' => false,
            'collectReturn' => false,
            'modParams' => false), $options);

        foreach ($this->_loaded as $obj) {
            if (method_exists($obj, $callback)) {
                $obj->{$callback}($params);
            } else {
                trigger_error("O método {$callback} não pode ser chamado na classe {$obj}", E_USER_WARNING);
            }
        }
    }

    /**
     * Provide public read access to the loaded objects
     *
     * @param $name string Name of property to read
     * @return mixed
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * Provide isset access to _loaded
     *
     * @param $name string Name of object being checked.
     * @return boolean
     */
    public function __isset($name) {
        return $this->exists($name);
    }

    /**
     * Enables callbacks on an object or array of objects
     *
     * @param $name mixed CamelCased name of the object(s) to enable (string or array)
     * @return void
     */
    public function enable($name) {
        foreach ((array) $name as $object) {
            if (isset($this->_loaded [$object]) && array_search($object, $this->_enabled) === false) {
                $this->_enabled [] = $object;
            }
        }
    }

    /**
     * Disables callbacks on a object or array of objects.
     * Public object methods are still
     * callable as normal.
     *
     * @param $name mixed CamelCased name of the objects(s) to disable (string or array)
     * @return void
     */
    public function disable($name) {
        foreach ((array) $name as $object) {
            $index = array_search($object, $this->_enabled);
            unset($this->_enabled [$index]);
        }
        $this->_enabled = array_values($this->_enabled);
    }

    /**
     * Gets the list of currently-enabled objects, or, the current status of a single objects
     *
     * @param $name string Optional. The name of the object to check the status of. If omitted,
     *        returns an array of currently-enabled object
     * @return mixed If $name is specified, returns the boolean status of the corresponding object.
     *         Otherwise, returns an array of all enabled objects.
     */
    public function enabled($name = null) {
        if (!empty($name)) {
            return in_array($name, $this->_enabled);
        }
        return $this->_enabled;
    }

    /**
     * Gets the list of attached behaviors, or, whether the given behavior is attached
     *
     * @param $name string Optional. The name of the behavior to check the status of. If omitted,
     *        returns an array of currently-attached behaviors
     * @return mixed If $name is specified, returns the boolean status of the corresponding
     *         behavior.
     *         Otherwise, returns an array of all attached behaviors.
     */
    public function attached($name = null) {
        if (!empty($name)) {
            return isset($this->_loaded [$name]);
        }
        return array_keys($this->_loaded);
    }

    /**
     * Check if exists the element in the list
     * @param string $name
     * @return bool 
     */
    public function exists($name) {
        return isset($this->_loaded [$name]);
    }

    /**
     * Name of the object to remove from the collection
     *
     * @param $name string Name of the object to delete.
     * @return void
     */
    public function remove($name) {
        unset($this->_loaded [$name]);
    }

    /**
     * Adds or overwrites an instantiated object to the collection
     *
     * @param $name string Name of the object
     * @param $object Object The object to use
     * @return array Loaded objects
     */
    public function add($name, $object) {
        if (!empty($name) && !empty($object)) {
            $this->_loaded [$name] = $object;
        }
        return $this->_loaded[$name];
    }

    /**
     * Add many elements to the list
     * @param array $values An associative array os elements
     * @return The elements list 
     */
    public function addRange($values) {
        foreach ($values as $key => $value) {
            $this->add($key, $value);
        }

        return $this->_loaded;
    }

    /**
     * Count the elements in the list
     */
    public function count() {
        return count($this->_loaded);
    }

    /**
     * Get an element based in the key
     * @param string $key The name of the key
     * @return The element object, or null if didn't find it 
     */
    public function get($key) {
        if (isset($this->_loaded [$key])) {
            return $this->_loaded [$key];
        } else {
            return null;
        }
    }

    /**
     * Normalizes an object array, creates an array that makes lazy loading
     * easier
     *
     * @param $objects array Array of child objects to normalize.
     * @return array Array of normalized objects.
     */
    public static function normalizeObjectArray($objects) {
        $normal = array();
        foreach ($objects as $i => $objectName) {
            $options = array();
            if (!is_int($i)) {
                $options = (array) $objectName;
                $objectName = $i;
            }
            $normal [$name] = array('class' => $objectName,
                'settings' => $options);
        }
        return $normal;
    }

}