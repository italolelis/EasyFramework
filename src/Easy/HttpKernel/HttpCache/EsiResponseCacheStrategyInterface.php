<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\HttpCache;

use Easy\Network\Response;

/**
 * EsiResponseCacheStrategyInterface implementations know how to compute the
 * Response cache HTTP header based on the different ESI response cache headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface EsiResponseCacheStrategyInterface
{

    /**
     * Adds a Response.
     *
     * @param Response $response
     */
    public function add(Response $response);

    /**
     * Updates the Response HTTP headers based on the embedded Responses.
     *
     * @param Response $response
     */
    public function update(Response $response);
}
