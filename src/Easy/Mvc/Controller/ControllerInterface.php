<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.easyframework.net>.
 */

namespace Easy\Mvc\Controller;

use Easy\Configure\IConfiguration;
use Easy\HttpKernel\Kernel;
use Easy\Mvc\Model\ORM\EntityManager;
use Easy\Network\Request;
use Easy\Network\Response;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * ControllerInterface should be implemented by classes that has a controller behaviour.
 *
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
interface ControllerInterface
{

    /**
     * Sets the kernel that handles the controller
     * @param Kernel $kernel
     */
    public function setKernel(Kernel $kernel);

    /**
     * Gets the kernel that handles the controller
     * @return IConfiguration
     */
    public function getKernel();

    /**
     * Gets the EntityManager for this model
     * @return EntityManager 
     */
    public function getEntityManager();

    /**
     * Returns the EventManager manager instance that is handling any callbacks
     * @return EventDispatcher
     */
    public function getEventDispatcher();

    /**
     * Sets the request object
     * @param Request $request
     */
    public function setRequest(Request $request);

    /**
     * Gets auto render mode
     * @return bool
     */
    public function getAutoRender();

    /**
     * Sets auto render mode
     * @param bool $autoRender
     */
    public function setAutoRender($autoRender);

    /**
     * Gets the IContainer object
     * @return ContainerBuilder
     */
    public function getContainer();

    /**
     * Sets the IContainer object
     * @param ContainerBuilder $container
     */
    public function setContainer(ContainerBuilder $container);

    /**
     * Gets the Request object
     * @return Request
     */
    public function getRequest();

    /**
     * Retrieve the controller's name
     * @return string
     */
    public function getName();

    /**
     * Sets a value to be sent to the view. It is not commonly used abandoned in favor of <Controller::__set>, which is much more convenient and readable. Use this only if you need extra performance.
     *
     * @param string $key name of the variable to be sent to the view. Can also be an array where the keys are the name of the variables. In this case, $value will be ignored.
     * @param mixed $value value to be sent to the view.
     */
    public function set($key, $value = null);

    /**
     * Instantiates the correct view class, hands it its data, and uses it to render the view output.
     *
     * @param string $action The view name
     * @param string $controller The controller name
     * @param string $layout The layout to render
     * @param boolean $output If the result should be outputed
     * @return Response
     */
    public function display($action, $controller = true, $layout = null, $output = true);

    /**
     * Internally redirects one action to another.
     * Does not perform another HTTP request unlike Controller::redirect()
     * 
     * @param string $action string The new action to be 'redirected' to
     * @param mixed Any other parameters passed to this method will be passed as parameters to the new action.
     * @return mixed Returns the return value of the called action
     */
    public function forward($action);

    /**
     * Redirects the user to another location.
     *
     * @param string $url Location to be redirected to.
     * @param int $status HTTP status code to be sent with the redirect header.
     * @param bool $exit If true, stops the execution of the controller.
     */
    public function redirect($url, $status = 302);

    /**
     * Redirect to a specific action
     * 
     * @param string $actionName The action's name
     * @param string $controllerName The controller's name
     * @param string $params Parameters to send to action
     * @return void
     * @throws LogicException If Url component doesn't exists.
     */
    public function redirectToAction($actionName, $controllerName = true, $params = null);

    /**
     * Called before the controller action.
     * You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @return void
     */
    public function beforeFilter();

    /**
     * Called after the controller action is run, but before the view is
     * rendered.
     * You can use this method
     * to perform logic or set view variables that are required on every
     * request.
     *
     * @return void
     */
    public function beforeRender();

    /**
     * Called after the controller action is run and rendered.
     *
     * @return void
     */
    public function afterFilter();
}