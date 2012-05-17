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
 * @since EasyFW v 0.3
 */
App::uses('Collection', "Collections");

/**
 * Manage Objects collections
 * 
 * @package Easy.Collections.Generic
 */
abstract class ObjectCollection extends Collection {

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

        if ($callback instanceof Event) {
            $event = $callback;
            if (is_array($event->data)) {
                $params = &$event->data;
            }
            if (empty($event->omitSubject)) {
                $params = &$event->subject();
            }
            //TODO: Temporary BC check, while we move all the triggers system into the CakeEventManager
            foreach (array('break', 'breakOn', 'collectReturn', 'modParams') as $opt) {
                if (isset($event->{$opt})) {
                    $options[$opt] = $event->{$opt};
                }
            }
            $parts = explode('.', $event->name());
            $callback = array_pop($parts);
        }

        $options = array_merge(array(
            'break' => false,
            'breakOn' => false,
            'collectReturn' => false,
            'modParams' => false), $options);

        foreach ($this->data as $obj) {
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
            return isset($this->data [$name]);
        }
        return array_keys($this->data);
    }

    /**
     * Check if exists the element in the list
     * @param string $name
     * @return bool 
     */
    public function exists($name) {
        return isset($this->data [$name]);
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