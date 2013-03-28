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

namespace Easy\Mvc\View\Engine;

use Easy\Core\Config;
use Easy\HttpKernel\Controller\ControllerResolverInterface;
use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\Controller\Controller;
use Easy\Mvc\Controller\Metadata\ControllerMetadata;
use Easy\Mvc\View\Engine\EngineInterface;
use Easy\Network\Request;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @since 0.2
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
abstract class Engine implements EngineInterface
{

    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var Request 
     */
    protected $request;

    /**
     * @var KernelInterface 
     */
    protected $kernel;
    protected $bundle;
    protected $config;
    protected $metadata;
    protected $layout = 'Layout';
    protected $options;

    /**
     * Initializes a new instance of the EngineInterface.
     * @param Controller $controller The controller to be associated with the view
     * @param array $options The options
     */
    public function __construct(KernelInterface $kernel, ControllerResolverInterface $resolver, $options = array())
    {
        $this->kernel = $kernel;
        $this->bundle = $this->kernel->getActiveBundle();
        $this->request = $this->kernel->getRequest();
        $this->container = $this->kernel->getContainer();

        $this->metadata = new ControllerMetadata($resolver->createControllerClass($this->request, $kernel));

        $this->options = $options;
        $this->config = Config::read("View");
        // Build the template language
        $this->buildLayouts();
        // Build the template language
        $this->buildElements();
        // Build the template language
        $this->buildHelpers();
    }

    /**
     * {@inheritdoc}
     */
    public function getLayout($layout = null)
    {
        if (empty($layout)) {
            $layout = $this->metadata->getLayout($this->request->action);

            if ($layout !== null) {
                return $layout;
            } else {
                return $this->layout;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    private function buildHelpers()
    {
        $helpers = $this->container->findTaggedServiceIds('templating.helper');
        foreach ($helpers as $id => $definition) {
            $service = $this->container->get($id);
            $this->set(ucfirst(str_replace("helper.", "", $id)), $service);
        }
    }

    private function buildLayouts()
    {
        if (isset($this->config["layouts"]) && is_array($this->config["layouts"])) {
            $layouts = $this->config["layouts"];
            foreach ($layouts as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

    private function buildElements()
    {
        if (isset($this->config["elements"]) && is_array($this->config["elements"])) {
            $elements = $this->config["elements"];
            foreach ($elements as $key => $value) {
                $this->set($key, $value);
            }
        }
    }

}