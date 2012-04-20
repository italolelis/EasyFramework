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
     *  Callback executado antes de qualquer ação do controller.
     *
     *  @param object $controller
     *  @return true
     */
    public function initialize(Controller &$controller) {
        return $this->controller = $controller;
    }

    /**
     *  Callback executado após Controller::beforeFilter.
     *
     *  @param object $controller
     *  @return true
     */
    public function startup(Controller &$controller) {
        return $this->controller = $controller;
    }

    /**
     *  Callback executado após todas as ações do controller, mas antes de enviar
     *  a saída renderizada.
     *
     *  @param object $controller
     *  @return true
     */
    public function shutdown(Controller &$controller) {
        return $this->controller = $controller;
    }

}