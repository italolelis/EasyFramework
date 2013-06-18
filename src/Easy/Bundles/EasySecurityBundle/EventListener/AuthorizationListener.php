<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\EasySecurityBundle\EventListener;

use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Mvc\Controller\Event\InitializeEvent;
use Easy\Mvc\Controller\Event\StartupEvent;
use LogicException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class AuthorizationListener implements EventSubscriberInterface
{

    private $container;
    private $auth;
    private $configs;

    public function __construct($container, $auth, $configs)
    {
        $this->container = $container;
        $this->auth = $auth;
        $this->configs = $configs;
    }

    public function onInitialize(InitializeEvent $event)
    {
        $controller = $event->getController();
        $request = $event->getRequest();

        $current_path = $request->getPathInfo();
        $auth = $this->auth;


        $auth->autoCheck = false;

        foreach ($this->configs['firewalls'] as $name => $firewall) {
            if (strstr($current_path, $firewall['pattern'])) {
                $auth->autoCheck = true;
            }
        }


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
    }

    private function sendResponse($request, $response)
    {
        if ($response) {
            $response->prepare($request);
            return $response->send();
        }
    }

    public function onStartup(StartupEvent $event)
    {
        $controller = $event->getController();

        if ($this->container->has("Acl")) {
            $acl = $this->container->get("Acl");
            $auth = $acl->getAuth();

            if ($auth->autoCheck) {
                $roles = $this->container->get('security.auth.metadata')->getAuthorized($controller[1]);

                $user = $auth->getUser();
                if ($user !== null) {
                    $field = $acl->getField();
                    $user->setIsAuthenticated($auth->isAuthenticated());
                    $user->setRoles($acl->getRolesForUser($user->{$field}));

                    if (!$acl->isAuthorized($user->{$field}, $roles)) {
                        throw new UnauthorizedException(__("You can not access this."));
                    }
                }
            }
        } else {
            throw new LogicException("The Acl service is not configured. Please add the service to your services file.");
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::INITIALIZE => array('onInitialize', 0),
            KernelEvents::STARTUP => array('onStartup'),
        );
    }

}