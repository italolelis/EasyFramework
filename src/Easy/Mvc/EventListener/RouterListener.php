<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\EventListener;

use Easy\HttpKernel\Event\GetResponseEvent;
use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Routing\RequestContext;
use Easy\Network\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class RouterListener implements EventSubscriberInterface
{

    private $matcher;
    private $context;
    private $logger;

    public function __construct($matcher, RequestContext $context = null, LoggerInterface $logger = null)
    {
        $this->matcher = $matcher;
        $this->context = $context;
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // initialize the context that is also used by the generator (assuming matcher and generator share the same context instance)
        // we call setRequest even if most of the time, it has already been done to keep compatibility
        // with frameworks which do not use the Symfony service container
        $this->setRequest($request);

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        // add attributes based on the request (routing)
        try {
            // matching a request is more powerful than matching a URL path + context, so try that first
            if ($this->matcher instanceof RequestMatcherInterface) {
                $parameters = $this->matcher->matchRequest($request);
            } else {
                $parameters = $this->matcher->match($request->getPathInfo());
            }

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
            }

            $request->attributes->add($parameters);
            unset($parameters['_route']);
            unset($parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

            throw new \Easy\Network\Exception\NotFoundException($message, $e);
        }
    }

    /**
     * Sets the current Request.
     *
     * The application should call this method whenever the Request
     * object changes (entering a Request scope for instance, but
     * also when leaving a Request scope -- especially when they are
     * nested).
     *
     * @param Request|null $request A Request instance
     */
    public function setRequest(Request $request = null)
    {
        if (null !== $request) {
            $this->context->fromEasyRequest($request);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest'),
        );
    }

}