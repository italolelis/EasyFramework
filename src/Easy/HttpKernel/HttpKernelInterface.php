<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HttpKernelInterface handles a Request to convert it to a Response.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface HttpKernelInterface
{

    const MASTER_REQUEST = 1;
    const SUB_REQUEST = 2;

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param integer $type    The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Response A Response instance
     *
     * @throws Exception When an Exception occurs during processing
     */
    public function handle(Request $request);
}