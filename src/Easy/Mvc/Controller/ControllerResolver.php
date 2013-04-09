<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller;

use Easy\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Easy\HttpKernel\KernelInterface;
use Easy\Network\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ControllerResolver extends BaseControllerResolver
{

    private $logger;
    protected $container;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger A LoggerInterface instance
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request, KernelInterface $kernel)
    {
        $controller = parent::getController($request, $kernel);
        $this->container->set("controller", $controller);
        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }

        return $controller;
    }

}
