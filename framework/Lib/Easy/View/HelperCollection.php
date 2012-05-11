<?php

/**
 * Helpers collection is used as a registry for loaded helpers and handles loading
 * and constructing helper class objects.
 * 
 * FROM CAKEPHP
 * 
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('ObjectCollection', 'Collections/Generic');
App::uses('Helper', 'View');
App::uses('AppHelper', 'Helper');

class HelperCollection extends ObjectCollection {

    /**
     * View object to use when making helpers. 
     *
     * @var View
     */
    protected $view;

    /**
     * Constructor
     *
     * @param $view View       	
     */
    public function __construct(View $view) {
        $this->view = $view;
    }

    public function init(Controller $controller) {
        if (empty($controller->helpers)) {
            return;
        }
        foreach ($controller->helpers as $name) {
            $this->view->set($name, $this->load($name));
        }
    }

    /**
     * Tries to lazy load a helper based on its name, if it cannot be found
     * in the application folder, then it tries looking under the current plugin
     * if any
     *
     * @param string $helper The helper name to be loaded
     * @return boolean wheter the helper could be loaded or not
     * @throws MissingHelperException When a helper could not be found.
     *    App helpers are searched, and then plugin helpers.
     */
    public function __isset($helper) {
        if (parent::__isset($helper)) {
            return true;
        }

        $this->load($helper);

        return true;
    }

    /**
     * Loads/constructs a helper.
     * Will return the instance in the registry if it already exists.
     * By setting `$enable` to false you can disable callbacks for a helper. Alternatively you
     * can set `$settings['enabled'] = false` to disable callbacks. This alias is provided so that
     * when
     * declaring $helpers arrays you can disable callbacks on helpers.
     *
     * You can alias your helper as an existing helper by setting the 'className' key, i.e.,
     * {{{
     * public $helpers = array(
     * 'Html' => array(
     * 'className' => 'AliasedHtml'
     * );
     * );
     * }}}
     * All calls to the `Html` helper would use `AliasedHtml` instead.
     *
     * @param $helper string
     *       	 Helper name to load
     * @param $settings array
     *       	 Settings for the helper.
     * @return Helper A helper object, Either the existing loaded helper or a new one.
     * @throws MissingHelperException when the helper could not be found
     */
    public function load($helper, $settings = array()) {
        $class = Inflector::camelize($helper . "Helper");

        if (!class_exists($class) && App::path("Helper", $class)) {
            App::uses($class, "Helper");
            $this->data [$helper] = new $class($this->view);
            return $this->data [$helper];
        } else {
            throw new MissingHelperException(null, array(
                'helper' => $helper,
                'controller' => $this->view->getController()->getName(),
                'title' => 'Helper class not found.'
            ));
        }
    }

}