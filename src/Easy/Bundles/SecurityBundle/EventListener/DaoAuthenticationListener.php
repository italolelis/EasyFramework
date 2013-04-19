<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SecurityBundle\EventListener;

use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\StartupEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class DaoAuthenticationListener implements EventSubscriberInterface
{

    private $container;
    private $configs;

    public function __construct($container, $configs)
    {
        $this->container = $container;
        $this->configs = $configs;
    }

    public function onStartup(StartupEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();
        $current_url = $request->getRequestUrl();

        \Easy\Utility\Debugger::dump($current_url);
        \Easy\Utility\Debugger::dump($this->configs);exit();

        if ($this->container->has('dao.provider')) {
            $auth = $this->container->get('dao.provider');

            if ($auth->autoCheck) {
                $response = null;
                $generator = $this->container->get('router');

                $url = $request->getRequestUri();
                $login_route = $generator->generate($auth->getLoginAction());
                $login_redirect_route = $generator->generate($auth->getLoginRedirect());

                $guestMode = false;
                if ($this->container->get('security.auth.metadata')->isGuest($controller[1])) {
                    $guestMode = true;
                }

                if ($login_route != $url && $guestMode) {
                    return true;
                }

                if ($login_route == $url) {
                    if ($auth->isAuthenticated()) {
                        $response = $controller[0]->redirect($login_redirect_route);
                        return $this->sendResponse($request, $response);
                    } else {
                        return true;
                    }
                }

                if (!$auth->isAuthenticated()) {

                    if (!$auth->restoreFromCookie()) {
                        $response = $controller[0]->redirect($login_route);
                    } else {
                        $response = $controller[0]->redirect($login_redirect_route);
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