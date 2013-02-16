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
use Easy\Mvc\Controller\Component\Exception\UnauthorizedException;
use Easy\Mvc\Controller\Event\StartupEvent;
use Easy\Security\Authentication\Metadata\AuthMetadata;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class AuthorizationListener implements EventSubscriberInterface
{

    public function onStartup(StartupEvent $event)
    {
        $controller = $event->getController();
        $container = $controller->getContainer();

        if ($container->has("Acl")) {
            $acl = $container->get("Acl");
            $auth = $acl->getAuth();
            $acl->setMetadata(new AuthMetadata($controller));


            $user = $auth->getUser();
            if ($user !== null) {
                $field = $acl->getField();
                $user->setIsAuthenticated($auth->isAuthenticated());
                $user->setRoles($acl->getRolesForUser($user->{$field}));

                if (!$acl->isAuthorized($user->{$field})) {
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