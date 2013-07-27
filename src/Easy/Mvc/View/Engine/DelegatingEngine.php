<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\View\Engine;

use Easy\Mvc\View\Engine\EngineInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * DelegatingEngine selects an engine for a given template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DelegatingEngine implements EngineInterface
{

    /**
     * @var EngineInterface[]
     */
    protected $engines;
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The DI container
     * @param array $engineIds An array of engine Ids
     */
    public function __construct(ContainerInterface $container, array $engineIds)
    {
        $this->container = $container;
        $this->engines = $engineIds;
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string $view       The view name
     * @param array $parameters An array of parameters to pass to the view
     * @param Response $response   A Response instance
     *
     * @return Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        return $this->getEngine($view)->renderResponse($view, $parameters, $response);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function render($name, array $parameters = array())
    {
        return $this->getEngine($name)->render($name, $parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function exists($name)
    {
        return $this->getEngine($name)->exists($name);
    }

    /**
     * Adds an engine.
     *
     * @param EngineInterface $engine An EngineInterface instance
     *
     * @api
     */
    public function addEngine(EngineInterface $engine)
    {
        $this->engines[] = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        foreach ($this->engines as $i => $engine) {
            if (is_string($engine)) {
                $engine = $this->engines[$i] = $this->container->get($engine);
            }

            if ($engine->supports($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngine($name)
    {
        foreach ($this->engines as $i => $engine) {
            if (is_string($engine)) {
                $engine = $this->engines[$i] = $this->container->get($engine);
            }

            if ($engine->supports($name)) {
                return $engine;
            }
        }

        throw new RuntimeException(sprintf('No engine is able to work with the template "%s".', $name));
    }

    public function getExtension()
    {
        throw new \LogicException('This method is not implemented');
    }

}