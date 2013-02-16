<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Bundles\SecurityBundle\EventListener;

use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Mvc\Routing\Mapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class AuthenticationListener implements EventSubscriberInterface
{

    public function onStartup(StartupEvent $event)
    {
        $controller = $event->getController();
        $request = $controller->getRequest();
        $container = $controller->getContainer();

        if ($container->has("Auth")) {
            $auth = $container->get("Auth");
            $auth->setController($controller);

            if ($auth->autoCheck) {
                $response = null;

                $url = Mapper::normalize($request->getRequestUrl());
                $loginAction = Mapper::normalize($auth->getLoginAction());

                if ($loginAction != $url && $auth->getGuestMode()) {
                    return true;
                }

                $urlComponent = $container->get("Url");
                if ($loginAction == $url) {
                    if ($auth->isAuthenticated()) {
                        $response = $controller->redirect($urlComponent->create($auth->getLoginRedirect()));
                    }
                }

                if (!$auth->isAuthenticated()) {
                    if (!$auth->restoreFromCookie()) {
                        $response = $controller->redirect($urlComponent->create($loginAction));
                    } else {
                        $response = $controller->redirect($urlComponent->create($auth->getLoginRedirect()));
                    }
                }
                if ($response) {
                    $response->prepare($request);
                    $response->send();
                }
            }
        } else {
            throw new \LogicException("The Auth service is not configured. Please add the service to your services file.");
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::STARTUP => array('onStartup'),
        );
    }

}