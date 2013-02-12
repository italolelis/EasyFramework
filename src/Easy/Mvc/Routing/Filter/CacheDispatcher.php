<?php

/*
 * This file is part of the Easy Framework package.
 *
 * (c) Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Easy\Mvc\Routing\Filter;

use Easy\HttpKernel\Event\BeforeDispatch;
use Easy\Network\Request;
use Easy\Network\Response;
use Symfony\Component\EventDispatcher\Event;

/**
 * This filter will check wheter the response was previously cached in the file system
 * and served it back to the client if appropriate.
 */
class CacheDispatcher
{

    /**
     * Default priority for all methods in this filter
     * This filter should run before the request gets parsed by router
     *
     * @var int
     */
    public $priority = 9;

    /**
     * @var Request
     */
    private $request;

    /**
     * Checks whether the response was cached and set the body accordingly.
     *
     * @param Event $event containing the request and response object
     * @return Response with cached content if found, null otherwise
     */
    public function beforeDispatch(BeforeDispatch $event)
    {
        $this->request = $event->getRequest();
        $response = new Response();
        $response->setPublic();
        if ($response->isNotModified($this->request)) {
            return $response;
        }
    }

}