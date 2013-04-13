<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\EventListener;

use Easy\HttpKernel\Event\GetResponseEvent;
use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Routing\Mapper;
use Easy\Network\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class ParseListener implements EventSubscriberInterface
{

    /**
     * @var Request
     */
    private $request;

    public function onRequest(GetResponseEvent $event)
    {
        $this->request = $event->getRequest();
        Mapper::setRequestInfo($this->request);

        if (empty($this->request->params['controller'])) {
            $params = Mapper::parse($this->request->getRequestUrl());
            $this->request->addParams($params);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onRequest'),
        );
    }

}