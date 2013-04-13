<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SecurityBundle\EventListener;

use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Mvc\Routing\Mapper;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class DaoAuthenticationListener implements EventSubscriberInterface
{

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onStartup(StartupEvent $event)
    {
        $controller = $event->getController();
        $request = $controller->getRequest();

        if ($this->container->has('dao.provider')) {
            $auth = $this->container->get('dao.provider');

            if ($auth->autoCheck) {
                $response = null;

                $url = Mapper::normalize($request->getRequestUrl());
                $loginAction = Mapper::normalize($auth->getLoginAction());

                $guestMode = false;
                if ($this->container->get('security.auth.metadata')->isGuest($request->action)) {
                    $guestMode = true;
                }

                if ($loginAction != $url && $guestMode) {
                    return true;
                }

                $urlComponent = $this->container->get('Url');

                if ($loginAction == $url) {
                    if ($auth->isAuthenticated()) {
                        $response = $controller->redirect($urlComponent->create($auth->getLoginRedirect()));
                        return $this->sendResponse($request, $response);
                    } else {
                        return true;
                    }
                }

                if (!$auth->isAuthenticated()) {
                    if (!$auth->restoreFromCookie()) {
                        $response = $controller->redirect($urlComponent->create($loginAction));
                    } else {
                        $response = $controller->redirect($urlComponent->create($auth->getLoginRedirect()));
                    }
                } else {
                    return true;
                }

                return $this->sendResponse($request, $response);
            }
        } else {
            throw new LogicException('The Auth service is not configured. Please add the service to your services file.');
        }
    }

    private function sendResponse($request, $response)
    {
        if ($response) {
            $response->prepare($request);
            return $response->send();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::STARTUP => array('onStartup'),
        );
    }

}