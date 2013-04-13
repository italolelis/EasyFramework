<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\SecurityBundle\EventListener;

use Easy\HttpKernel\KernelEvents;
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Mvc\Controller\Event\StartupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class AuthorizationListener implements EventSubscriberInterface
{

    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onStartup(StartupEvent $event)
    {
        $request = $event->getRequest();

        if ($this->container->has("Acl")) {
            $acl = $this->container->get("Acl");
            $auth = $acl->getAuth();
            $roles = $this->container->get('security.auth.metadata')->getAuthorized($request->action);

            $user = $auth->getUser();
            if ($user !== null) {
                $field = $acl->getField();
                $user->setIsAuthenticated($auth->isAuthenticated());
                $user->setRoles($acl->getRolesForUser($user->{$field}));

                if (!$acl->isAuthorized($user->{$field}, $roles)) {
                    throw new UnauthorizedException(__("You can not access this."));
                }
            }
        } else {
            throw new \LogicException("The Acl service is not configured. Please add the service to your services file.");
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::STARTUP => array('onStartup'),
        );
    }

}