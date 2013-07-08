<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\EventListener;

use Easy\HttpKernel\Event\GetResponseForControllerResultEvent;
use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

class TemplateListener implements EventSubscriberInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $controller;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onInitialize(InitializeEvent $event)
    {
        $request = $event->getRequest();
        $request->attributes->set('_auto_render', false);
        $event->setRequest($request);
    }

    public function onKernelController(StartupEvent $event)
    {
        $this->controller = $event->getController();
        $request = $event->getRequest();

        $guesser = $this->container->get('framework.template.guesser');

        $request->attributes->set('_template', $guesser->guessTemplateName($this->controller, $request, 'tpl'));
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $templating = $this->container->get('templating');
        $autoRender = $request->attributes->get('_auto_render');
        $layout = false;

        $annotation = $this->container->get('controller.metadata')->getTemplateAnnotation($this->controller[1]);

        if ($annotation) {
            $autoRender = true;
            $layout = $annotation->getLayout();
        }

        if ($autoRender) {
            $response = $templating->renderResponse($request->attributes->get('_template'), $layout);
            $event->setResponse($response);
        } else {
            $response = new Response();
            $response->setContent($event->getControllerResult());
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::INITIALIZE => array('onInitialize'),
            KernelEvents::STARTUP => array('onKernelController', -128),
            KernelEvents::VIEW => array('onKernelView'),
        );
    }

}
