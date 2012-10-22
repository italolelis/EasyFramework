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

namespace Easy\Controller;

use Easy\Collections\Generic\ObjectCollection;
use Easy\Controller\Controller;
use Easy\Event\EventListener;
use Easy\Utility\Inflector;

/**
 * Components collection is used as a registry for loaded components and handles loading
 * and constructing component class objects.
 *
 * @package       Easy.Controller
 */
class ComponentCollection extends ObjectCollection implements EventListener
{

    protected $controller = null;
    protected $factory;

    public function __construct()
    {
        $this->factory = new ComponentFactory();
    }

    /**
     * Get the controller associated with the collection.
     *
     * @return Controller.
     */
    public function getController()
    {
        return $this->controller;
    }

    public function init(Controller &$controller)
    {
        if ($controller->requiredComponents->IsEmpty()) {
            return;
        }
        $this->controller = $controller;
        foreach ($controller->requiredComponents as $name => $options) {
            $controller->{$name} = $this->load($name, $options);
        }
    }

    /**
     * Carrega todos os componentes associados ao controller.
     * @return boolean Verdadeiro se todos os componentes foram carregados
     */
    public function load($component, $options = array())
    {
        $component = Inflector::camelize($component);
        $this->add($component, $this->factory->create($component, $options, $this));
        return $this->offsetGet($component);
    }

    /**
     * Returns the implemented events that will get routed to the trigger function
     * in order to dispatch them separately on each component
     *
     * @return array
     */
    public function implementedEvents()
    {
        return array(
            'Controller.initialize' => array('callable' => 'trigger'),
            'Controller.startup' => array('callable' => 'trigger'),
            'Controller.beforeRender' => array('callable' => 'trigger'),
            'Controller.beforeRedirect' => array('callable' => 'trigger'),
            'Controller.shutdown' => array('callable' => 'trigger'),
        );
    }

}