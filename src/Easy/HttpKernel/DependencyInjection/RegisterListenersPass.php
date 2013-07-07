<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\DependencyInjection;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to register tagged services for an event dispatcher.
 */
class RegisterListenersPass implements CompilerPassInterface
{

    /**
     * @var string
     */
    protected $dispatcherService;

    /**
     * @var string
     */
    protected $listenerTag;

    /**
     * @var string
     */
    protected $subscriberTag;

    /**
     * Constructor.
     *
     * @param string $dispatcherService Service name of the event dispatcher in processed container
     * @param string $listenerTag       Tag name used for listener
     * @param string $subscriberTag     Tag name used for subscribers
     */
    public function __construct($dispatcherService = 'event_dispatcher', $listenerTag = 'kernel.event_listener', $subscriberTag = 'kernel.event_subscriber')
    {
        $this->dispatcherService = $dispatcherService;
        $this->listenerTag = $listenerTag;
        $this->subscriberTag = $subscriberTag;
    }

    public function process(ContainerBuilder $container)
    {
        $dispatcher = $container->get($this->dispatcherService);

//        foreach ($container->findTaggedServiceIds($this->listenerTag) as $id => $events) {
//            foreach ($events as $event) {
//                $priority = isset($event['priority']) ? $event['priority'] : 0;
//
//                if (!isset($event['event'])) {
//                    throw new InvalidArgumentException(sprintf('Service "%s" must define the "event" attribute on "kernel.event_listener" tags.', $id));
//                }
//
//                if (!isset($event['method'])) {
//                    $event['method'] = 'on' . preg_replace_callback(array(
//                                '/(?<=\b)[a-z]/i',
//                                '/[^a-z0-9]/i',
//                                    ), function ($matches) {
//                                        return strtoupper($matches[0]);
//                                    }, $event['event']);
//                    $event['method'] = preg_replace('/[^a-z0-9]/i', '', $event['method']);
//                }
//
//                $dispatcher->addListenerService($event['event'], array($id, $event['method']), $priority);
//            }
//        }

        $subscribers = $container->findTaggedServiceIds($this->subscriberTag);
        foreach ($subscribers as $id => $attributes) {
            $dispatcher->addSubscriber($container->get($id));
        }
    }

}
