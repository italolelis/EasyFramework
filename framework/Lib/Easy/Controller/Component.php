<?php

/**
 * EasyFramework : Rapid Development Framework
 * Copyright 2011, EasyFramework (http://easyframework.org.br)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011, EasyFramework (http://easyframework.org.br)
 * @since         EasyFramework v 0.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Base class for an individual Component.  Components provide reusable bits of
 * controller logic that can be composed into a controller.  Components also
 * provide request life-cycle callbacks for injecting logic at specific points.
 *
 * ## Life cycle callbacks
 *
 * Components can provide several callbacks that are fired at various stages of the request
 * cycle.  The available callbacks are:
 *
 * - `initialize()` - Fired before the controller's beforeFilter method.
 * - `startup()` - Fired after the controller's beforeFilter method.
 * - `beforeRender()` - Fired before the view + layout are rendered.
 * - `shutdown()` - Fired after the action is complete and the view has been rendered
 *    but before Controller::afterFilter().
 * - `beforeRedirect()` - Fired before a redirect() is done.
 *
 * @package       Easy.Controller
 * @see Controller::$components
 */
class Component {

    protected $controller = null;

    /**
     * Called before the Controller::beforeFilter().
     *
     * @param Controller $controller Controller with components to initialize
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::initialize
     */
    public function initialize(Controller $controller) {
        return $this->controller = $controller;
    }

    /**
     * Called after the Controller::beforeFilter() and before the controller action
     *
     * @param Controller $controller Controller with components to startup
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::startup
     */
    public function startup(Controller $controller) {
        return $this->controller = $controller;
    }

    /**
     * Called before the Controller::beforeRender(), and before 
     * the view class is loaded, and before Controller::render()
     *
     * @param Controller $controller Controller with components to beforeRender
     * @return void
     * @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRender
     */
    public function beforeRender(Controller $controller) {
        return $this->controller = $controller;
    }

    /**
     * Called after Controller::render() and before the output is printed to the browser.
     *
     * @param Controller $controller Controller with components to shutdown
     * @return void
     * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::shutdown
     */
    public function shutdown(Controller $controller) {
        return $this->controller = $controller;
    }

    /**
     * Called before Controller::redirect().  Allows you to replace the url that will
     * be redirected to with a new url. The return of this method can either be an array or a string.
     *
     * If the return is an array and contains a 'url' key.  You may also supply the following:
     *
     * - `status` The status code for the redirect
     * - `exit` Whether or not the redirect should exit.
     *
     * If your response is a string or an array that does not contain a 'url' key it will
     * be used as the new url to redirect to.
     *
     * @param Controller $controller Controller with components to beforeRedirect
     * @param string|array $url Either the string or url array that is being redirected to.
     * @param integer $status The status code of the redirect
     * @param boolean $exit Will the script exit.
     * @return array|null Either an array or null.
     * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRedirect
     */
    public function beforeRedirect(Controller $controller, $url, $status = null, $exit = true) {
        return $this->controller = $controller;
    }

}